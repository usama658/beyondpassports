<?php
/**
 * Test: UKV Passport Return (tracked passport-return logistics — Gap #86).
 * Policy: sticker-visa passports returned by tracked + insured courier (we pay).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-passport-return.php"
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );
$check( function_exists( 'ukv_return_status' ), 'ukv_return_status() is defined' );
$check( function_exists( 'ukv_set_return' ), 'ukv_set_return() is defined' );

$created = [];

// 1. New order has return status 'none'.
$oid = ukv_create_order( [
	'order_ref'   => 'UKV-PRET-' . substr( (string) time(), -6 ),
	'name'        => 'Return Tester',
	'email'       => 'return.tester@example.com',
	'destination' => 'Egypt',
	'tier'        => 'Standard',
	'total'       => 99,
] );
$created[] = $oid;
$check( $oid > 0, "created order (#{$oid})" );
$check( ukv_return_status( $oid ) === 'none', "fresh order ukv_return_status === 'none'; got '" . ukv_return_status( $oid ) . "'" );

// 2. Dispatch: set carrier/tracking/status -> dispatched stamps + journey note.
$j0     = get_post_meta( $oid, 'ukv_journey', true );
$before = is_array( $j0 ) ? count( $j0 ) : 0;
ukv_set_return( $oid, [
	'carrier'  => 'Royal Mail Special Delivery',
	'tracking' => 'RM123GB',
	'status'   => 'dispatched',
] );

$check( ukv_return_status( $oid ) === 'dispatched', "ukv_return_status === 'dispatched'" );
$check( get_post_meta( $oid, 'ukv_return_carrier', true ) === 'Royal Mail Special Delivery', "ukv_return_carrier stored" );
$check( get_post_meta( $oid, 'ukv_return_tracking', true ) === 'RM123GB', "ukv_return_tracking === 'RM123GB'" );
$check( (int) get_post_meta( $oid, 'ukv_return_dispatched_at', true ) > 0, 'ukv_return_dispatched_at timestamp set' );

$journey = (array) get_post_meta( $oid, 'ukv_journey', true );
$check( count( $journey ) >= $before + 1, 'journey gained a dispatch audit note' );
$note = null;
foreach ( $journey as $n ) {
	if ( is_array( $n ) && stripos( (string) ( $n['text'] ?? '' ), 'dispatched' ) !== false ) { $note = $n; break; }
}
$check( $note !== null, 'a journey note mentions "dispatched"' );
$check( $note && strpos( (string) $note['text'], 'RM123GB' ) !== false, 'dispatch note contains the tracking number RM123GB' );
$check( $note && strpos( (string) $note['text'], 'Royal Mail Special Delivery' ) !== false, 'dispatch note contains the carrier' );

// 3. Clean up.
foreach ( $created as $id ) { if ( $id ) { wp_delete_post( $id, true ); } }
echo 'INFO — cleaned up orders: ' . implode( ', ', array_map( fn( $i ) => "#{$i}", $created ) ) . "\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
