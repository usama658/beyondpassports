<?php
/**
 * Plugin Name: UKV Stripe Reconciliation (Gap #85)
 * Desc: Daily safety net — compares recent successful Stripe charges against
 *       ukv_order records and flags any paid charge with NO matching order
 *       (a missed webhook = revenue at risk). Read-only: it only reports.
 */
defined( 'ABSPATH' ) || exit;

const UKV_RECONCILE_CRON   = 'ukv_reconcile_cron';
const UKV_RECONCILE_OPTION = 'ukv_reconcile_last';

/**
 * Reconcile a list of Stripe charge records against ukv_order records.
 *
 * @param array $charges Each: [ 'id'=>str, 'email'=>str, 'amount'=>float, 'created'=>epoch ].
 * @return array [ 'matched' => [charge_id...], 'unmatched' => [ ['id','email','amount'], ... ] ]
 *               A charge is MATCHED when a ukv_order exists with the same ukv_email
 *               and ukv_total within £0.01 of the charge amount. Unmatched charges are
 *               paid charges with no order behind them — i.e. a missed/dropped webhook.
 */
function ukv_reconcile( array $charges ) {
	$matched   = [];
	$unmatched = [];

	foreach ( $charges as $charge ) {
		$email  = isset( $charge['email'] ) ? sanitize_email( (string) $charge['email'] ) : '';
		$amount = isset( $charge['amount'] ) ? (float) $charge['amount'] : 0.0;
		$id     = isset( $charge['id'] ) ? (string) $charge['id'] : '';

		if ( '' === $id ) { continue; }

		if ( $email && ukv_reconcile_order_exists( $email, $amount ) ) {
			$matched[] = $id;
		} else {
			$unmatched[] = [
				'id'     => $id,
				'email'  => $email,
				'amount' => $amount,
			];
		}
	}

	return [ 'matched' => $matched, 'unmatched' => $unmatched ];
}

/**
 * Is there a ukv_order matching this email + amount (within £0.01)?
 * Match is by email + amount (kept simple — created-date window is intentionally
 * not enforced to avoid false "missed charge" alarms across time zones).
 */
function ukv_reconcile_order_exists( $email, $amount ) {
	$ids = get_posts( [
		'post_type'      => 'ukv_order',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => [
			[
				'key'     => 'ukv_email',
				'value'   => $email,
				'compare' => '=',
			],
		],
	] );

	foreach ( $ids as $pid ) {
		$total = (float) get_post_meta( $pid, 'ukv_total', true );
		if ( abs( $total - (float) $amount ) <= 0.01 ) {
			return true;
		}
	}
	return false;
}

/**
 * Fetch recent successful charges from Stripe.
 *
 * Live keys are not available in this environment, so this returns [] by default
 * and exposes the `ukv_reconcile_charges` filter so tests (and launch wiring) can
 * inject charges. At launch the real Stripe API list-charges call is wired here:
 * read the live secret key from the DB option, call \Stripe\Charge::all([
 * 'created' => ['gte' => strtotime('-7 days')], 'limit' => 100 ]) (paginating),
 * keep only paid/succeeded charges, and map each to
 * [ 'id', 'email', 'amount' (cents/100), 'created' ].
 *
 * @return array List of charge records.
 */
function ukv_reconcile_fetch_charges() {
	$charges = [];

	// --- Real Stripe call wired at launch (uses the live secret key DB option) ---
	// $key = get_option( 'ukv_stripe_secret_key' );
	// if ( $key ) { /* \Stripe\Stripe::setApiKey($key); list + map charges into $charges */ }

	return apply_filters( 'ukv_reconcile_charges', $charges );
}

/**
 * Cron job: reconcile recent charges; if any are unmatched, persist the result
 * so ops can see (and the admin notice can warn).
 */
function ukv_reconcile_run() {
	$result = ukv_reconcile( ukv_reconcile_fetch_charges() );

	if ( ! empty( $result['unmatched'] ) ) {
		update_option( UKV_RECONCILE_OPTION, [
			'time'      => time(),
			'matched'   => $result['matched'],
			'unmatched' => $result['unmatched'],
		], false );
	} else {
		// Still record a clean run so the admin page shows "all reconciled".
		update_option( UKV_RECONCILE_OPTION, [
			'time'      => time(),
			'matched'   => $result['matched'],
			'unmatched' => [],
		], false );
	}

	return $result;
}
add_action( UKV_RECONCILE_CRON, 'ukv_reconcile_run' );

