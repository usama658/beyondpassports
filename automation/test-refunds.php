<?php
/**
 * Test: UKV Refunds / Cancellation flow (Gap #72).
 * Policy: refund OUR SERVICE FEE only; govt fee is non-refundable.
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-refunds.php"
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );
$check( function_exists( 'ukv_refund_amount' ), 'ukv_refund_amount() is defined' );
$check( function_exists( 'ukv_process_refund' ), 'ukv_process_refund() is defined' );

$created = [];

// 1. Refund amount = service fee only (govt fee never refunded).
$oid = ukv_create_order( [
	'order_ref'   => 'UKV-RFND-' . substr( (string) time(), -6 ),
	'name'        => 'Refund Tester',
	'email'       => 'refund.tester@example.com',
	'destination' => 'Egypt',
	'tier'        => 'Standard',
	'service_fee' => 49,
	'govt_fee'    => 25,
	'total'       => 74,
] );
$created[] = $oid;
$check( $oid > 0, "created order with service_fee=49 (#{$oid})" );
$amt = ukv_refund_amount( $oid );
$check( $amt === 49.0, "refund amount === 49.0 (service fee only); got {$amt}" );

// 2. Process refund records status + reason + journey audit.
$before = count( (array) get_post_meta( $oid, 'ukv_journey', true ) );
$res    = ukv_process_refund( $oid, 'Government refusal' );
$check( is_array( $res ) && $res['amount'] === 49.0, "process_refund returns amount 49.0; got " . var_export( $res['amount'] ?? null, true ) );
$check( ( $res['status'] ?? '' ) === 'refunded', "process_refund returns status 'refunded'" );
$check( get_post_meta( $oid, 'ukv_status', true ) === 'refunded', "order ukv_status === 'refunded'" );
$check( get_post_meta( $oid, 'ukv_refund_amount', true ) == 49.0, 'order ukv_refund_amount meta === 49.0' );
$check( get_post_meta( $oid, 'ukv_refunded_at', true ) > 0, 'order ukv_refunded_at timestamp set' );
$check( strpos( (string) get_post_meta( $oid, 'ukv_refund_reason', true ), 'refusal' ) !== false, "ukv_refund_reason contains 'refusal'" );

$journey = (array) get_post_meta( $oid, 'ukv_journey', true );
$check( count( $journey ) >= $before + 1, 'journey gained a refund audit note' );
// Locate the refund note (the email engine may append its own note too).
$refund_note = null;
foreach ( $journey as $n ) {
	if ( is_array( $n ) && strpos( (string) ( $n['text'] ?? '' ), 'Refund processed' ) !== false ) { $refund_note = $n; break; }
}
$check( $refund_note !== null, 'a journey note mentions "Refund processed"' );
$check( $refund_note && strpos( (string) $refund_note['text'], 'non-refundable' ) !== false, 'journey note mentions "non-refundable" (govt fee)' );
$check( $refund_note && ( $refund_note['agent'] ?? '' ) === 'system' && ( $refund_note['channel'] ?? '' ) === 'internal', 'refund note agent=system, channel=internal' );

// 3. Fallback: no service_fee, total=74, govt_fee=25 -> 49.0.
$oid2 = ukv_create_order( [
	'order_ref'   => 'UKV-RFB-' . substr( (string) time(), -6 ),
	'name'        => 'Fallback Tester',
	'email'       => 'fallback.tester@example.com',
	'destination' => 'Egypt',
	'tier'        => 'Standard',
	'govt_fee'    => 25,
	'total'       => 74,
] );
$created[] = $oid2;
delete_post_meta( $oid2, 'ukv_service_fee' ); // ensure unset for fallback path
$amt2 = ukv_refund_amount( $oid2 );
$check( $amt2 === 49.0, "fallback refund amount === 49.0 (total - govt_fee); got {$amt2}" );

// 4. Clean up.
foreach ( $created as $id ) { if ( $id ) { wp_delete_post( $id, true ); } }
echo 'INFO — cleaned up orders: ' . implode( ', ', array_map( fn( $i ) => "#{$i}", $created ) ) . "\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
