<?php
/**
 * Test: UKV Appointment Booking (Production Line Phase 5, #81).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-appointments.php"
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );
$check( function_exists( 'ukv_appointment_status' ), 'ukv_appointment_status() is defined' );
$check( function_exists( 'ukv_set_appointment' ), 'ukv_set_appointment() is defined' );
$check( function_exists( 'ukv_appointment_pack' ), 'ukv_appointment_pack() is defined' );

$created = [];

// 1. Create an order; status defaults to 'not_required'.
$oid = ukv_create_order( [
	'order_ref'   => 'UKV-APT-' . substr( (string) time(), -6 ),
	'name'        => 'Appointment Tester',
	'email'       => 'apt.tester@example.com',
	'destination' => 'Egypt',
	'tier'        => 'Standard',
] );
$created[] = $oid;
$check( $oid > 0, "created order (#{$oid})" );
$check( ukv_appointment_status( $oid ) === 'not_required', "status defaults to 'not_required'; got '" . ukv_appointment_status( $oid ) . "'" );

// 2. Set the appointment; status, ref and a journey note update.
ukv_set_appointment( $oid, [ 'centre' => 'VFS London', 'ref' => 'APT-99', 'date' => '2026-07-10', 'status' => 'booked' ] );
$check( ukv_appointment_status( $oid ) === 'booked', "status === 'booked' after ukv_set_appointment; got '" . ukv_appointment_status( $oid ) . "'" );
$check( get_post_meta( $oid, 'ukv_appointment_ref', true ) === 'APT-99', "ukv_appointment_ref === 'APT-99'; got '" . get_post_meta( $oid, 'ukv_appointment_ref', true ) . "'" );
$check( get_post_meta( $oid, 'ukv_appointment_centre', true ) === 'VFS London', 'ukv_appointment_centre === VFS London' );
$check( get_post_meta( $oid, 'ukv_appointment_at', true ) === '2026-07-10', 'ukv_appointment_at === 2026-07-10' );

$journey = (array) get_post_meta( $oid, 'ukv_journey', true );
$note_text = '';
foreach ( $journey as $n ) { $note_text .= ' ' . ( $n['text'] ?? '' ); }
$check( strpos( $note_text, 'Appointment' ) !== false, 'a journey note mentions "Appointment"' );
$check( strpos( $note_text, 'VFS London' ) !== false, 'a journey note mentions "VFS London"' );

// 3. Appointment pack contains the key content + compliance line.
$packs = ukv_appointment_pack( $oid );
$check( is_string( $packs ) && $packs !== '', 'ukv_appointment_pack() returns a non-empty string' );
$check( strpos( $packs, 'VFS London' ) !== false, 'pack contains the centre (VFS London)' );
$check( strpos( $packs, 'APT-99' ) !== false, 'pack contains the ref (APT-99)' );
$check( stripos( $packs, 'passport' ) !== false, 'pack contains "passport"' );
$check( strpos( $packs, 'not a government website' ) !== false, 'pack contains the compliance line "not a government website"' );
$check( strpos( $packs, 'Appointment Tester' ) !== false, 'pack greets the customer by name' );

// 4. Clean up.
foreach ( $created as $id ) { if ( $id ) { wp_delete_post( $id, true ); } }
echo 'INFO — cleaned up orders: ' . implode( ', ', array_map( fn( $i ) => "#{$i}", $created ) ) . "\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
