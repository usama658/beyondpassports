<?php
/**
 * Plugin Name: UKV Rejection (Structured rejection-reason capture + analytics — Gap #73)
 * Desc: When an order is rejected, capture a structured reason (from a fixed taxonomy)
 *       plus an optional free-text note. Stores reason + note meta, appends a journey
 *       audit note, and surfaces aggregate rejection-cause analytics (overall and per
 *       destination) in an admin dashboard widget.
 */
defined( 'ABSPATH' ) || exit;

/** Fixed taxonomy of rejection reasons: key => label. */
const UKV_REJECTION_REASONS = [
	'doc_quality'       => 'Document quality',
	'eligibility'       => 'Eligibility',
	'passport_validity' => 'Passport validity',
	'portal_error'      => 'Portal/technical',
	'customer_withdrew' => 'Customer withdrew',
	'other'             => 'Other',
];

/**
 * Record a structured rejection reason for an order.
 *
 * Validates the reason key against UKV_REJECTION_REASONS, stores
 * ukv_rejection_reason + ukv_rejection_note, and appends a journey audit
 * note (agent 'system', channel 'internal').
 *
 * @return bool false when the reason key is not in the taxonomy.
 */
function ukv_set_rejection( int $order_id, string $reason_key, string $note = '' ): bool {
	if ( ! isset( UKV_REJECTION_REASONS[ $reason_key ] ) ) {
		return false;
	}

	$note = sanitize_textarea_field( $note );
	update_post_meta( $order_id, 'ukv_rejection_reason', $reason_key );
	update_post_meta( $order_id, 'ukv_rejection_note', $note );

	// Journey audit note (matches ukv-orders journey schema: date/agent/channel/text).
	$label   = UKV_REJECTION_REASONS[ $reason_key ];
	$journey = get_post_meta( $order_id, 'ukv_journey', true );
	$journey = is_array( $journey ) ? $journey : [];
	$journey[] = [
		'date'    => gmdate( 'Y-m-d H:i' ),
		'agent'   => 'system',
		'channel' => 'internal',
		'text'    => sprintf( 'Rejection reason: %s — %s', $label, $note ),
	];
	update_post_meta( $order_id, 'ukv_journey', $journey );

	return true;
}

/**
 * Aggregate rejection-cause analytics across all rejected orders.
 *
 * @return array{by_reason:array<string,int>,by_destination:array<string,array<string,int>>,total:int}
 */
function ukv_rejection_stats(): array {
	$ids = get_posts( [
		'post_type'      => 'ukv_order',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'post_status'    => 'publish',
		'meta_query'     => [
			[ 'key' => 'ukv_status', 'value' => 'rejected' ],
		],
	] );

	$by_reason = [];
	$by_dest   = [];
	$total     = 0;

	foreach ( $ids as $pid ) {
		$reason = (string) get_post_meta( $pid, 'ukv_rejection_reason', true );
		if ( '' === $reason || ! isset( UKV_REJECTION_REASONS[ $reason ] ) ) {
			$reason = 'other';
		}
		$total++;
		$by_reason[ $reason ] = ( $by_reason[ $reason ] ?? 0 ) + 1;

		$dest = sanitize_title( (string) get_post_meta( $pid, 'ukv_destination', true ) );
		if ( '' === $dest ) { $dest = 'unknown'; }
		if ( ! isset( $by_dest[ $dest ] ) ) { $by_dest[ $dest ] = []; }
		$by_dest[ $dest ][ $reason ] = ( $by_dest[ $dest ][ $reason ] ?? 0 ) + 1;
	}

	return [
		'by_reason'      => $by_reason,
		'by_destination' => $by_dest,
		'total'          => $total,
	];
}

/* -------------------------------------------------------------------------
 * Admin: "Rejection reason" meta box on the order edit screen.
 * ---------------------------------------------------------------------- */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ukv_rejection', 'Rejection reason', 'ukv_rejection_metabox', 'ukv_order', 'side', 'default' );
} );

