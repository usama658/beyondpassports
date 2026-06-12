<?php
/**
 * Test: UKV Zapier order-events webhook feed.
 * NO real HTTP — every send path is intercepted via the 'ukv_zapier_pre_send' filter.
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-zapier.php"
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Safety: ensure no real hook URL is configured for the duration of the test.
$saved_url = get_option( 'ukv_zapier_hook_url', '' );
update_option( 'ukv_zapier_hook_url', '' );

// Capture slot for payloads intercepted by the filter (so no HTTP ever runs).
$GLOBALS['ukv_zap_captured'] = null;

// ----- 1. Payload shape ----------------------------------------------------
$oid = ukv_create_order( [
	'order_ref'   => 'UKV-ZAP-TEST',
	'name'        => 'Zap Tester',
	'email'       => 'zap@example.com',
	'destination' => 'Testland',
	'tier'        => 'Express',
	'total'       => 149,
] );
// ukv_create_order forces status to 'paid'.
$check( $oid > 0, "created test order (#{$oid})" );

$payload = ukv_zapier_payload( $oid );
$check( ( $payload['ref'] ?? '' ) === 'UKV-ZAP-TEST', "payload contains the order ref" );
$check( (string) ( $payload['total'] ?? '' ) === '149', "payload contains the total (149)" );
$check( ( $payload['status'] ?? '' ) === 'paid', "payload contains the status (paid)" );
$check( array_key_exists( 'event', $payload ), "payload has an 'event' key" );
$check( ( $payload['event'] ?? '' ) === 'update', "default event is 'update'" );

// ----- 2. Filter returns true + captures payload (no URL, no HTTP) ----------
$capture = function ( $pre, $order_id, $event, $payload ) {
	$GLOBALS['ukv_zap_captured'] = $payload;
	return true; // force a successful result without any POST.
};
add_filter( 'ukv_zapier_pre_send', $capture, 10, 4 );

$r = ukv_zapier_send( $oid, 'created' );
$check( true === $r, "ukv_zapier_send returns true when filter forces true (no URL set)" );
$cap = $GLOBALS['ukv_zap_captured'];
$check( is_array( $cap ) && ( $cap['event'] ?? '' ) === 'created', "captured payload has event='created'" );
$check( is_array( $cap ) && ( $cap['ref'] ?? '' ) === 'UKV-ZAP-TEST', "captured payload has the right ref" );

remove_filter( 'ukv_zapier_pre_send', $capture, 10 );

// ----- 3. Filter returns false ---------------------------------------------
$false_cb = fn() => false;
add_filter( 'ukv_zapier_pre_send', $false_cb, 10, 4 );
$check( false === ukv_zapier_send( $oid, 'created' ), "ukv_zapier_send returns false when filter forces false" );
remove_filter( 'ukv_zapier_pre_send', $false_cb, 10 );

// ----- 4. No filter, no URL -> no-op false, no fatal ------------------------
$check( false === ukv_zapier_send( $oid, 'created' ), "no filter + no URL -> returns false (no-op, no HTTP, no fatal)" );

// ----- 5. Cleanup ----------------------------------------------------------
wp_delete_post( $oid, true );
clean_post_cache( $oid );
update_option( 'ukv_zapier_hook_url', $saved_url ); // restore original option.
echo "INFO — cleaned up order #{$oid} and restored hook-url option\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
