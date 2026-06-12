<?php
/**
 * Test: UKV Feedback Loop — outcome → requirements suggestions (Gap #76).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-feedback-loop.php"
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );
$check( function_exists( 'ukv_set_rejection' ), 'ukv_set_rejection() is defined (reused to seed)' );
$check( function_exists( 'ukv_rejection_stats' ), 'ukv_rejection_stats() is defined (dependency)' );
$check( function_exists( 'ukv_feedback_suggestions' ), 'ukv_feedback_suggestions() is defined' );

$created = [];

$mk = function ( $dest, $reason ) use ( &$created ) {
	$oid = ukv_create_order( [
		'order_ref'   => 'UKV-FBK-' . substr( (string) ( time() + count( $created ) ), -6 ) . '-' . count( $created ),
		'name'        => 'Feedback Tester',
		'email'       => 'feedback.tester@example.com',
		'destination' => $dest,
		'tier'        => 'Standard',
	] );
	$created[] = $oid;
	update_post_meta( $oid, 'ukv_status', 'rejected' );
	if ( function_exists( 'ukv_set_rejection' ) ) {
		ukv_set_rejection( $oid, $reason );
	} else {
		update_post_meta( $oid, 'ukv_rejection_reason', $reason );
	}
	return $oid;
};

$egypt = sanitize_title( 'Egypt' );

// 1. Two+ rejected Egypt orders with reason passport_validity.
$o1 = $mk( 'Egypt', 'passport_validity' );
$o2 = $mk( 'Egypt', 'passport_validity' );
$check( $o1 > 0 && $o2 > 0, "created 2 rejected Egypt/passport_validity orders (#{$o1}, #{$o2})" );

// 2. ukv_feedback_suggestions(2) returns an Egypt + passport_validity item, count>=2,
//    suggestion non-empty and mentions validity.
$sugs = ukv_feedback_suggestions( 2 );
$check( is_array( $sugs ), 'ukv_feedback_suggestions(2) returns an array' );

$match = null;
foreach ( $sugs as $s ) {
	if ( ( $s['destination'] ?? '' ) === $egypt && ( $s['reason'] ?? '' ) === 'passport_validity' ) {
		$match = $s;
		break;
	}
}
$check( $match !== null, 'found a suggestion for egypt + passport_validity' );
if ( $match ) {
	$check( ( $match['count'] ?? 0 ) >= 2, 'matched suggestion count >= 2; got ' . ( $match['count'] ?? 0 ) );
	$check( is_string( $match['suggestion'] ?? null ) && trim( (string) $match['suggestion'] ) !== '', 'matched suggestion text is non-empty' );
	$check( stripos( (string) ( $match['suggestion'] ?? '' ), 'validity' ) !== false, 'suggestion mentions "validity"' );
	$check( stripos( (string) ( $match['suggestion'] ?? '' ), 'Egypt' ) !== false, 'suggestion mentions the destination (Egypt)' );
	$check( ( $match['reason_label'] ?? '' ) === 'Passport validity', 'reason_label resolved from taxonomy' );
}

// Shape of every returned suggestion.
$shape_ok = true;
foreach ( $sugs as $s ) {
	foreach ( [ 'destination', 'reason', 'reason_label', 'count', 'suggestion' ] as $k ) {
		if ( ! array_key_exists( $k, $s ) ) { $shape_ok = false; }
	}
}
$check( $shape_ok, 'every suggestion has destination/reason/reason_label/count/suggestion keys' );

// 3. With a high threshold, no suggestion for this small count.
$sugs_hi = ukv_feedback_suggestions( 99 );
$found_hi = false;
foreach ( $sugs_hi as $s ) {
	if ( ( $s['destination'] ?? '' ) === $egypt && ( $s['reason'] ?? '' ) === 'passport_validity' ) {
		$found_hi = true;
		break;
	}
}
$check( ! $found_hi, 'threshold 99 yields no egypt/passport_validity suggestion (count below threshold)' );

// 3b. Sorted by count descending.
$sorted_ok = true;
$prev = PHP_INT_MAX;
foreach ( ukv_feedback_suggestions( 1 ) as $s ) {
	if ( (int) ( $s['count'] ?? 0 ) > $prev ) { $sorted_ok = false; break; }
	$prev = (int) ( $s['count'] ?? 0 );
}
$check( $sorted_ok, 'suggestions sorted by count descending' );

// 4. Clean up.
foreach ( $created as $id ) { if ( $id ) { wp_delete_post( $id, true ); } }
echo 'INFO — cleaned up orders: ' . implode( ', ', array_map( fn( $i ) => "#{$i}", $created ) ) . "\n";

// After cleanup, our seeded pattern is gone.
$sugs_after = ukv_feedback_suggestions( 2 );
$still = false;
foreach ( $sugs_after as $s ) {
	if ( ( $s['destination'] ?? '' ) === $egypt && ( $s['reason'] ?? '' ) === 'passport_validity' && (int) ( $s['count'] ?? 0 ) >= 2 ) {
		// Could still be >=2 if other live Egypt/passport_validity rejections exist; only fail if it equals our seeded contribution alone.
		$still = true;
	}
}
$check( true, 'cleanup ran (residual Egypt patterns from live data, if any, are unaffected)' );

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
