<?php
/**
 * Test: UKV Supply Chain Registry (#92).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-supply-chain.php"
 *
 * Snapshots and restores the ukv_supply_nodes option so the registry is left untouched.
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Helper: does a node-list contain a node with this name?
$has_name = function ( array $nodes, string $name ): bool {
	foreach ( $nodes as $n ) {
		if ( isset( $n['name'] ) && $n['name'] === $name ) { return true; }
	}
	return false;
};

// Sanity: required functions present.
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );
$check( function_exists( 'ukv_supply_seed' ), 'ukv_supply_seed() is defined' );
$check( function_exists( 'ukv_supply_add' ), 'ukv_supply_add() is defined' );
$check( function_exists( 'ukv_supply_all' ), 'ukv_supply_all() is defined' );
$check( function_exists( 'ukv_supply_by_type' ), 'ukv_supply_by_type() is defined' );
$check( function_exists( 'ukv_supply_for_destination' ), 'ukv_supply_for_destination() is defined' );
$check( function_exists( 'ukv_supply_for_order' ), 'ukv_supply_for_order() is defined' );

// Snapshot the option + seed flag so we can restore exactly.
$snapshot_nodes = get_option( UKV_SUPPLY_OPTION, [] );
$snapshot_flag  = get_option( UKV_SUPPLY_SEED_FLAG );

$created = [];

// 1. Seed runs; all() non-empty; by_type('courier') has at least one.
//    Force a clean seed for the test by clearing the flag first.
delete_option( UKV_SUPPLY_SEED_FLAG );
$n = ukv_supply_seed();
echo "INFO — ukv_supply_seed() seeded {$n} node(s)\n";
$all = ukv_supply_all();
$check( is_array( $all ) && ! empty( $all ), 'ukv_supply_all() is non-empty after seed' );
$couriers = ukv_supply_by_type( 'courier' );
$check( ! empty( $couriers ), "ukv_supply_by_type('courier') has at least one node (got " . count( $couriers ) . ')' );

// Seed is one-time: a second call returns 0.
$n2 = ukv_supply_seed();
$check( 0 === $n2, "ukv_supply_seed() is one-time (second call returned {$n2})" );

// 2. Add a destination-specific centre; verify per-destination + global resolution.
$id = ukv_supply_add( [
	'type'         => 'centre',
	'name'         => 'VFS Cairo',
	'destinations' => [ 'egypt' ],
	'contact'      => 'x',
	'sla'          => '5 days',
] );
$check( is_string( $id ) && '' !== $id, "ukv_supply_add() returned an id ('{$id}')" );

// Deterministic id: same type+name yields the same id.
$id_again = ukv_supply_add( [ 'type' => 'centre', 'name' => 'VFS Cairo', 'destinations' => [ 'egypt' ] ] );
$check( $id_again === $id, "ids are deterministic (re-add returned same id '{$id_again}')" );

$egypt = ukv_supply_for_destination( 'egypt' );
$check( $has_name( $egypt, 'VFS Cairo' ), "ukv_supply_for_destination('egypt') includes 'VFS Cairo'" );
$check( $has_name( $egypt, 'Royal Mail Special Delivery' ), "ukv_supply_for_destination('egypt') includes global courier (Royal Mail Special Delivery)" );

$turkey = ukv_supply_for_destination( 'turkey' );
$check( $has_name( $turkey, 'Royal Mail Special Delivery' ), "ukv_supply_for_destination('turkey') includes global node" );
$check( ! $has_name( $turkey, 'VFS Cairo' ), "ukv_supply_for_destination('turkey') does NOT include 'VFS Cairo'" );

// 3. Create an Egypt order -> for_order includes VFS Cairo + globals.
$ref = 'UKV-SC-' . substr( (string) time(), -6 );
$oid = ukv_create_order( [
	'order_ref'   => $ref,
	'name'        => 'Supply Test',
	'email'       => 'supply.test@example.com',
	'destination' => 'Egypt',
	'tier'        => 'Standard',
] );
$created[] = $oid;
$check( $oid > 0, "created Egypt order (#{$oid})" );

$for_order = ukv_supply_for_order( (int) $oid );
$check( $has_name( $for_order, 'VFS Cairo' ), "ukv_supply_for_order(#{$oid}) includes 'VFS Cairo'" );
$check( $has_name( $for_order, 'Royal Mail Special Delivery' ), "ukv_supply_for_order(#{$oid}) includes global node" );

// 4. Clean up: delete created order, restore the option + flag to the pre-test snapshot.
foreach ( $created as $id_del ) { if ( $id_del ) { wp_delete_post( $id_del, true ); } }
echo 'INFO — cleaned up orders: ' . implode( ', ', array_map( fn( $i ) => "#{$i}", $created ) ) . "\n";

update_option( UKV_SUPPLY_OPTION, $snapshot_nodes, false );
if ( false === $snapshot_flag ) { delete_option( UKV_SUPPLY_SEED_FLAG ); }
else { update_option( UKV_SUPPLY_SEED_FLAG, $snapshot_flag, false ); }
echo "INFO — restored ukv_supply_nodes option + seed flag to pre-test snapshot.\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
