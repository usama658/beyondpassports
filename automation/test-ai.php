<?php
/**
 * Test: UKV AI Assist (P15) — ukv-ai.php
 * MOCKS the Anthropic API via the `ukv_ai_pre_response` filter. NO real key, NO HTTP call.
 * Run: cd /c/xampp/htdocs/ukvisa && /c/xampp/php/php.exe wp-cli.phar eval-file "<abs path>" 2>&1 | head -40
 */
defined( 'ABSPATH' ) || exit;

$pass = 0;
$fail = 0;
$ok = static function ( bool $cond, string $msg ) use ( &$pass, &$fail ) {
	if ( $cond ) { $pass++; echo "PASS — {$msg}\n"; }
	else { $fail++; echo "FAIL — {$msg}\n"; }
};

// Ensure the functions are loaded (mu-plugins load automatically, but be defensive).
$ok( function_exists( 'ukv_ai' ), 'ukv_ai() is defined' );
$ok( function_exists( 'ukv_ai_polish_content' ), 'ukv_ai_polish_content() is defined' );
$ok( function_exists( 'ukv_ai_next_best_action' ), 'ukv_ai_next_best_action() is defined' );
$ok( function_exists( 'ukv_story_has_leak' ), 'ukv_story_has_leak() dependency is loaded' );

// Guard: make sure no real key is set for this test run; remember + clear, restore at the end.
$saved_key = get_option( 'ukv_anthropic_key', '' );
update_option( 'ukv_anthropic_key', '' );

/* 1) No key, no mock -> null. */
$r1 = ukv_ai( 's', 'u' );
$ok( null === $r1, 'no key + no mock returns null (got: ' . var_export( $r1, true ) . ')' );

/* 2) Mock filter short-circuits with no key. */
$mock2 = static fn() => 'POLISHED TEXT';
add_filter( 'ukv_ai_pre_response', $mock2 );
$r2 = ukv_ai( 's', 'u' );
remove_filter( 'ukv_ai_pre_response', $mock2 );
$ok( 'POLISHED TEXT' === $r2, 'mock filter makes ukv_ai return injected text without a key' );

/* Confirm the filter was removed (no key -> null again). */
$ok( null === ukv_ai( 's', 'u' ), 'filter cleanly removed after check 2 (back to null)' );

/* 3a) polish_content with clean mock -> returns that clean text. */
$clean = 'A traveller hit an unexpected delay and stayed patient until it cleared.';
$mock3a = static fn() => $clean;
add_filter( 'ukv_ai_pre_response', $mock3a );
$r3a = ukv_ai_polish_content( 'some anonymised draft', 'story' );
remove_filter( 'ukv_ai_pre_response', $mock3a );
$ok( $r3a === $clean, 'polish_content returns clean mocked text' );

/* 3b) polish_content with a LEAKY mock (raw email + known PII) -> re-gate rejects -> null. */
$leaky  = 'Contact applicant at leak@x.com — handled by Jane Doe.';
$mock3b = static fn() => $leaky;
add_filter( 'ukv_ai_pre_response', $mock3b );
// Sanity: confirm the gate itself flags this text (email is enough; 'Jane Doe' is a planted known PII).
$gate_findings = ukv_story_has_leak( $leaky, [ 'Jane Doe' ] );
$r3b = ukv_ai_polish_content( 'some anonymised draft', 'story' );
remove_filter( 'ukv_ai_pre_response', $mock3b );
$ok( ! empty( $gate_findings ), 'leak gate independently flags the leaky mock (' . count( $gate_findings ) . ' finding(s))' );
$ok( null === $r3b, 'polish_content DISCARDS leaky AI output (re-gate rejected) -> null' );

/* 4) next_best_action with a mock -> returns the mocked string; prompt path does not fatal. */
$order_id = wp_insert_post( [
	'post_type'   => 'ukv_order',
	'post_title'  => 'TEST AI ORDER (delete me)',
	'post_status' => 'draft',
] );
$ok( $order_id > 0, 'created a test ukv_order post (#' . (int) $order_id . ')' );
update_post_meta( $order_id, 'ukv_destination', 'india' );
update_post_meta( $order_id, 'ukv_tier', 'express' );
update_post_meta( $order_id, 'ukv_status', 'in_progress' );
update_post_meta( $order_id, 'ukv_journey', [
	[ 'date' => '2026-06-10 09:00', 'agent' => 'agent', 'channel' => 'call',
	  'text' => 'Client called about timing; reassured. Reach them on 07700 900123.' ],
] );

$mock4 = static fn() => 'Chase the visa centre for a status update and proactively message the client today.';
add_filter( 'ukv_ai_pre_response', $mock4 );
$r4 = ukv_ai_next_best_action( $order_id );
remove_filter( 'ukv_ai_pre_response', $mock4 );
$ok( is_string( $r4 ) && '' !== $r4, 'next_best_action returns a non-null mocked recommendation (no fatal)' );
$ok(
	is_string( $r4 ) && false !== strpos( $r4, 'Chase the visa centre' ),
	'next_best_action returns exactly the mocked string'
);

/* Bonus: confirm next_best_action is null-safe for a non-order id. */
$ok( null === ukv_ai_next_best_action( 0 ), 'next_best_action(0) is null-safe' );

/* Cleanup test post. */
wp_delete_post( $order_id, true );
$ok( null === get_post( $order_id ), 'test ukv_order post cleaned up' );

/* Restore any pre-existing key option. */
update_option( 'ukv_anthropic_key', $saved_key );

echo "\n{$pass} passed, {$fail} failed\n";
echo ( 0 === $fail ) ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
