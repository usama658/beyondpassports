<?php
/**
 * Plugin Name: UKV GDPR Document Retention + Auto-purge
 * Desc: Records when an order closes (ukv_closed_at) and, after a configurable
 *       retention window (default 90 days), force-deletes the order's uploaded
 *       documents — files and attachment posts — via a daily cron. The order
 *       record itself is NEVER deleted; only its uploaded documents. Idempotent.
 * Gap: #71 (GDPR data minimisation / retention).
 */
defined( 'ABSPATH' ) || exit;

const UKV_RETENTION_CRON   = 'ukv_retention_purge_cron';
const UKV_RETENTION_OPTION = 'ukv_retention_days';
const UKV_RETENTION_DEFAULT = 90;

/** Configured retention window in whole days (>= 1). */
function ukv_retention_days(): int {
	$d = (int) get_option( UKV_RETENTION_OPTION, UKV_RETENTION_DEFAULT );
	return $d > 0 ? $d : UKV_RETENTION_DEFAULT;
}

/* -------------------------------------------------------------------------
 * 1. Setting: retention days. Registered + a field on a small settings page.
 * ---------------------------------------------------------------------- */
add_action( 'admin_init', function () {
	register_setting( 'ukv_retention', UKV_RETENTION_OPTION, [
		'type'              => 'integer',
		'sanitize_callback' => static function ( $v ) {
			$v = (int) $v;
			return $v > 0 ? $v : UKV_RETENTION_DEFAULT;
		},
		'default'           => UKV_RETENTION_DEFAULT,
	] );
} );

/* -------------------------------------------------------------------------
 * 2. Record closure time on save. Priority 14 (before the Orders journey
 *    save at default 10? — no: 14 runs after 10, so ukv_status is already
 *    persisted by the metabox save). Guards autosave.
 * ---------------------------------------------------------------------- */
add_action( 'save_post_ukv_order', function ( $pid ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( 'ukv_order' !== get_post_type( $pid ) ) { return; }
	$status = (string) get_post_meta( $pid, 'ukv_status', true );
	if ( in_array( $status, UKV_ORDER_CLOSED, true ) && '' === (string) get_post_meta( $pid, 'ukv_closed_at', true ) ) {
		update_post_meta( $pid, 'ukv_closed_at', time() );
	}
}, 14 );

/**
 * Stamp an order closed "now" if not already stamped. Exposed for tests/ops.
 */
function ukv_mark_closed_now( int $order_id ): void {
	if ( $order_id <= 0 || 'ukv_order' !== get_post_type( $order_id ) ) { return; }
	if ( '' === (string) get_post_meta( $order_id, 'ukv_closed_at', true ) ) {
		update_post_meta( $order_id, 'ukv_closed_at', time() );
	}
}

/* -------------------------------------------------------------------------
 * 3. The purge. Returns the list of order IDs whose documents were purged
 *    on this run. Force-deletes files; never deletes the order. Idempotent.
 * ---------------------------------------------------------------------- */
function ukv_retention_purge(): array {
	$cutoff = time() - ( ukv_retention_days() * 86400 );
	$ids    = get_posts( [
		'post_type'      => 'ukv_order',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => [
			'relation' => 'AND',
			[ 'key' => 'ukv_closed_at', 'compare' => 'EXISTS' ],
			[
				'relation' => 'OR',
				[ 'key' => 'ukv_docs_purged', 'compare' => 'NOT EXISTS' ],
				[ 'key' => 'ukv_docs_purged', 'value' => '1', 'compare' => '!=' ],
			],
		],
	] );

	$purged = [];
	foreach ( $ids as $oid ) {
		$closed_at = (int) get_post_meta( $oid, 'ukv_closed_at', true );
		if ( $closed_at <= 0 || $closed_at > $cutoff ) { continue; } // not set, or still within window.
		if ( '1' === (string) get_post_meta( $oid, 'ukv_docs_purged', true ) ) { continue; } // already done.

		// Force-delete each uploaded document (attachment post + underlying files).
		$docs = (array) get_post_meta( $oid, 'ukv_documents', true );
		foreach ( $docs as $att_id ) {
			$att_id = (int) $att_id;
			if ( $att_id > 0 && get_post( $att_id ) ) {
				wp_delete_attachment( $att_id, true );
			}
		}

		update_post_meta( $oid, 'ukv_documents', [] );
		update_post_meta( $oid, 'ukv_docs_purged', '1' );

		// Append a journey note (system / internal).
		$journey   = (array) get_post_meta( $oid, 'ukv_journey', true );
		$journey[] = [
			'date'    => gmdate( 'Y-m-d H:i' ),
			'agent'   => 'system',
			'channel' => 'internal',
			'text'    => 'Documents purged per retention policy',
		];
		update_post_meta( $oid, 'ukv_journey', $journey );

		$purged[] = (int) $oid;
	}

	return $purged;
}

