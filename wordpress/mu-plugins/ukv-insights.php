<?php
/**
 * Plugin Name: UKV Insights (Orders P9 — case-pattern intelligence)
 * Desc: Success-rate stats, per-case risk scoring + risk-flag refresh cron, and a
 *       "Success intelligence" dashboard widget. Reuses ukv-barriers helpers (no copies).
 */
defined( 'ABSPATH' ) || exit;

// Statuses that count as a successful outcome vs a failed one (subset of UKV_ORDER_CLOSED).
const UKV_SUCCESS_STATUSES = [ 'won', 'delivered' ];
const UKV_FAIL_STATUSES    = [ 'rejected', 'refunded' ];

/**
 * Aggregate success/fail stats across every ukv_order.
 * Returns:
 *   overall            => [ success, fail, rate ]
 *   by_destination     => slug => [ success, fail, rate, rejection_rate ]
 *   avg_processing_days=> float (delivered/won: now - ukv_created, as a proxy)
 *   high_risk_open     => int
 */
function ukv_success_stats() {
	$ids = get_posts( [ 'post_type' => 'ukv_order', 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => -1 ] );

	$overall = [ 'success' => 0, 'fail' => 0, 'rate' => 0.0 ];
	$by      = [];
	$proc_sum = 0.0; $proc_n = 0;
	$now = time();

	foreach ( $ids as $oid ) {
		$status = (string) get_post_meta( $oid, 'ukv_status', true );
		$slug   = ukv_dest_slug( get_post_meta( $oid, 'ukv_destination', true ) );
		if ( ! isset( $by[ $slug ] ) ) { $by[ $slug ] = [ 'success' => 0, 'fail' => 0, 'rate' => 0.0, 'rejection_rate' => 0.0 ]; }

		$is_success = in_array( $status, UKV_SUCCESS_STATUSES, true );
		$is_fail    = in_array( $status, UKV_FAIL_STATUSES, true );

		if ( $is_success ) { $overall['success']++; $by[ $slug ]['success']++; }
		if ( $is_fail )    { $overall['fail']++;    $by[ $slug ]['fail']++; }

		// Avg processing days proxy for completed-good cases.
		if ( $is_success ) {
			$created = (int) get_post_meta( $oid, 'ukv_created', true );
			if ( $created > 0 ) { $proc_sum += max( 0, $now - $created ) / 86400; $proc_n++; }
		}
	}

	$den = $overall['success'] + $overall['fail'];
	$overall['rate'] = $den ? $overall['success'] / $den : 0.0;

	foreach ( $by as $slug => &$row ) {
		$d = $row['success'] + $row['fail'];
		$row['rate'] = $d ? $row['success'] / $d : 0.0;
		list( $rej, ) = ukv_dest_rejection_rate( $slug );
		$row['rejection_rate'] = $rej;
	}
	unset( $row );

	// Count open orders currently scored as high risk.
	$high = 0;
	foreach ( $ids as $oid ) {
		if ( in_array( get_post_meta( $oid, 'ukv_status', true ), UKV_ORDER_CLOSED, true ) ) { continue; }
		if ( 'high' === ukv_case_risk( $oid )['level'] ) { $high++; }
	}

	return [
		'overall'             => $overall,
		'by_destination'      => $by,
		'avg_processing_days' => $proc_n ? $proc_sum / $proc_n : 0.0,
		'high_risk_open'      => $high,
	];
}

/**
 * Risk score for an OPEN order. Returns [ level => high|medium|low, reasons => string[] ].
 *   A = destination rejection_rate >= 0.30 with resolved_count >= 3.
 *   B = an open blocker (ukv_blocker not in ['','none']).
 *   C = travel date within 14 days.
 * high if A AND B; medium if exactly one of A/B (C alone never exceeds medium); else low.
 */
