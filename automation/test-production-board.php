<?php
/**
 * Test: UKV Production Line (kanban ops board).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-production-board.php"
 *
 * Seeds its OWN isolated orders, asserts columns/card/render, then force-deletes them.
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_prodboard_columns' ), 'ukv_prodboard_columns() is defined' );
$check( function_exists( 'ukv_prodboard_card' ), 'ukv_prodboard_card() is defined' );
$check( function_exists( 'ukv_prodboard_render' ), 'ukv_prodboard_render() is defined' );
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined (helper reuse)' );

$created = [];
$mk = function ( $status, $extra = [] ) use ( &$created ) {
	$oid = ukv_create_order( array_merge( [
		'order_ref'   => 'UKV-PBRD-' . substr( md5( uniqid( '', true ) ), 0, 8 ),
		'name'        => 'Board Test',
		'email'       => 'board.test@example.com',
		'destination' => 'Egypt',
		'tier'        => 'Standard',
		'total'       => 49,
	], $extra ) );
	$created[] = $oid;
	if ( null !== $status ) { update_post_meta( $oid, 'ukv_status', $status ); }
	return $oid;
};

// 1. Seed orders across a few stages.
$paid_oid      = $mk( 'paid',      [ 'order_ref' => 'UKV-PBRD-PAID',  'name' => 'Paid Person' ] );
$review_oid    = $mk( 'doc_review',[ 'order_ref' => 'UKV-PBRD-REV',   'name' => 'Review Person', 'destination' => 'Turkey', 'tier' => 'Express' ] );
$submitted_oid = $mk( 'submitted', [ 'order_ref' => 'UKV-PBRD-SUBM',  'name' => 'Submitted Person' ] );
$delivered_oid = $mk( 'delivered', [ 'order_ref' => 'UKV-PBRD-DELV',  'name' => 'Delivered Person' ] );
$rejected_oid  = $mk( 'rejected',  [ 'order_ref' => 'UKV-PBRD-REJ',   'name' => 'Rejected Person' ] );

// Flag the doc_review order as risky for the card assertion.
update_post_meta( $review_oid, 'ukv_risk_flag', '1' );
update_post_meta( $review_oid, 'ukv_next_action', 'Chase passport scan' );

// 2. Columns: right IDs in the right stage.
$cols = ukv_prodboard_columns();
$check( is_array( $cols ) && ! empty( $cols ), 'ukv_prodboard_columns() returns non-empty array' );

// Stage order: open stages must precede delivered/won (the design ordering).
$keys     = array_keys( $cols );
$pos_paid = array_search( 'paid', $keys, true );
$pos_delv = array_search( 'delivered', $keys, true );
$check( $pos_paid !== false && $pos_delv !== false && $pos_paid < $pos_delv, "open stage 'paid' precedes 'delivered' in column order" );

$in = function ( $stage, $oid ) use ( $cols ) {
	return isset( $cols[ $stage ] ) && in_array( $oid, $cols[ $stage ]['orders'], true );
};
$check( $in( 'paid', $paid_oid ),           "paid order is under 'paid'" );
$check( $in( 'doc_review', $review_oid ),    "doc_review order is under 'doc_review'" );
$check( $in( 'submitted', $submitted_oid ),  "submitted order is under 'submitted'" );
$check( $in( 'delivered', $delivered_oid ),  "delivered order is under 'delivered'" );
// rejected collapses into the trailing _closed column (per design).
$check( $in( '_closed', $rejected_oid ),     "rejected order is under collapsed '_closed' column" );
// Cross-stage isolation: paid order must NOT appear in doc_review.
$check( ! $in( 'doc_review', $paid_oid ),    "paid order does NOT leak into 'doc_review'" );

// 3. Card shape + values.
$card = ukv_prodboard_card( $review_oid );
$check( is_array( $card ), 'ukv_prodboard_card() returns array' );
foreach ( [ 'ref', 'name', 'destination', 'tier', 'owner', 'sla', 'risk', 'docs_badge', 'barriers', 'next_action' ] as $k ) {
	$check( array_key_exists( $k, $card ), "card has key '{$k}'" );
}
$check( $card['ref'] === 'UKV-PBRD-REV', "card ref correct (got '{$card['ref']}')" );
$check( $card['name'] === 'Review Person', "card name correct (got '{$card['name']}')" );
$check( $card['destination'] === 'Turkey', "card destination correct (got '{$card['destination']}')" );
$check( $card['tier'] === 'Express', "card tier correct (got '{$card['tier']}')" );
$check( $card['risk'] === true, 'card risk flag is true for flagged order' );
$check( in_array( $card['sla'], [ 'breach', 'ok' ], true ), "card sla is 'breach'|'ok' (got '{$card['sla']}')" );
$check( is_int( $card['barriers'] ), 'card barriers is int' );
$check( is_string( $card['docs_badge'] ), 'card docs_badge is string' );
$check( $card['next_action'] === 'Chase passport scan', "card next_action correct (got '{$card['next_action']}')" );

// 4. Render: non-empty, contains stage label + seeded ref, no PHP error/warning.
// Render gates on current_user_can('edit_posts'); set an admin so the authorized path runs.
$admin = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ids' ] );
if ( $admin ) { wp_set_current_user( (int) $admin[0] ); }
$check( current_user_can( 'edit_posts' ), 'test user can edit_posts (render is permission-gated)' );

$err = '';
set_error_handler( function ( $no, $str ) use ( &$err ) { $err .= $str . "\n"; return true; } );
ob_start();
ukv_prodboard_render();
$html = ob_get_clean();
restore_error_handler();

$check( $err === '', 'no PHP error/warning during render' . ( $err ? " (got: " . trim( $err ) . ")" : '' ) );
$check( is_string( $html ) && $html !== '', 'render produced non-empty output' );
$check( strpos( $html, 'Production Line' ) !== false, 'render contains the board heading' );
$check( strpos( $html, 'Doc review' ) !== false, 'render contains a stage label (Doc review)' );
$check( strpos( $html, esc_html( 'UKV-PBRD-REV' ) ) !== false, 'render shows the seeded doc_review ref' );
$check( strpos( $html, esc_html( 'UKV-PBRD-PAID' ) ) !== false, 'render shows the seeded paid ref' );
$check( strpos( $html, '<' ) !== false, 'render emitted HTML markup' );

// 5. Clean up every created order.
$n = 0;
foreach ( $created as $oid ) { if ( $oid ) { wp_delete_post( $oid, true ); $n++; } }
echo "INFO — cleaned up {$n} created order(s)\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
