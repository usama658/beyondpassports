<?php
/**
 * Plugin Name: UKV Required Docs per destination (Gap #78)
 * Desc: Defines the set of documents a destination requires and wires the COUNT into the
 *       pre-submission QA gate so completeness compares UPLOADED vs REQUIRED (not just ">= 1").
 *         (a) Pods `required_docs` paragraph field on `destination` (comma/newline list of labels);
 *         (b) ukv_required_docs() / _count() readers with a sensible default;
 *         (c) ukv_order_docs_complete() — uploaded ukv_documents >= destination's required count;
 *         (d) ukv_sync_required_count() — sets ukv_required_docs order meta from the destination, so
 *             the existing QA gate (ukv-qa-gate.php) enforces the per-destination floor.
 */
defined( 'ABSPATH' ) || exit;

/** Sensible default list when a destination has no explicit required_docs configured. */
function ukv_required_docs_default(): array {
	return [ 'Passport bio page', 'Passport photo' ];
}

/**
 * Parse a stored required_docs blob (comma/newline separated labels) into a clean array.
 * Trims, drops empties, de-duplicates. Returns [] when nothing usable.
 */
function ukv_required_docs_parse( $raw ): array {
	if ( is_array( $raw ) ) { $raw = implode( "\n", $raw ); }
	$parts = preg_split( '/[\r\n,]+/', (string) $raw );
	$out   = [];
	foreach ( (array) $parts as $p ) {
		$p = trim( wp_strip_all_tags( (string) $p ) );
		if ( '' !== $p && ! in_array( $p, $out, true ) ) { $out[] = $p; }
	}
	return $out;
}

/**
 * Required document labels for a destination. Reads the Pods `required_docs` field; falls back to
 * ukv_required_docs_default() when the destination has no value (or Pods is unavailable).
 */
function ukv_required_docs( string $dest_slug ): array {
	$slug = ukv_dest_slug( $dest_slug );
	if ( '' === $slug ) { return ukv_required_docs_default(); }
	$raw  = function_exists( 'ukv_dest_value' ) ? ukv_dest_value( $slug, 'required_docs' ) : null;
	$list = ukv_required_docs_parse( $raw );
	return empty( $list ) ? ukv_required_docs_default() : $list;
}

/** Count of required documents for a destination. */
function ukv_required_docs_count( string $dest_slug ): int {
	return count( ukv_required_docs( $dest_slug ) );
}

/**
 * True when the number of uploaded documents on the order meets/exceeds the destination's
 * required count. Reads ukv_documents (array of attachment IDs) and the order's ukv_destination
 * (display name -> slug).
 */
function ukv_order_docs_complete( int $order_id ): bool {
	$docs = array_filter( (array) get_post_meta( $order_id, 'ukv_documents', true ) );
	$have = count( $docs );
	$dest = (string) get_post_meta( $order_id, 'ukv_destination', true );
	$need = ukv_required_docs_count( $dest );
	return $have >= $need;
}

/**
 * Mirror the destination's required-docs COUNT onto the order as ukv_required_docs meta, which the
 * QA gate (ukv-qa-gate.php) reads to compare uploaded vs required. No-op for non-orders.
 */
function ukv_sync_required_count( int $order_id ): void {
	if ( 'ukv_order' !== get_post_type( $order_id ) ) { return; }
	$dest = (string) get_post_meta( $order_id, 'ukv_destination', true );
	update_post_meta( $order_id, 'ukv_required_docs', ukv_required_docs_count( $dest ) );
}

/**
 * Ensure `required_docs` is a registered Pods PARAGRAPH field on the destination pod, so
 * Pods `field()` returns the stored text. Idempotent — save_field upserts. (Mirrors the
 * passport_validity_months registration pattern.)
 */
function ukv_required_docs_register_field(): void {
	if ( ! function_exists( 'pods_api' ) ) { return; }
	$api      = pods_api();
	$existing = $api->load_field( [ 'pod' => 'destination', 'name' => 'required_docs' ] );
	if ( ! empty( $existing ) ) { return; }
	$api->save_field( [
		'pod'   => 'destination',
		'name'  => 'required_docs',
		'label' => 'Required documents (one per line, or comma-separated)',
		'type'  => 'paragraph',
	] );
}

/**
 * One-time seed: register the Pods field (if needed) then set `required_docs` =
 * "Passport bio page, Passport photo" on every `destination` that has no value.
 * Returns the number of destinations updated. Idempotent.
 */
function ukv_seed_required_docs(): int {
	if ( ! function_exists( 'pods' ) ) { return 0; }
	ukv_required_docs_register_field();
	$default = implode( ', ', ukv_required_docs_default() );
	$ids = get_posts( [
		'post_type'   => 'destination',
		'post_status' => 'publish',
		'numberposts' => -1,
		'fields'      => 'ids',
	] );
	$updated = 0;
	foreach ( $ids as $id ) {
		$pod = pods( 'destination', $id );
		$cur = $pod->field( 'required_docs' );
		if ( is_array( $cur ) ) { $cur = reset( $cur ); }
		if ( '' === trim( (string) $cur ) ) {
			$pod->save( 'required_docs', $default );
			$updated++;
		}
	}
	return $updated;
}

/** Guarded one-time seeding on admin init (runs once, then a flag short-circuits it). */
add_action( 'admin_init', function () {
	if ( get_option( 'ukv_required_docs_seeded' ) ) { return; }
	if ( ukv_seed_required_docs() >= 0 ) {
		update_option( 'ukv_required_docs_seeded', 1, false );
	}
} );

/**
 * Keep the order's required-docs count in sync with its destination on every order save, so the
 * QA gate always compares uploaded vs required. Guards autosave / revisions. Runs at priority 7,
 * before the gate's enforce hook (9), so the freshly-synced count is in effect for that save.
 */
add_action( 'save_post_ukv_order', function ( $order_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( wp_is_post_revision( $order_id ) ) { return; }
	ukv_sync_required_count( (int) $order_id );
}, 7 );