function ukv_case_risk( $order_id ) {
	$reasons = [];
	$slug    = ukv_dest_slug( get_post_meta( $order_id, 'ukv_destination', true ) );

	// Factor A — high-rejection destination.
	list( $rate, $resolved ) = ukv_dest_rejection_rate( $slug );
	$factor_a = ( $resolved >= 3 && $rate >= 0.30 );
	if ( $factor_a ) {
		$reasons[] = sprintf( 'High refusal destination (%.0f%% over %d resolved cases)', $rate * 100, $resolved );
	}

	// Factor B — open blocker.
	$blocker  = (string) get_post_meta( $order_id, 'ukv_blocker', true );
	$factor_b = ( '' !== $blocker && 'none' !== $blocker );
	if ( $factor_b ) {
		$label = ( defined( 'UKV_BLOCKERS' ) && isset( UKV_BLOCKERS[ $blocker ] ) ) ? UKV_BLOCKERS[ $blocker ] : $blocker;
		$reasons[] = 'Open blocker: ' . $label;
	}

	// Factor C — near travel date.
	$travel   = (string) get_post_meta( $order_id, 'ukv_travel_date', true );
	$factor_c = false;
	if ( $travel && false !== strtotime( $travel ) ) {
		$days = ( strtotime( $travel ) - time() ) / 86400;
		if ( $days >= 0 && $days <= 14 ) { $factor_c = true; $reasons[] = 'Travel within 14 days'; }
	}

	if ( $factor_a && $factor_b ) {
		$level = 'high';
	} elseif ( $factor_a xor $factor_b ) {
		$level = 'medium';
	} else {
		// Neither A nor B: C alone never exceeds medium, and on its own stays low here.
		$level = 'low';
	}

	return [ 'level' => $level, 'reasons' => $reasons ];
}

/**
 * Recompute ukv_risk_flag on every OPEN order: '1' when ukv_case_risk level is 'high', else ''.
 * Returns the number of orders flagged high.
 */
function ukv_refresh_risk_flags() {
	$ids = get_posts( [ 'post_type' => 'ukv_order', 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => -1 ] );
	$flagged = 0;
	foreach ( $ids as $oid ) {
		if ( in_array( get_post_meta( $oid, 'ukv_status', true ), UKV_ORDER_CLOSED, true ) ) { continue; }
		$high = ( 'high' === ukv_case_risk( $oid )['level'] );
		update_post_meta( $oid, 'ukv_risk_flag', $high ? '1' : '' );
		if ( $high ) { $flagged++; }
	}
	return $flagged;
}
add_action( 'ukv_refresh_risk', 'ukv_refresh_risk_flags' );
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ukv_refresh_risk' ) ) {
		wp_schedule_event( time() + 300, 'daily', 'ukv_refresh_risk' );
	}
} );

/** Dashboard widget: success intelligence summary. */
add_action( 'wp_dashboard_setup', function () {
	wp_add_dashboard_widget( 'ukv_insights_widget', 'UKV — Success intelligence', function () {
		$s = ukv_success_stats();

		echo '<p><strong>Overall success rate:</strong> ' . esc_html( number_format( $s['overall']['rate'] * 100, 1 ) ) . '% '
			. '<span style="color:#666">(' . (int) $s['overall']['success'] . ' won/delivered, ' . (int) $s['overall']['fail'] . ' rejected/refunded)</span></p>';
		echo '<p><strong>Avg processing time:</strong> ' . esc_html( number_format( $s['avg_processing_days'], 1 ) ) . ' days '
			. '&middot; <strong>High-risk open cases:</strong> ' . (int) $s['high_risk_open'] . '</p>';

		// Top 5 destinations by rejection rate (only those with resolved data).
		$rows = [];
		foreach ( $s['by_destination'] as $slug => $r ) {
			if ( '' === $slug ) { continue; }
			$rows[ $slug ] = (float) $r['rejection_rate'];
		}
		arsort( $rows );
		$rows = array_slice( $rows, 0, 5, true );

		if ( $rows ) {
			echo '<p style="margin-bottom:2px"><strong>Top destinations by refusal rate</strong></p><ul style="margin-left:1em">';
			foreach ( $rows as $slug => $rate ) {
				echo '<li>' . esc_html( ucwords( str_replace( '-', ' ', $slug ) ) ) . ': '
					. esc_html( number_format( $rate * 100, 1 ) ) . '%</li>';
			}
			echo '</ul>';
		} else {
			echo '<p><em>No resolved cases yet.</em></p>';
		}
	} );
} );
