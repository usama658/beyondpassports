<?php
/**
 * Real hook/cron trigger harness — closes the "side-effects via real triggers" gap.
 * Instead of calling the unit functions directly, this fires the ACTUAL WordPress
 * actions (do_action) and the status-change entry point, then asserts the side effects.
 *
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file automation/test-triggers.php
 *
 * SAFETY: blanks ukv_hubspot_token + ukv_anthropic_key for the duration (save+restore)
 *         so no real CRM record / AI call can fire.
 */

function t_assert( $cond, $msg ) {
	echo ( $cond ? 'PASS' : 'FAIL' ) . " — $msg\n";
	if ( ! $cond ) { $GLOBALS['t_fail'] = true; }
}
$GLOBALS['t_fail'] = false;

/* ---- SAFETY: save + blank live tokens ---- */
$saved_hs = get_option( 'ukv_hubspot_token', '' );
$saved_ak = get_option( 'ukv_anthropic_key', '' );
update_option( 'ukv_hubspot_token', '' );
update_option( 'ukv_anthropic_key', '' );
echo 'INFO — blanked ukv_hubspot_token (had value: ' . ( '' !== $saved_hs ? 'yes' : 'no' ) . '), ukv_anthropic_key (had value: ' . ( '' !== $saved_ak ? 'yes' : 'no' ) . ")\n\n";

$created = [];

/** Helper: make an order, optionally backdate its post_date (drives the SLA clock). */
function t_order( $ref, $destName, $status, $tier = 'Standard', $days_old = 0, &$created ) {
	$oid = ukv_create_order( [ 'order_ref' => $ref, 'name' => 'Trig Test', 'email' => 'trig@test.local', 'destination' => $destName, 'tier' => $tier, 'total' => 49 ] );
	update_post_meta( $oid, 'ukv_status', $status );
	if ( $days_old ) {
		$d = gmdate( 'Y-m-d H:i:s', time() - $days_old * 86400 );
		wp_update_post( [ 'ID' => $oid, 'post_date' => $d, 'post_date_gmt' => $d ] );
	}
	$created[] = $oid;
	return $oid;
}

/** Count OPEN barriers carrying a given rule_key. */
function t_barriers_with_rule( $rule_key ) {
	return get_posts( [
		'post_type' => 'ukv_barrier', 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => -1,
		'meta_query' => [
			[ 'key' => 'rule_key', 'value' => $rule_key ],
			[ 'key' => 'status', 'value' => 'open' ],
		],
	] );
}

/* =========================================================================
 * 1) ukv_barriers_detect (cron action) -> SLA-breach barrier, idempotent.
 * ====================================================================== */
echo "== Trigger: do_action('ukv_barriers_detect') ==\n";
$sla_oid = t_order( 'T-TRIG-SLA', 'Egypt', 'paid', 'Standard', 5, $created ); // 5 days old vs 72h Standard SLA -> breach

do_action( 'ukv_barriers_detect' );
$after1 = t_barriers_with_rule( 'T-TRIG-SLA:sla_breach' );
t_assert( count( $after1 ) === 1, 'real ukv_barriers_detect hook created exactly 1 open SLA-breach barrier (T-TRIG-SLA:sla_breach)' );

do_action( 'ukv_barriers_detect' );
$after2 = t_barriers_with_rule( 'T-TRIG-SLA:sla_breach' );
t_assert( count( $after2 ) === 1, 'second hook run is idempotent — still exactly 1 SLA-breach barrier' );

/* =========================================================================
 * 2) ukv_refresh_risk (cron action) -> ukv_risk_flag === '1' on a high-risk open order.
 *    Factor A: Egypt rejection_rate >= 0.30 over >= 3 resolved (3 rejected + 1 won).
 *    Factor B: open blocker 'docs_missing'. A && B => 'high' => flag '1'.
 * ====================================================================== */
echo "\n== Trigger: do_action('ukv_refresh_risk') ==\n";
t_order( 'T-TRIG-RJ1', 'Egypt', 'rejected', 'Standard', 0, $created );
t_order( 'T-TRIG-RJ2', 'Egypt', 'rejected', 'Standard', 0, $created );
t_order( 'T-TRIG-RJ3', 'Egypt', 'rejected', 'Standard', 0, $created );
t_order( 'T-TRIG-WON', 'Egypt', 'won',      'Standard', 0, $created ); // 4 resolved, 75% rejection

$risk_oid = t_order( 'T-TRIG-RISK', 'Egypt', 'paid', 'Standard', 0, $created );
update_post_meta( $risk_oid, 'ukv_blocker', 'docs_missing' );

do_action( 'ukv_refresh_risk' );
$flag = (string) get_post_meta( $risk_oid, 'ukv_risk_flag', true );
t_assert( '1' === $flag, "real ukv_refresh_risk hook set ukv_risk_flag === '1' on the high-risk open Egypt order (got '" . $flag . "')" );

/* =========================================================================
 * 3) Email status-change core -> ukv_email_sent + ukv_email_log recorded.
 *    'submitted' then 'delivered' (delivered fires delivered + review_request).
 * ====================================================================== */
echo "\n== Trigger: ukv_email_on_status_change() ==\n";
$mail_oid = t_order( 'T-TRIG-MAIL', 'Egypt', 'paid', 'Standard', 0, $created );

ukv_email_on_status_change( $mail_oid, 'submitted' );
ukv_email_on_status_change( $mail_oid, 'delivered' );

$sent = (array) get_post_meta( $mail_oid, 'ukv_email_sent', true );
$log  = (array) get_post_meta( $mail_oid, 'ukv_email_log', true );

t_assert( in_array( 'submitted', $sent, true ),      "ukv_email_sent records 'submitted'" );
t_assert( in_array( 'delivered', $sent, true ),      "ukv_email_sent records 'delivered'" );
t_assert( in_array( 'review_request', $sent, true ), "ukv_email_sent records 'review_request' (fired by 'delivered')" );
$log_events = array_map( fn( $e ) => $e['event'] ?? '', $log );
t_assert( count( $log ) >= 3 && in_array( 'submitted', $log_events, true ) && in_array( 'delivered', $log_events, true ) && in_array( 'review_request', $log_events, true ),
	'ukv_email_log recorded entries for submitted + delivered + review_request (' . count( $log ) . ' entries)' );

/* ----------------------------------------------------------------- cleanup */
// Sweep ALL barriers tied to our test refs (auto-detected ones too).
$sweep = [];
foreach ( ukv_open_barriers() as $b ) {
	$ref = (string) get_post_meta( $b, 'order_ref', true );
	if ( 0 === strpos( $ref, 'T-TRIG-' ) ) { $sweep[] = $b; }
}
foreach ( array_unique( $sweep ) as $b ) { wp_delete_post( $b, true ); }
foreach ( array_unique( $created ) as $o ) { wp_delete_post( $o, true ); }

/* ---- restore live tokens ---- */
update_option( 'ukv_hubspot_token', $saved_hs );
update_option( 'ukv_anthropic_key', $saved_ak );
echo "\nINFO — cleaned up " . count( $created ) . ' orders + ' . count( $sweep ) . " barriers; restored live tokens\n";

echo $GLOBALS['t_fail'] ? "\nRESULT: FAILURES PRESENT\n" : "\nRESULT: ALL PASS\n";
