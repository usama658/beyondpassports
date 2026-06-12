<?php
/**
 * Test: UKV Premium Appointment Slot (paid add-on, #84).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-premium-slot.php"
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );
$check( function_exists( 'ukv_has_premium_slot' ), 'ukv_has_premium_slot() is defined' );
$check( function_exists( 'ukv_add_premium_slot' ), 'ukv_add_premium_slot() is defined' );
$check( function_exists( 'ukv_remove_premium_slot' ), 'ukv_remove_premium_slot() is defined' );

$created = [];

// 1. Create an order; no premium slot yet.
$oid = ukv_create_order( [
	'order_ref'   => 'UKV-PSLOT-' . substr( (string) time(), -6 ),
	'name'        => 'Premium Slot Tester',
	'email'       => 'pslot.tester@example.com',
	'destination' => 'Egypt',
	'tier'        => 'Standard',
	'total'       => 199.0,
] );
$created[] = $oid;
$check( $oid > 0, "created order (#{$oid})" );
$check( ukv_has_premium_slot( $oid ) === false, 'ukv_has_premium_slot() is false before adding' );

$total_before = (float) get_post_meta( $oid, 'ukv_total', true );

// 2. Add premium slot at GBP75.
ukv_add_premium_slot( $oid, 75.0 );
$check( ukv_has_premium_slot( $oid ) === true, 'ukv_has_premium_slot() is true after adding' );
$check( (float) get_post_meta( $oid, 'ukv_premium_slot_fee', true ) === 75.0, "ukv_premium_slot_fee === 75.0; got '" . get_post_meta( $oid, 'ukv_premium_slot_fee', true ) . "'" );
$check( (int) get_post_meta( $oid, 'ukv_premium_slot_added_at', true ) > 0, 'ukv_premium_slot_added_at is stamped' );

// Service total must NOT change — the fee is recorded separately.
$total_after = (float) get_post_meta( $oid, 'ukv_total', true );
$check( $total_after === $total_before, "ukv_total unchanged ({$total_before} === {$total_after}) — fee recorded separately" );

// Journey audit note mentions Premium + 75.
$journey = (array) get_post_meta( $oid, 'ukv_journey', true );
$note_text = '';
foreach ( $journey as $n ) { $note_text .= ' ' . ( $n['text'] ?? '' ); }
$check( strpos( $note_text, 'Premium' ) !== false, 'a journey note mentions "Premium"' );
$check( strpos( $note_text, '75' ) !== false, 'a journey note mentions "75"' );

// 3. Remove premium slot.
ukv_remove_premium_slot( $oid );
$check( ukv_has_premium_slot( $oid ) === false, 'ukv_has_premium_slot() is false after removing' );

// 4. Clean up.
foreach ( $created as $id ) { if ( $id ) { wp_delete_post( $id, true ); } }
echo 'INFO — cleaned up orders: ' . implode( ', ', array_map( fn( $i ) => "#{$i}", $created ) ) . "\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
