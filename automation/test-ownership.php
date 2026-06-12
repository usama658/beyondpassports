<?php
/**
 * Test: UKV Order Ownership + SLA-breach escalation (Gap #70).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-ownership.php"
 *
 * Seeds its OWN isolated orders, asserts owner field + SLA escalation (fire-once / non-fatal),
 * then force-deletes them.
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_set_owner' ), 'ukv_set_owner() is defined' );
$check( function_exists( 'ukv_get_owner' ), 'ukv_get_owner() is defined' );
$check( function_exists( 'ukv_auto_assign' ), 'ukv_auto_assign() is defined' );
$check( function_exists( 'ukv_sla_escalate' ), 'ukv_sla_escalate() is defined' );
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined (helper reuse)' );

$created = [];
$mk = function ( $extra = [] ) use ( &$created ) {
	$oid = ukv_create_order( array_merge( [
		'order_ref'   => 'UKV-OWN-' . substr( md5( uniqid( '', true ) ), 0, 8 ),
		'name'        => 'Ownership Test',
		'email'       => 'ownership.test@example.com',
		'destination' => 'Egypt',
		'tier'        => 'Standard',
		'total'       => 49,
	], $extra ) );
	$created[] = $oid;
	return $oid;
};

// 1. Owner round-trip.
$oid1 = $mk();
$check( $oid1 > 0, 'created an order for owner test' );
$check( ukv_get_owner( $oid1 ) === 0, 'default owner is 0 (unassigned)' );
ukv_set_owner( $oid1, 1 );
$check( ukv_get_owner( $oid1 ) === 1, 'ukv_get_owner returns the user set by ukv_set_owner (===1)' );

// 1b. Round-robin auto-assign returns an eligible (edit_posts) user id and persists it.
$assigned = ukv_auto_assign( $mk() );
$check( is_int( $assigned ), 'ukv_auto_assign returns an int (got ' . gettype( $assigned ) . ')' );
// In a fresh WP at least the admin (user 1) has edit_posts, so we expect a real assignee here.
$check( $assigned >= 1, "ukv_auto_assign picked an eligible user (got {$assigned})" );

// 2. SLA-breaching order: Standard (72h SLA), backdated ~5 days, status 'paid' (open).
$breach = $mk( [ 'tier' => 'Standard', 'order_ref' => 'UKV-OWN-BREACH' ] );
update_post_meta( $breach, 'ukv_status', 'paid' );
$past = gmdate( 'Y-m-d H:i:s', time() - 5 * 86400 );
wp_update_post( [ 'ID' => $breach, 'post_date' => get_date_from_gmt( $past ), 'post_date_gmt' => $past ] );
update_post_meta( $breach, 'ukv_created', time() - 5 * 86400 );
// Give it an owner with an email so the best-effort mail path is exercised (non-fatal).
ukv_set_owner( $breach, 1 );

// Capture mail so the test never actually sends.
$mailed = [];
add_filter( 'pre_wp_mail', function ( $null, $atts ) use ( &$mailed ) { $mailed[] = $atts; return true; }, 10, 2 );

$err = '';
set_error_handler( function ( $no, $str ) use ( &$err ) { $err .= $str . "\n"; return true; } );
$escalated = ukv_sla_escalate();
restore_error_handler();

$check( $err === '', 'no PHP error/warning during ukv_sla_escalate()' . ( $err ? " (got: " . trim( $err ) . ")" : '' ) );
$check( is_array( $escalated ), 'ukv_sla_escalate() returns an array' );
$check( in_array( $breach, $escalated, true ), 'breached order id is in the escalated list' );
$check( get_post_meta( $breach, 'ukv_sla_escalated', true ) === '1', "ukv_sla_escalated meta set to '1'" );

$journey = (array) get_post_meta( $breach, 'ukv_journey', true );
$last    = $journey ? end( $journey ) : [];
$check( $journey && stripos( $last['text'] ?? '', 'SLA' ) !== false, 'journey note mentioning "SLA" appended' );
$check( ( $last['agent'] ?? '' ) === 'system' && ( $last['channel'] ?? '' ) === 'internal', "journey note is agent 'system' / channel 'internal'" );
$check( ! empty( $mailed ), 'best-effort owner mail attempted (non-fatal)' );

// 3. Idempotent: second run must NOT re-escalate the same order.
$escalated2 = ukv_sla_escalate();
$check( is_array( $escalated2 ), 'second ukv_sla_escalate() returns an array' );
$check( ! in_array( $breach, $escalated2, true ), 'breached order is NOT re-escalated on second run (fire-once)' );
$journey2 = (array) get_post_meta( $breach, 'ukv_journey', true );
$check( count( $journey2 ) === count( $journey ), 'no duplicate journey note added on second run' );

// 4. Clean up every created order.
$n = 0;
foreach ( $created as $oid ) { if ( $oid ) { wp_delete_post( $oid, true ); $n++; } }
echo "INFO — cleaned up {$n} created order(s)\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
