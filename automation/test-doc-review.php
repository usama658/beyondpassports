<?php
/**
 * Test: UKV AI Document Review (Orders Hub P5) — ukv-doc-review.php
 * MOCKS the Anthropic API via the `ukv_ai_pre_response` filter. NO real key, NO HTTP call.
 * Run: cd /c/xampp/htdocs/ukvisa && /c/xampp/php/php.exe -d memory_limit=512M wp-cli.phar eval-file "<abs path>" 2>&1 | tail -30
 *
 * Proves the engine: null without key, NEVER mutates ukv_status (advisory), invalid-JSON safe.
 */
defined( 'ABSPATH' ) || exit;

$pass = 0;
$fail = 0;
$ok = static function ( bool $cond, string $msg ) use ( &$pass, &$fail ) {
	if ( $cond ) { $pass++; echo "PASS — {$msg}\n"; }
	else { $fail++; echo "FAIL — {$msg}\n"; }
};

$ok( function_exists( 'ukv_doc_review_verdict' ), 'ukv_doc_review_verdict() is defined' );
$ok( function_exists( 'ukv_doc_review_badge' ), 'ukv_doc_review_badge() is defined' );
$ok( function_exists( 'ukv_ai' ), 'ukv_ai() gateway dependency is loaded' );
$ok( function_exists( 'ukv_create_order' ), 'ukv_create_order() factory dependency is loaded' );

// Guard: ensure no real key for this run; remember + clear, restore at the end.
$saved_key = get_option( 'ukv_anthropic_key', '' );
update_option( 'ukv_anthropic_key', '' );

// Create one order to review. Factory sets ukv_status = 'paid'.
$oid = ukv_create_order( [
	'name'            => 'Test Traveller',
	'email'           => 'test@example.com',
	'destination'     => 'india',
	'tier'            => 'express',
	'total'           => '149',
	'passport_number' => 'SECRET123456',
	'documents'       => [ 'passport-bio-page.jpg' ],
] );
$ok( $oid > 0, 'created a test ukv_order (#' . (int) $oid . ')' );
update_post_meta( $oid, 'ukv_travel_date', '2026-08-01' );
$ok( 'paid' === get_post_meta( $oid, 'ukv_status', true ), 'order starts with status = paid' );

/* 1) No key, no mock -> null (no-op). */
$r1 = ukv_doc_review_verdict( $oid );
$ok( null === $r1, 'no key + no mock returns null (got: ' . var_export( $r1, true ) . ')' );

/* 2) Mock a clean PASS verdict. */
$mock2 = static fn() => '{"pass":true,"flags":[]}';
add_filter( 'ukv_ai_pre_response', $mock2 );
$r2 = ukv_doc_review_verdict( $oid );
remove_filter( 'ukv_ai_pre_response', $mock2 );
$ok( is_array( $r2 ) && true === ( $r2['pass'] ?? null ), 'PASS mock -> verdict array with pass===true' );
$ok( 'paid' === get_post_meta( $oid, 'ukv_status', true ), 'ukv_status UNCHANGED after PASS verdict (still paid)' );
$ok( is_array( get_post_meta( $oid, 'ukv_doc_review', true ) ), 'verdict stored on ukv_doc_review meta' );
$ok( false !== strpos( ukv_doc_review_badge( $oid ), 'Docs OK' ), 'badge shows "Docs OK" for a pass' );

/* 3) Mock a FAIL verdict with one flag -> proves never auto-rejects. */
$mock3 = static fn() => '{"pass":false,"flags":[{"check":"expiry","severity":"fail","note":"expires too soon"}]}';
add_filter( 'ukv_ai_pre_response', $mock3 );
$r3 = ukv_doc_review_verdict( $oid );
remove_filter( 'ukv_ai_pre_response', $mock3 );
$ok( is_array( $r3 ) && false === ( $r3['pass'] ?? null ), 'FAIL mock -> verdict pass===false' );
$ok( is_array( $r3 ) && 1 === count( $r3['flags'] ?? [] ), 'FAIL mock -> exactly 1 flag' );
$ok( 'paid' === get_post_meta( $oid, 'ukv_status', true ), 'ukv_status STILL unchanged after FAIL (never auto-rejects)' );
$ok( false !== strpos( ukv_doc_review_badge( $oid ), 'Review' ), 'badge shows "Review" when flags exist' );

/* 4) Mock invalid JSON -> verdict pass===null, no fatal. */
$mock4 = static fn() => 'not json';
add_filter( 'ukv_ai_pre_response', $mock4 );
$r4 = ukv_doc_review_verdict( $oid );
remove_filter( 'ukv_ai_pre_response', $mock4 );
$ok( is_array( $r4 ) && array_key_exists( 'pass', $r4 ) && null === $r4['pass'], 'invalid JSON -> verdict returned with pass===null (no fatal)' );
$ok( is_array( $r4 ) && isset( $r4['raw'] ), 'invalid JSON -> raw text preserved' );
$ok( 'paid' === get_post_meta( $oid, 'ukv_status', true ), 'ukv_status unchanged after invalid-JSON verdict' );

/* Bonus: null-safe for a non-order id. */
add_filter( 'ukv_ai_pre_response', $mock2 );
$ok( null === ukv_doc_review_verdict( 0 ), 'verdict(0) is null-safe (not an order)' );
remove_filter( 'ukv_ai_pre_response', $mock2 );

/* Badge for an order with no verdict stored -> "Not reviewed". */
$oid2 = ukv_create_order( [ 'name' => 'No Review', 'destination' => 'egypt' ] );
$ok( false !== strpos( ukv_doc_review_badge( $oid2 ), 'Not reviewed' ), 'badge shows "Not reviewed" with no stored verdict' );

/* 5) Clean up. */
wp_delete_post( $oid, true );
wp_delete_post( $oid2, true );
$ok( null === get_post( $oid ), 'test order cleaned up' );
update_option( 'ukv_anthropic_key', $saved_key );

echo "\n{$pass} passed, {$fail} failed\n";
echo ( 0 === $fail ) ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