function ukv_rejection_metabox( $post ) {
	$pid = (int) $post->ID;
	wp_nonce_field( 'ukv_rejection_' . $pid, 'ukv_rejection_nonce' );

	$current = (string) get_post_meta( $pid, 'ukv_rejection_reason', true );
	$note    = (string) get_post_meta( $pid, 'ukv_rejection_note', true );
	$status  = (string) get_post_meta( $pid, 'ukv_status', true );

	if ( 'rejected' !== $status ) {
		echo '<p style="color:#555;font-size:11px">Only meaningful once the status is set to <strong>Rejected</strong>.</p>';
	}

	echo '<p><label for="ukv_rejection_reason"><strong>Reason</strong></label><br>';
	echo '<select id="ukv_rejection_reason" name="ukv_rejection_reason" style="width:100%">';
	echo '<option value="">— Select —</option>';
	foreach ( UKV_REJECTION_REASONS as $k => $label ) {
		echo '<option value="' . esc_attr( $k ) . '" ' . selected( $current, $k, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select></p>';

	echo '<p><label for="ukv_rejection_note"><strong>Note</strong></label><br>';
	echo '<textarea id="ukv_rejection_note" name="ukv_rejection_note" rows="3" style="width:100%" placeholder="Detail (optional)">' . esc_textarea( $note ) . '</textarea></p>';
	echo '<p style="color:#555;font-size:11px">Saving a reason logs an audit note to the Lead Journey.</p>';
}

add_action( 'save_post_ukv_order', function ( $pid ) {
	if ( ! isset( $_POST['ukv_rejection_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ukv_rejection_nonce'] ) ), 'ukv_rejection_' . $pid )
	) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $pid ) ) { return; }

	$reason = isset( $_POST['ukv_rejection_reason'] ) ? sanitize_text_field( wp_unslash( $_POST['ukv_rejection_reason'] ) ) : '';
	$note   = isset( $_POST['ukv_rejection_note'] ) ? wp_unslash( $_POST['ukv_rejection_note'] ) : '';

	// Only act when a valid reason is chosen AND it differs from what's stored
	// (avoid re-logging the same reason on every save). Invalid keys are ignored.
	if ( '' !== $reason && isset( UKV_REJECTION_REASONS[ $reason ] ) ) {
		$existing      = (string) get_post_meta( $pid, 'ukv_rejection_reason', true );
		$existing_note = (string) get_post_meta( $pid, 'ukv_rejection_note', true );
		if ( $reason !== $existing || sanitize_textarea_field( $note ) !== $existing_note ) {
			ukv_set_rejection( $pid, $reason, $note );
		}
	}
} );

/* -------------------------------------------------------------------------
 * Dashboard widget: rejection causes (overall + top reason per destination).
 * ---------------------------------------------------------------------- */
add_action( 'wp_dashboard_setup', function () {
	wp_add_dashboard_widget( 'ukv_rejection_causes', 'UKV — Rejection causes', 'ukv_rejection_widget' );
} );

function ukv_rejection_widget() {
	$stats = ukv_rejection_stats();

	if ( empty( $stats['total'] ) ) {
		echo '<p style="color:#555">No rejected orders yet.</p>';
		return;
	}

	echo '<p><strong>Total rejections:</strong> ' . (int) $stats['total'] . '</p>';

	// Top reasons overall.
	$by_reason = $stats['by_reason'];
	arsort( $by_reason );
	echo '<p style="margin-bottom:2px"><strong>Top reasons</strong></p><ul style="margin:0 0 8px 1em">';
	foreach ( $by_reason as $key => $count ) {
		$label = UKV_REJECTION_REASONS[ $key ] ?? $key;
		echo '<li>' . esc_html( $label ) . ': <strong>' . (int) $count . '</strong></li>';
	}
	echo '</ul>';

	// Top reason per destination.
	echo '<p style="margin-bottom:2px"><strong>Top reason by destination</strong></p><ul style="margin:0 0 0 1em">';
	foreach ( $stats['by_destination'] as $dest => $reasons ) {
		arsort( $reasons );
		$top_key   = (string) array_key_first( $reasons );
		$top_count = (int) reset( $reasons );
		$top_label = UKV_REJECTION_REASONS[ $top_key ] ?? $top_key;
		echo '<li>' . esc_html( ucwords( str_replace( '-', ' ', $dest ) ) ) . ': '
			. esc_html( $top_label ) . ' (<strong>' . $top_count . '</strong>)</li>';
	}
	echo '</ul>';
}
