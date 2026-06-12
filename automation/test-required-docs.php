<?php
/**
 * Test: UKV Required Docs per destination + QA gate wiring (Gap #78).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-required-docs.php"
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );
$check( function_exists( 'ukv_seed_required_docs' ), 'ukv_seed_required_docs() is defined' );
$check( function_exists( 'ukv_required_docs' ), 'ukv_required_docs() is defined' );
$check( function_exists( 'ukv_required_docs_count' ), 'ukv_required_docs_count() is defined' );
$check( function_exists( 'ukv_order_docs_complete' ), 'ukv_order_docs_complete() is defined' );
$check( function_exists( 'ukv_sync_required_count' ), 'ukv_sync_required_count() is defined' );

$created = [];

// 1. Seed runs; Egypt returns a non-empty list with count >= 2.
$n = ukv_seed_required_docs();
echo "INFO — ukv_seed_required_docs() updated {$n} destination(s)\n";
$egypt_docs = ukv_required_docs( 'egypt' );
$check( is_array( $egypt_docs ) && ! empty( $egypt_docs ), "ukv_required_docs('egypt') returns a non-empty array" );
$egypt_count = ukv_required_docs_count( 'egypt' );
$check( $egypt_count >= 2, "ukv_required_docs_count('egypt') >= 2; got {$egypt_count}" );

// 2. Create an Egypt order; sync sets ukv_required_docs meta to the dest count.
$ref = 'UKV-RD-' . substr( (string) time(), -6 );
$oid = ukv_create_order( [
	'order_ref'   => $ref,
	'name'        => 'Docs Test',
	'email'       => 'docs.test@example.com',
	'destination' => 'Egypt',
	'tier'        => 'Standard',
] );
$created[] = $oid;
$check( $oid > 0, "created Egypt order (#{$oid})" );

ukv_sync_required_count( $oid );
$meta_count = (int) get_post_meta( $oid, 'ukv_required_docs', true );
$check( $meta_count === $egypt_count, "ukv_sync_required_count() set ukv_required_docs meta to {$egypt_count}; got {$meta_count}" );

// 0 uploaded docs -> incomplete.
update_post_meta( $oid, 'ukv_documents', [] );
$check( false === ukv_order_docs_complete( $oid ), 'with 0 uploaded docs, ukv_order_docs_complete() is false' );

// Add 2 dummy attachment IDs -> complete (egypt requires 2).
update_post_meta( $oid, 'ukv_documents', [ 101, 102 ] );
$check( true === ukv_order_docs_complete( $oid ), 'with 2 uploaded docs, ukv_order_docs_complete() is true' );

// 3. QA gate: required=2, with 2 docs + sign-off -> passes; with 1 doc -> fails.
if ( function_exists( 'ukv_qa_can_submit' ) ) {
	update_post_meta( $oid, 'ukv_qa_signed_off', '1' );

	update_post_meta( $oid, 'ukv_documents', [ 101, 102 ] );
	$res_ok = ukv_qa_can_submit( $oid );
	$check( ! empty( $res_ok['ok'] ), 'QA gate PASSES with 2 docs + sign-off (required=2): ' . implode( '; ', $res_ok['reasons'] ) );

	update_post_meta( $oid, 'ukv_documents', [ 101 ] );
	$res_bad = ukv_qa_can_submit( $oid );
	$check( empty( $res_bad['ok'] ), 'QA gate FAILS with only 1 doc (required=2): ' . implode( '; ', $res_bad['reasons'] ) );
} else {
	echo "INFO — ukv_qa_can_submit() not present; skipping gate checks.\n";
}

// 4. Clean up created orders.
foreach ( $created as $id ) { if ( $id ) { wp_delete_post( $id, true ); } }
echo 'INFO — cleaned up orders: ' . implode( ', ', array_map( fn( $i ) => "#{$i}", $created ) ) . "\n";
echo "INFO — Pods required_docs seeding left in place (real config).\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
