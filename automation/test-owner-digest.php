<?php
/**
 * Test: UKV Owner Digest — per-owner daily digest of pending actions (Gap #94).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-owner-digest.php"
 *
 * Seeds its OWN isolated orders (owner 1 + a different owner), asserts per-owner filtering
 * and the breaches / due-today / high-risk buckets, then force-deletes them.
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_owner_digest' ), 'ukv_owner_digest() is defined' );
$check( function_exists( 'ukv_owner_digest_text' ), 'ukv_owner_digest_text() is defined' );
$check( function_exists( 'ukv_owner_digest_cron' ), 'ukv_owner_digest_cron() is defined' );
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined (helper reuse)' );
$check( function_exists( 'ukv_set_owner' ), 'ukv_set_owner() is defined (helper reuse)' );

$created = [];
$mk = function ( $extra = [] ) use ( &$created ) {
	$oid = ukv_create_order( array_merge( [
		'order_ref'   => 'UKV-DIG-' . substr( md5( uniqid( '', true ) ), 0, 8 ),
		'name'        => 'Digest Test',
		'email'       => 'digest.test@example.com',
		'destination' => 'Egypt',
		'tier'        => 'Standard',
		'total'       => 49,
	], $extra ) );
	$created[] = $oid;
	return $oid;
};

$today = current_time( 'Y-m-d' );

// 1. Three orders owned by user 1.
// 1a. Normal open order.
$normal = $mk( [ 'order_ref' => 'UKV-DIG-NORMAL' ] );
update_post_meta( $normal, 'ukv_status', 'paid' );
ukv_set_owner( $normal, 1 );

// 1b. SLA-breaching order: Standard (72h SLA), backdated ~5 days, status open.
$breach = $mk( [ 'order_ref' => 'UKV-DIG-BREACH', 'tier' => 'Standard' ] );
update_post_meta( $breach, 'ukv_status', 'paid' );
$past = gmdate( 'Y-m-d H:i:s', time() - 5 * 86400 );
wp_update_post( [ 'ID' => $breach, 'post_date' => get_date_from_gmt( $past ), 'post_date_gmt' => $past ] );
update_post_meta( $breach, 'ukv_created', time() - 5 * 86400 );
ukv_set_owner( $breach, 1 );

// 1c. High-risk order due today.
$risk = $mk( [ 'order_ref' => 'UKV-DIG-RISK' ] );
update_post_meta( $risk, 'ukv_status', 'doc_review' );
update_post_meta( $risk, 'ukv_risk_flag', '1' );
update_post_meta( $risk, 'ukv_next_due', $today );
update_post_meta( $risk, 'ukv_next_action', 'Chase missing passport scan' );
ukv_set_owner( $risk, 1 );

// 1d. Order owned by a DIFFERENT user (should never appear in owner 1's digest).
$other = $mk( [ 'order_ref' => 'UKV-DIG-OTHER' ] );
update_post_meta( $other, 'ukv_status', 'paid' );
update_post_meta( $other, 'ukv_risk_flag', '1' );
update_post_meta( $other, 'ukv_next_due', $today );
ukv_set_owner( $other, 99999 );

$check( $normal > 0 && $breach > 0 && $risk > 0 && $other > 0, 'created 4 seed orders' );

// 2. Digest for owner 1.
$err = '';
set_error_handler( function ( $no, $str ) use ( &$err ) { $err .= $str . "\n"; return true; } );
$d = ukv_owner_digest( 1 );
restore_error_handler();

$check( $err === '', 'no PHP error/warning during ukv_owner_digest()' . ( $err ? " (got: " . trim( $err ) . ")" : '' ) );
$check( is_array( $d ), 'ukv_owner_digest() returns an array' );
$check( ( $d['open'] ?? 0 ) >= 3, 'open count >= 3 (got ' . ( $d['open'] ?? 0 ) . ')' );
$check( in_array( $breach, $d['sla_breaches'], true ), 'sla_breaches includes the backdated breacher' );
$check( in_array( $risk, $d['high_risk'], true ), 'high_risk includes the risk-flagged order' );
$check( in_array( $risk, $d['due_today'], true ), 'due_today includes the due-today order' );

// 2b. Other-owner order excluded from every bucket.
$other_anywhere = in_array( $other, $d['sla_breaches'], true )
	|| in_array( $other, $d['due_today'], true )
	|| in_array( $other, $d['high_risk'], true )
	|| in_array( $other, array_column( $d['orders'], 'id' ), true );
$check( ! $other_anywhere, 'the other-owner order is NOT included in owner 1 digest' );

// 3. Text body.
$text = ukv_owner_digest_text( 1 );
$check( is_string( $text ) && $text !== '', 'ukv_owner_digest_text() returns a non-empty string' );
$check( strpos( $text, (string) $d['open'] . ' open' ) !== false, 'text body mentions the open count' );

// 4. Clean up every created order.
$n = 0;
foreach ( $created as $oid ) { if ( $oid ) { wp_delete_post( $oid, true ); $n++; } }
echo "INFO — cleaned up {$n} created order(s)\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