/** Count orders currently eligible to have documents purged (past retention, not yet purged). */
function ukv_retention_pending_count(): int {
	$cutoff = time() - ( ukv_retention_days() * 86400 );
	$ids    = get_posts( [
		'post_type'      => 'ukv_order',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => [ [ 'key' => 'ukv_closed_at', 'compare' => 'EXISTS' ] ],
	] );
	$n = 0;
	foreach ( $ids as $oid ) {
		if ( '1' === (string) get_post_meta( $oid, 'ukv_docs_purged', true ) ) { continue; }
		$closed_at = (int) get_post_meta( $oid, 'ukv_closed_at', true );
		if ( $closed_at > 0 && $closed_at <= $cutoff ) { $n++; }
	}
	return $n;
}

/* -------------------------------------------------------------------------
 * Daily cron registration + handler.
 * ---------------------------------------------------------------------- */
add_action( 'init', function () {
	if ( ! wp_next_scheduled( UKV_RETENTION_CRON ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', UKV_RETENTION_CRON );
	}
} );
add_action( UKV_RETENTION_CRON, 'ukv_retention_purge' );

/* -------------------------------------------------------------------------
 * 4. Admin: settings + "documents pending purge" count.
 *    Lives under Tools, alongside the Pre-launch readiness page.
 * ---------------------------------------------------------------------- */
add_action( 'admin_menu', function () {
	add_management_page(
		'Document retention',
		'Doc retention',
		'manage_options',
		'ukv-retention',
		'ukv_retention_render_page'
	);
} );

function ukv_retention_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to view this page.' ) );
	}

	// Manual "run now" (nonce-protected) for ops convenience.
	$ran = null;
	if ( isset( $_POST['ukv_retention_run'] )
		&& check_admin_referer( 'ukv_retention_run', 'ukv_retention_run_nonce' ) ) {
		$ran = ukv_retention_purge();
	}

	$days    = ukv_retention_days();
	$pending = ukv_retention_pending_count();

	echo '<div class="wrap"><h1>Document retention</h1>';
	echo '<p>Closed orders (delivered / won / rejected / refunded) have their uploaded '
		. 'documents force-deleted ' . esc_html( (string) $days ) . ' days after closure. '
		. 'The order record itself is always kept — only the uploaded files are purged.</p>';

	if ( is_array( $ran ) ) {
		echo '<div class="notice notice-success inline"><p>'
			. esc_html( sprintf( 'Purge run complete: %d order%s had documents purged.', count( $ran ), 1 === count( $ran ) ? '' : 's' ) )
			. '</p></div>';
	}

	echo '<p><strong>Documents pending purge:</strong> '
		. '<span style="font-size:1.2em">' . esc_html( (string) $pending ) . '</span> order(s) past retention awaiting the next run.</p>';

	// Settings form (retention days).
	echo '<form method="post" action="options.php" style="margin:18px 0;max-width:520px">';
	settings_fields( 'ukv_retention' );
	echo '<table class="form-table"><tr>'
		. '<th scope="row"><label for="ukv_retention_days">Retention period (days)</label></th>'
		. '<td><input name="' . esc_attr( UKV_RETENTION_OPTION ) . '" id="ukv_retention_days" type="number" min="1" step="1" value="'
		. esc_attr( (string) $days ) . '" class="small-text"> '
		. '<p class="description">Days after an order closes before its uploaded documents are deleted. Default ' . (int) UKV_RETENTION_DEFAULT . '.</p>'
		. '</td></tr></table>';
	submit_button( 'Save retention period' );
	echo '</form>';

	// Manual run.
	echo '<form method="post" style="margin-top:8px">';
	wp_nonce_field( 'ukv_retention_run', 'ukv_retention_run_nonce' );
	echo '<input type="hidden" name="ukv_retention_run" value="1">';
	submit_button( 'Run purge now', 'secondary' );
	echo '</form>';

	$next = wp_next_scheduled( UKV_RETENTION_CRON );
	echo '<p class="description">' . ( $next
		? 'Next scheduled automatic purge: ' . esc_html( gmdate( 'Y-m-d H:i', $next ) ) . ' UTC.'
		: 'Daily purge cron is not currently scheduled.' ) . '</p>';

	echo '</div>';
}

/* Tidy up the scheduled event if this mu-plugin is ever removed (best effort). */
register_deactivation_hook( __FILE__, function () {
	$ts = wp_next_scheduled( UKV_RETENTION_CRON );
	if ( $ts ) { wp_unschedule_event( $ts, UKV_RETENTION_CRON ); }
} );
