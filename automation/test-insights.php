<?php
/**
 * Test: UKV Insights (Orders P9 — case-pattern intelligence + risk + success stats).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-insights.php"
 */

$pass = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_success_stats' ), 'ukv_success_stats() is defined' );
$check( function_exists( 'ukv_case_risk' ), 'ukv_case_risk() is defined' );
$check( function_exists( 'ukv_refresh_risk_flags' ), 'ukv_refresh_risk_flags() is defined' );
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );

$created = [];
$mk = function ( $status, $extra = [] ) use ( &$created ) {
	$oid = ukv_create_order( array_merge( [
		'order_ref'   => 'UKV-INS-' . substr( md5( uniqid( '', true ) ), 0, 8 ),
		'name'        => 'Insights Test',
		'email'       => 'insights.test@example.com',
		'destination' => 'Egypt',
		'tier'        => 'Standard',
		'total'       => 49,
	], $extra ) );
	$created[] = $oid;
	if ( null !== $status ) { update_post_meta( $oid, 'ukv_status', $status ); }
	return $oid;
};

// 1. Egypt resolved dataset: 3 won, 1 delivered (success=4), 3 rejected (fail=3).
foreach ( [ 'won', 'won', 'won', 'delivered', 'rejected', 'rejected', 'rejected' ] as $st ) { $mk( $st ); }

$s = ukv_success_stats();
$eg = $s['by_destination']['egypt'] ?? null;
$check( is_array( $eg ), 'by_destination[egypt] exists' );
$check( ( $eg['success'] ?? null ) === 4, "egypt success === 4 (got " . ( $eg['success'] ?? 'n/a' ) . ")" );
$check( ( $eg['fail'] ?? null ) === 3, "egypt fail === 3 (got " . ( $eg['fail'] ?? 'n/a' ) . ")" );
$check( abs( ( $eg['rate'] ?? -1 ) - 4 / 7 ) < 0.01, "egypt rate ≈ 4/7 (got " . round( $eg['rate'] ?? -1, 4 ) . ")" );

// rejection_rate from this data ≈ 3/7 ≈ 0.4286, resolved = 7.
$check( abs( ( $eg['rejection_rate'] ?? -1 ) - 3 / 7 ) < 0.01, "egypt rejection_rate ≈ 3/7 (got " . round( $eg['rejection_rate'] ?? -1, 4 ) . ")" );

// 2. Open Egypt order with a blocker -> factor A (rejection) + factor B (blocker) = high.
$high_oid = $mk( null, [] ); // stays 'paid' (open)
update_post_meta( $high_oid, 'ukv_blocker', 'docs_missing' );
$risk = ukv_case_risk( $high_oid );
$check( $risk['level'] === 'high', "open Egypt + docs_missing blocker => high (got '{$risk['level']}')" );

// 3. Open Egypt order with blocker none, no travel date -> factor A only -> medium (not high).
$low_oid = $mk( null, [] );
update_post_meta( $low_oid, 'ukv_blocker', 'none' );
delete_post_meta( $low_oid, 'ukv_travel_date' );
$risk2 = ukv_case_risk( $low_oid );
$check( $risk2['level'] !== 'high', "open Egypt + no blocker => not high (got '{$risk2['level']}')" );
$check( in_array( $risk2['level'], [ 'low', 'medium' ], true ), "level is low or medium (got '{$risk2['level']}')" );

// 4. Refresh risk flags: returns int, and the step-2 high order is flagged '1'.
$flagged = ukv_refresh_risk_flags();
$check( is_int( $flagged ), "ukv_refresh_risk_flags() returned int (got " . gettype( $flagged ) . " = {$flagged})" );
$check( get_post_meta( $high_oid, 'ukv_risk_flag', true ) === '1', 'high-risk order has ukv_risk_flag === "1"' );
$check( get_post_meta( $low_oid, 'ukv_risk_flag', true ) !== '1', 'non-high order does not have risk flag set' );

// 5. Clean up every created order.
$n = 0;
foreach ( $created as $oid ) { if ( $oid ) { wp_delete_post( $oid, true ); $n++; } }
echo "INFO — cleaned up {$n} created order(s)\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
