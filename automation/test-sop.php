<?php
/**
 * Test: UKV Stage SOP & troubleshooting surfacing — ukv-sop.php
 * Proves: per-stage SOP map is populated + queryable; per-order troubleshooting
 * resolves from ukv_blocker (+ open barriers); the order meta box renders escaped HTML.
 * Run: cd /c/xampp/htdocs/ukvisa && /c/xampp/php/php.exe -d memory_limit=512M wp-cli.phar eval-file "<abs path>" 2>&1 | tail -40
 */
defined( 'ABSPATH' ) || exit;

$pass = 0;
$fail = 0;
$ok = static function ( bool $cond, string $msg ) use ( &$pass, &$fail ) {
	if ( $cond ) { $pass++; echo "PASS — {$msg}\n"; }
	else { $fail++; echo "FAIL — {$msg}\n"; }
};

$ok( function_exists( 'ukv_stage_sop' ), 'ukv_stage_sop() is defined' );
$ok( function_exists( 'ukv_order_troubleshooting' ), 'ukv_order_troubleshooting() is defined' );
$ok( function_exists( 'ukv_create_order' ), 'ukv_create_order() factory dependency is loaded' );

/* 1) SOP map shape + content. */
$paid = ukv_stage_sop( 'paid' );
$ok( is_array( $paid ) && ! empty( $paid ), 'ukv_stage_sop("paid") is non-empty array' );
$ok( isset( $paid['do'] ) && isset( $paid['next'] ), 'paid SOP has "do" and "next" keys' );

$dr = ukv_stage_sop( 'doc_review' );
$dr_text = strtolower( wp_json_encode( $dr ) );
$ok( false !== strpos( $dr_text, 'complete' ) || false !== strpos( $dr_text, 'sign' ),
	'doc_review SOP mentions completeness/sign-off' );

$ok( [] === ukv_stage_sop( 'no_such_status' ), 'unknown status returns empty array (empty-safe)' );

/* 2) Troubleshooting resolves from ukv_blocker. */
$created = [];
$oid = ukv_create_order( [
	'name'        => 'SOP Test',
	'email'       => 'sop@example.com',
	'destination' => 'india',
	'tier'        => 'standard',
	'total'       => '99',
	'documents'   => [],
] );
$created[] = $oid;
$ok( $oid > 0, 'created a test ukv_order (#' . (int) $oid . ')' );

update_post_meta( $oid, 'ukv_status', 'awaiting_docs' );
update_post_meta( $oid, 'ukv_blocker', 'docs_missing' );

$ts = ukv_order_troubleshooting( $oid );
$ok( is_array( $ts ) && count( $ts ) >= 1, 'troubleshooting returns at least one entry for docs_missing' );

$ts_text = strtolower( wp_json_encode( $ts ) );
$ok( false !== strpos( $ts_text, 'chas' ) || false !== strpos( $ts_text, 'doc' ),
	'troubleshooting text mentions chasing/docs' );

/* 3) Meta box callback renders non-empty escaped HTML with no PHP error. */
$ok( function_exists( 'ukv_sop_metabox' ), 'ukv_sop_metabox() is defined' );
ob_start();
ukv_sop_metabox( get_post( $oid ) );
$html = ob_get_clean();
$ok( '' !== trim( (string) $html ), 'meta box renders non-empty HTML' );
$ok( false !== strpos( $html, '<' ), 'meta box output contains markup' );
// Stage title for awaiting_docs should appear in the rendered box.
$ad = ukv_stage_sop( 'awaiting_docs' );
$ok( ! empty( $ad['title'] ) && false !== strpos( $html, esc_html( $ad['title'] ) ),
	'meta box shows the current stage SOP title' );

/* 4) Clean up. */
foreach ( $created as $id ) { if ( $id ) { wp_delete_post( $id, true ); } }
$ok( null === get_post( $oid ), 'test order(s) cleaned up' );

echo "\n{$pass} passed, {$fail} failed\n";
echo ( 0 === $fail ) ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
