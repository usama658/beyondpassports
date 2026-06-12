<?php
/**
 * Test: UKV Order Groups — group / linked orders (one order per traveller,
 *       linked as a group) — Gap #82.
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-order-groups.php"
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );
$check( function_exists( 'ukv_link_orders' ), 'ukv_link_orders() is defined' );
$check( function_exists( 'ukv_group_id' ), 'ukv_group_id() is defined' );
$check( function_exists( 'ukv_group_orders' ), 'ukv_group_orders() is defined' );
$check( function_exists( 'ukv_order_group_siblings' ), 'ukv_order_group_siblings() is defined' );

$created = [];
$mk = function ( $name ) use ( &$created ) {
	$oid = ukv_create_order( [
		'order_ref'   => 'UKV-GRP-' . substr( (string) ( time() + count( $created ) ), -6 ) . '-' . count( $created ),
		'name'        => $name,
		'email'       => 'group.tester@example.com',
		'destination' => 'Egypt',
		'tier'        => 'Standard',
	] );
	$created[] = $oid;
	return $oid;
};

// 1. Create 3 orders, link them.
$a = $mk( 'Traveller A' );
$b = $mk( 'Traveller B' );
$c = $mk( 'Traveller C' );
$check( $a > 0 && $b > 0 && $c > 0, "created 3 orders (#{$a}, #{$b}, #{$c})" );

$gid = ukv_link_orders( [ $a, $b, $c ] );
$check( is_string( $gid ) && $gid !== '', "ukv_link_orders returns non-empty group id ({$gid})" );
$check( ukv_group_id( $a ) === $gid, 'order a ukv_group_id equals returned gid' );
$check( ukv_group_id( $b ) === $gid, 'order b ukv_group_id equals returned gid' );
$check( ukv_group_id( $c ) === $gid, 'order c ukv_group_id equals returned gid' );

// Determinism: relinking the same ids (any order) yields the same id.
$gid2 = ukv_link_orders( [ $c, $a, $b ] );
$check( $gid2 === $gid, "deterministic: relinking same ids (reordered) returns same gid ({$gid2})" );

// 2. group_orders + siblings.
$sorted = [ $a, $b, $c ];
sort( $sorted );
$members = ukv_group_orders( $gid );
$check( $members === $sorted, 'ukv_group_orders returns all 3 ids, sorted' );

$expect_sib = array_values( array_filter( $sorted, fn( $i ) => $i !== $a ) );
$siblings   = ukv_order_group_siblings( $a );
$check( $siblings === $expect_sib, 'ukv_order_group_siblings(a) = [b,c] sorted, excludes self' );

// 3. A 4th unlinked order: empty group id + no siblings.
$d = $mk( 'Traveller D (solo)' );
$check( ukv_group_id( $d ) === '', 'unlinked order ukv_group_id is empty string' );
$check( ukv_order_group_siblings( $d ) === [], 'unlinked order has no siblings' );

// 4. Each linked order has a journey note mentioning "group".
foreach ( [ 'a' => $a, 'b' => $b, 'c' => $c ] as $lbl => $oid ) {
	$journey = (array) get_post_meta( $oid, 'ukv_journey', true );
	$found   = false;
	foreach ( $journey as $n ) {
		if ( is_array( $n ) && stripos( (string) ( $n['text'] ?? '' ), 'group' ) !== false ) {
			$found = true;
			break;
		}
	}
	$check( $found, "order {$lbl} has a journey note mentioning 'group'" );
}

// 5. Clean up.
foreach ( $created as $id ) { if ( $id ) { wp_delete_post( $id, true ); } }
echo 'INFO — cleaned up orders: ' . implode( ', ', array_map( fn( $i ) => "#{$i}", $created ) ) . "\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
