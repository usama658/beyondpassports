<?php
/**
 * Test: UKV Tracker (public customer status tracker — privacy-critical).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-tracker.php"
 */

$pass = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_tracker_lookup' ), 'ukv_tracker_lookup() is defined' );
$check( function_exists( 'ukv_tracker_view' ), 'ukv_tracker_view() is defined' );
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );

// 1. Create an order (ref TRK-1, real@x.com, Egypt, doc_review) + an open barrier.
$oid = ukv_create_order( [
	'order_ref'   => 'TRK-1',
	'name'        => 'Real Person',
	'email'       => 'real@x.com',
	'destination' => 'Egypt',
	'tier'        => 'Standard',
	'total'       => 49,
] );
$check( $oid > 0, "created order TRK-1 (#{$oid})" );
update_post_meta( $oid, 'ukv_status', 'doc_review' );
update_post_meta( $oid, 'ukv_passport_number', 'X9999' );
update_post_meta( $oid, 'ukv_value_note', 'risk: upsell express tier' );

$barrier_id = 0;
if ( function_exists( 'ukv_barrier_create' ) ) {
	$barrier_id = ukv_barrier_create( [
		'nature'      => 'temporary',
		'scope'       => 'case',
		'destination' => 'egypt',
		'order_ref'   => 'TRK-1',
		'guidance'    => 'Portal briefly down; no action needed.',
		'status'      => 'open',
	] );
}
$check( $barrier_id > 0, "created open barrier (#{$barrier_id})" );

// 2. Lookup: exact, case-insensitive email, wrong email.
$check( ukv_tracker_lookup( 'TRK-1', 'real@x.com' ) === $oid, 'lookup TRK-1 / real@x.com returns the order id' );
$check( ukv_tracker_lookup( 'TRK-1', 'REAL@x.com' ) === $oid, 'lookup is case-insensitive on email' );
$check( ukv_tracker_lookup( 'TRK-1', 'wrong@x.com' ) === null, 'lookup with wrong email returns null' );
$check( ukv_tracker_lookup( 'NOPE-9', 'real@x.com' ) === null, 'lookup with non-existent ref returns null' );

// 3. View renders client-safe content only.
$html = ukv_tracker_view( $oid );
$check( strpos( $html, 'In document review' ) !== false, 'view shows friendly status "In document review"' );
$check( strpos( $html, 'Portal briefly down' ) !== false, 'view shows the open barrier guidance' );
$check( strpos( $html, 'Independent service' ) !== false, 'view shows the compliance footer "Independent service"' );
$check( strpos( $html, 'TRK-1' ) !== false, 'view shows the order ref' );
$check( strpos( $html, 'Egypt' ) !== false, 'view shows the destination' );

// Privacy: never leak internal fields.
$check( strpos( $html, 'X9999' ) === false, 'view does NOT contain the passport number (X9999)' );
$check( stripos( $html, 'risk' ) === false, 'view does NOT contain "risk" (value/risk note leak)' );
$check( strpos( $html, 'real@x.com' ) === false, 'view does NOT contain the customer email' );
$check( stripos( $html, 'upsell' ) === false, 'view does NOT contain the value/upsell note' );

// 4. Clean up.
wp_delete_post( $oid, true );
if ( $barrier_id ) { wp_delete_post( $barrier_id, true ); }
echo "INFO — cleaned up order #{$oid} and barrier #{$barrier_id}\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
