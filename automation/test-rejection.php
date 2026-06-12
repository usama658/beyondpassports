<?php
/**
 * Test: UKV Rejection-reason capture + analytics (Gap #73).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-rejection.php"
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions + taxonomy present.
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );
$check( function_exists( 'ukv_set_rejection' ), 'ukv_set_rejection() is defined' );
$check( function_exists( 'ukv_rejection_stats' ), 'ukv_rejection_stats() is defined' );
$check( defined( 'UKV_REJECTION_REASONS' ) && is_array( UKV_REJECTION_REASONS ), 'UKV_REJECTION_REASONS taxonomy defined' );

$created = [];

$mk = function ( $dest ) use ( &$created ) {
	$oid = ukv_create_order( [
		'order_ref'   => 'UKV-REJ-' . substr( (string) ( time() + count( $created ) ), -6 ) . '-' . count( $created ),
		'name'        => 'Reject Tester',
		'email'       => 'reject.tester@example.com',
		'destination' => $dest,
		'tier'        => 'Standard',
	] );
	$created[] = $oid;
	update_post_meta( $oid, 'ukv_status', 'rejected' );
	return $oid;
};

// 1. Three rejected orders with reasons: doc_quality, doc_quality, passport_validity.
$o1 = $mk( 'Egypt' );
$o2 = $mk( 'Egypt' );
$o3 = $mk( 'India' );
$check( $o1 > 0 && $o2 > 0 && $o3 > 0, "created 3 rejected orders (#{$o1}, #{$o2}, #{$o3})" );

$check( ukv_set_rejection( $o1, 'doc_quality' ) === true, 'set_rejection o1 doc_quality returns true' );
$check( ukv_set_rejection( $o2, 'doc_quality', 'Blurry passport scan' ) === true, 'set_rejection o2 doc_quality (with note) returns true' );
$check( ukv_set_rejection( $o3, 'passport_validity' ) === true, 'set_rejection o3 passport_validity returns true' );

$check( get_post_meta( $o1, 'ukv_rejection_reason', true ) === 'doc_quality', 'o1 ukv_rejection_reason meta stored' );
$check( get_post_meta( $o2, 'ukv_rejection_note', true ) === 'Blurry passport scan', 'o2 ukv_rejection_note meta stored' );

// 2. Invalid key returns false and stores nothing.
$check( ukv_set_rejection( $o1, 'not_a_real_key' ) === false, "set_rejection invalid key returns false" );
$check( get_post_meta( $o1, 'ukv_rejection_reason', true ) === 'doc_quality', 'invalid key did NOT overwrite valid reason' );

// 3. Stats by reason + destination + total.
$stats = ukv_rejection_stats();
$check( is_array( $stats ) && isset( $stats['by_reason'], $stats['by_destination'], $stats['total'] ), 'stats shape: by_reason/by_destination/total' );
$check( ( $stats['by_reason']['doc_quality'] ?? 0 ) >= 2, "by_reason[doc_quality] >= 2; got " . ( $stats['by_reason']['doc_quality'] ?? 0 ) );
$check( ( $stats['by_reason']['passport_validity'] ?? 0 ) >= 1, "by_reason[passport_validity] >= 1; got " . ( $stats['by_reason']['passport_validity'] ?? 0 ) );
$check( ( $stats['total'] ?? 0 ) >= 3, "total >= 3; got " . ( $stats['total'] ?? 0 ) );
$egypt = sanitize_title( 'Egypt' );
$check( ( $stats['by_destination'][ $egypt ]['doc_quality'] ?? 0 ) >= 2, "by_destination[egypt][doc_quality] >= 2; got " . ( $stats['by_destination'][ $egypt ]['doc_quality'] ?? 0 ) );

// 4. Journey gained a note containing "Rejection reason".
$journey = (array) get_post_meta( $o2, 'ukv_journey', true );
$found   = false;
foreach ( $journey as $n ) {
	if ( is_array( $n ) && strpos( (string) ( $n['text'] ?? '' ), 'Rejection reason' ) !== false ) {
		$found = true;
		$check( ( $n['agent'] ?? '' ) === 'system' && ( $n['channel'] ?? '' ) === 'internal', 'rejection note agent=system, channel=internal' );
		$check( strpos( (string) $n['text'], 'Blurry passport scan' ) !== false, 'rejection note includes the free-text note' );
		break;
	}
}
$check( $found, 'a journey note contains "Rejection reason"' );

// 5. Clean up.
foreach ( $created as $id ) { if ( $id ) { wp_delete_post( $id, true ); } }
echo 'INFO — cleaned up orders: ' . implode( ', ', array_map( fn( $i ) => "#{$i}", $created ) ) . "\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