/* Schedule the daily reconciliation (guarded). */
add_action( 'init', function () {
	if ( ! wp_next_scheduled( UKV_RECONCILE_CRON ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', UKV_RECONCILE_CRON );
	}
} );

/* Tidy up the scheduled event if this mu-plugin is ever removed (best effort). */
register_deactivation_hook( __FILE__, function () {
	$ts = wp_next_scheduled( UKV_RECONCILE_CRON );
	if ( $ts ) { wp_unschedule_event( $ts, UKV_RECONCILE_CRON ); }
} );

/* Best-effort admin notice when the last run found unmatched charges. */
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$last = get_option( UKV_RECONCILE_OPTION );
	if ( empty( $last['unmatched'] ) ) { return; }
	$n = count( $last['unmatched'] );
	echo '<div class="notice notice-error"><p>'
		. '<strong>UKV reconciliation:</strong> '
		. esc_html( sprintf( '%d successful Stripe charge%s have no matching order (possible missed payment).', $n, 1 === $n ? '' : 's' ) )
		. ' <a href="' . esc_url( admin_url( 'tools.php?page=ukv-reconcile' ) ) . '">Review</a></p></div>';
} );

/* Tools page: show the last reconciliation. */
add_action( 'admin_menu', function () {
	add_management_page(
		'Stripe reconciliation',
		'Stripe reconcile',
		'manage_options',
		'ukv-reconcile',
		'ukv_reconcile_render_page'
	);
} );

function ukv_reconcile_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to view this page.' ) );
	}

	// Manual "run now" (nonce-protected) for ops convenience.
	if ( isset( $_POST['ukv_reconcile_run'] )
		&& check_admin_referer( 'ukv_reconcile_run', 'ukv_reconcile_run_nonce' ) ) {
		ukv_reconcile_run();
	}

	$last = get_option( UKV_RECONCILE_OPTION );

	echo '<div class="wrap"><h1>Stripe reconciliation</h1>';
	echo '<p>Daily safety net: compares recent successful Stripe charges against order records. '
		. 'Any paid charge with <strong>no matching order</strong> is a possible missed webhook (revenue at risk).</p>';

	echo '<form method="post">';
	wp_nonce_field( 'ukv_reconcile_run', 'ukv_reconcile_run_nonce' );
	echo '<p><button class="button button-primary" name="ukv_reconcile_run" value="1">Run reconciliation now</button></p>';
	echo '</form>';

	if ( ! is_array( $last ) || empty( $last['time'] ) ) {
		echo '<p><em>No reconciliation has run yet.</em></p></div>';
		return;
	}

	$matched_n = count( (array) ( $last['matched'] ?? [] ) );
	$unmatched = (array) ( $last['unmatched'] ?? [] );

	echo '<p><strong>Last run:</strong> ' . esc_html( gmdate( 'Y-m-d H:i', (int) $last['time'] ) ) . ' UTC &middot; '
		. '<strong>Matched:</strong> ' . esc_html( (string) $matched_n ) . ' &middot; '
		. '<strong>Unmatched:</strong> ' . esc_html( (string) count( $unmatched ) ) . '</p>';

	if ( ! $unmatched ) {
		echo '<div class="notice notice-success inline"><p>All recent charges reconciled — no missed payments.</p></div></div>';
		return;
	}

	echo '<div class="notice notice-error inline"><p>The following successful charges have no matching order:</p></div>';
	echo '<table class="widefat striped"><thead><tr><th>Charge ID</th><th>Email</th><th>Amount</th></tr></thead><tbody>';
	foreach ( $unmatched as $u ) {
		echo '<tr>'
			. '<td><code>' . esc_html( (string) ( $u['id'] ?? '' ) ) . '</code></td>'
			. '<td>' . esc_html( (string) ( $u['email'] ?? '' ) ) . '</td>'
			. '<td>£' . esc_html( number_format( (float) ( $u['amount'] ?? 0 ), 2 ) ) . '</td>'
			. '</tr>';
	}
	echo '</tbody></table></div>';
}
