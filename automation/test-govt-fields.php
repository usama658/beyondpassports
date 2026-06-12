<?php
/**
 * Test: UKV Government Submission Fields (Gap #69).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-govt-fields.php"
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );
$check( function_exists( 'ukv_govt_ref' ), 'ukv_govt_ref() is defined' );
$check( function_exists( 'ukv_govt_fee_is_paid' ), 'ukv_govt_fee_is_paid() is defined' );

$created = [];

// 1. Create an order, set govt ref + mark fee paid.
$oid = ukv_create_order( [
	'order_ref'   => 'UKV-GOVT-' . substr( (string) time(), -6 ),
	'name'        => 'Govt Tester',
	'email'       => 'govt.tester@example.com',
	'destination' => 'Egypt',
	'tier'        => 'Standard',
] );
$created[] = $oid;
$check( $oid > 0, "created order (#{$oid})" );
update_post_meta( $oid, 'ukv_govt_ref', 'GWF12345' );
update_post_meta( $oid, 'ukv_govt_fee_paid', '1' );
update_post_meta( $oid, 'ukv_govt_fee_paid_at', time() );

// 2. Helpers read back correctly.
$check( ukv_govt_ref( $oid ) === 'GWF12345', "ukv_govt_ref === 'GWF12345'; got '" . ukv_govt_ref( $oid ) . "'" );
$check( ukv_govt_fee_is_paid( $oid ) === true, 'ukv_govt_fee_is_paid() true when fee paid' );

// 3. Fresh order without the meta -> false / empty.
$oid2 = ukv_create_order( [
	'order_ref'   => 'UKV-GOVT2-' . substr( (string) time(), -6 ),
	'name'        => 'No Govt Tester',
	'email'       => 'nogovt.tester@example.com',
	'destination' => 'Egypt',
	'tier'        => 'Standard',
] );
$created[] = $oid2;
$check( ukv_govt_fee_is_paid( $oid2 ) === false, 'ukv_govt_fee_is_paid() false with no meta' );
$check( ukv_govt_ref( $oid2 ) === '', "ukv_govt_ref === '' with no meta; got '" . ukv_govt_ref( $oid2 ) . "'" );

// 4. Column callback produces non-empty escaped HTML containing the ref.
ob_start();
do_action( 'manage_ukv_order_posts_custom_column', 'ukv_govt', $oid );
$html = ob_get_clean();
$check( $html !== '', 'ukv_govt column callback produced non-empty output' );
$check( strpos( $html, 'GWF12345' ) !== false, 'column output contains the govt ref GWF12345' );
$check( strpos( $html, 'Fee paid' ) !== false, 'column output shows paid badge' );

// Unpaid order shows unpaid badge.
ob_start();
do_action( 'manage_ukv_order_posts_custom_column', 'ukv_govt', $oid2 );
$html2 = ob_get_clean();
$check( strpos( $html2, 'Fee unpaid' ) !== false, 'column output shows unpaid badge for fresh order' );

// Confirm new column id is registered without clobbering existing columns.
$cols = apply_filters( 'manage_ukv_order_posts_columns', [ 'cb' => '<x>', 'title' => 'Order', 'ukv_status' => 'Status', 'date' => 'Date' ] );
$check( isset( $cols['ukv_govt'] ), "column id 'ukv_govt' registered" );
$check( isset( $cols['date'] ) && isset( $cols['title'] ), 'existing columns preserved (no clash)' );

// 5. Clean up.
foreach ( $created as $id ) { if ( $id ) { wp_delete_post( $id, true ); } }
echo 'INFO — cleaned up orders: ' . implode( ', ', array_map( fn( $i ) => "#{$i}", $created ) ) . "\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
