<?php
/**
 * Test: UKV Bespoke Quote (manual-review pricing — Unit 7).
 * Policy: manual-review orders are NOT priced on the fixed tiers — an agent sets a
 *         bespoke price reflecting the real work + sends a Stripe Payment Link.
 *         Standard-lane orders keep the fixed tiers untouched.
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-quote.php"
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );
$check( function_exists( 'ukv_set_quote' ), 'ukv_set_quote() is defined' );
$check( function_exists( 'ukv_quote_amount' ), 'ukv_quote_amount() is defined' );
$check( function_exists( 'ukv_quote_send' ), 'ukv_quote_send() is defined' );

$created = [];

// 1. Set a quote amount, read it back.
$oid = ukv_create_order( [
	'order_ref'   => 'UKV-QUOTE-' . substr( (string) time(), -6 ),
	'name'        => 'Quote Tester',
	'email'       => 'quote.tester@example.com',
	'destination' => 'Egypt',
	'tier'        => 'Manual review',
	'total'       => 0,
] );
$created[] = $oid;
update_post_meta( $oid, 'ukv_eligibility', 'manual_review' );
$check( $oid > 0, "created manual-review order (#{$oid})" );

ukv_set_quote( $oid, 149.0 );
$check( ukv_quote_amount( $oid ) === 149.0, 'ukv_quote_amount === 149.0 after ukv_set_quote; got ' . var_export( ukv_quote_amount( $oid ), true ) );
$check( get_post_meta( $oid, 'ukv_quote_status', true ) === 'none', "setting an amount alone leaves status 'none' (not yet sent)" );

// 2. Send the payment link.
$j0     = get_post_meta( $oid, 'ukv_journey', true );
$before = is_array( $j0 ) ? count( $j0 ) : 0;

$sent = ukv_quote_send( $oid );
$check( $sent === true, 'ukv_quote_send() returns true when an amount is set' );
$check( get_post_meta( $oid, 'ukv_quote_status', true ) === 'sent', "ukv_quote_status === 'sent' after send" );
$check( (string) get_post_meta( $oid, 'ukv_quote_link', true ) !== '', 'ukv_quote_link is non-empty after send' );
$check( (int) get_post_meta( $oid, 'ukv_quote_sent_at', true ) > 0, 'ukv_quote_sent_at timestamp stamped' );

$journey = (array) get_post_meta( $oid, 'ukv_journey', true );
$check( count( $journey ) >= $before + 1, 'journey gained a "quote sent" audit note' );
$note = null;
foreach ( $journey as $n ) {
	if ( is_array( $n ) && stripos( (string) ( $n['text'] ?? '' ), 'Quote sent' ) !== false ) { $note = $n; break; }
}
$check( $note !== null, 'a journey note mentions "Quote sent"' );
$check( $note && strpos( (string) $note['text'], '149' ) !== false, 'quote-sent note contains the amount 149' );

// 3. Send with no amount -> false, nothing happens.
$oid2 = ukv_create_order( [
	'order_ref'   => 'UKV-QUOTE0-' . substr( (string) time(), -6 ),
	'name'        => 'No Amount',
	'email'       => 'noamount@example.com',
	'destination' => 'India',
	'tier'        => 'Manual review',
	'total'       => 0,
] );
$created[] = $oid2;
update_post_meta( $oid2, 'ukv_eligibility', 'manual_review' );

$sent2 = ukv_quote_send( $oid2 );
$check( $sent2 === false, 'ukv_quote_send() returns false when no amount is set' );
$check( get_post_meta( $oid2, 'ukv_quote_status', true ) === 'none', "no-amount order stays status 'none'" );
$check( (string) get_post_meta( $oid2, 'ukv_quote_link', true ) === '', 'no-amount order has no payment link' );

// 4. Clean up.
foreach ( $created as $id ) { if ( $id ) { wp_delete_post( $id, true ); } }
echo 'INFO — cleaned up orders: ' . implode( ', ', array_map( fn( $i ) => "#{$i}", $created ) ) . "\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
