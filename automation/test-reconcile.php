<?php
/**
 * Test: UKV Stripe <-> Orders reconciliation (Gap #85).
 * Catches successful Stripe charges that have NO matching ukv_order (a missed
 * webhook -> revenue at risk). No real Stripe calls — charges are injected.
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-reconcile.php"
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );
$check( function_exists( 'ukv_reconcile' ), 'ukv_reconcile() is defined' );
$check( function_exists( 'ukv_reconcile_fetch_charges' ), 'ukv_reconcile_fetch_charges() is defined' );

$created = [];

// 1. Create a paid order: paid@x.com, total 49.
$oid = ukv_create_order( [
	'order_ref'   => 'UKV-RECON-' . substr( (string) time(), -6 ),
	'name'        => 'Paid Customer',
	'email'       => 'paid@x.com',
	'destination' => 'Egypt',
	'tier'        => 'Standard',
	'total'       => 49,
] );
$created[] = $oid;
$check( $oid > 0, "created paid order paid@x.com total=49 (#{$oid})" );

// 2. Reconcile: ch_1 matches the order, ch_2 (ghost) is an unmatched paid charge.
$res = ukv_reconcile( [
	[ 'id' => 'ch_1', 'email' => 'paid@x.com',  'amount' => 49.0, 'created' => time() ],
	[ 'id' => 'ch_2', 'email' => 'ghost@x.com', 'amount' => 99.0, 'created' => time() ],
] );
$check( is_array( $res ) && isset( $res['matched'], $res['unmatched'] ), 'reconcile returns [matched, unmatched]' );
$check( in_array( 'ch_1', $res['matched'], true ), "matched contains 'ch_1' (order exists)" );
$check( ! in_array( 'ch_2', $res['matched'], true ), "matched does NOT contain ghost 'ch_2'" );

$ghost = null;
foreach ( $res['unmatched'] as $u ) {
	if ( ( $u['id'] ?? '' ) === 'ch_2' ) { $ghost = $u; break; }
}
$check( $ghost !== null, "unmatched contains the ghost charge 'ch_2' (missed charge -> revenue at risk)" );
$check( $ghost && ( $ghost['email'] ?? '' ) === 'ghost@x.com', 'unmatched ghost carries its email' );
$check( $ghost && (float) ( $ghost['amount'] ?? 0 ) === 99.0, 'unmatched ghost carries its amount' );
// And the matched charge must NOT appear in unmatched.
$un_ids = array_map( fn( $u ) => $u['id'] ?? '', $res['unmatched'] );
$check( ! in_array( 'ch_1', $un_ids, true ), "matched 'ch_1' is NOT flagged unmatched" );

// Penny-tolerance: a charge 1p off still matches (within £0.01).
$res2 = ukv_reconcile( [ [ 'id' => 'ch_p', 'email' => 'paid@x.com', 'amount' => 49.01, 'created' => time() ] ] );
$check( in_array( 'ch_p', $res2['matched'], true ), 'charge within £0.01 (49.01 vs 49) still matches' );

// 3. No real Stripe: filter injection feeds fetch_charges().
$inject = [ [ 'id' => 'ch_3', 'email' => 'paid@x.com', 'amount' => 49.0, 'created' => time() ] ];
$cb     = fn() => $inject;
add_filter( 'ukv_reconcile_charges', $cb );
$fetched = ukv_reconcile_fetch_charges();
$check( $fetched === $inject, 'fetch_charges() returns the filter-injected list (no real Stripe call)' );
remove_filter( 'ukv_reconcile_charges', $cb );
$check( ukv_reconcile_fetch_charges() === [], 'fetch_charges() returns [] once the filter is removed (live key wired at launch)' );

// 4. Clean up.
foreach ( $created as $id ) { if ( $id ) { wp_delete_post( $id, true ); } }
echo 'INFO — cleaned up orders: ' . implode( ', ', array_map( fn( $i ) => "#{$i}", $created ) ) . "\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
