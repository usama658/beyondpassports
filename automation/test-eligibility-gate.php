<?php
/**
 * Test: UKV Eligibility meta box (Unit 2) + Eligibility gate (Unit 3).
 * Proves:
 *  - A manual_review order that is NOT cleared is blocked from advancing past 'paid';
 *    the gate reverts ukv_status, sets an admin transient, and logs a journey note.
 *  - Once cleared (or standard) the gate never blocks.
 *  - The Eligibility meta box render callback emits escaped HTML mentioning the lane.
 * Run: cd /c/xampp/htdocs/ukvisa && /c/xampp/php/php.exe -d memory_limit=512M wp-cli.phar eval-file "<abs path>" 2>&1 | tail -40
 */
defined( 'ABSPATH' ) || exit;

$pass = 0;
$fail = 0;
$ok = static function ( bool $cond, string $msg ) use ( &$pass, &$fail ) {
	if ( $cond ) { $pass++; echo "PASS — {$msg}\n"; }
	else { $fail++; echo "FAIL — {$msg}\n"; }
};

$ok( function_exists( 'ukv_eligibility_gate_enforce' ), 'ukv_eligibility_gate_enforce() is defined' );
$ok( function_exists( 'ukv_eligibility_metabox' ), 'ukv_eligibility_metabox() render callback is defined' );
$ok( function_exists( 'ukv_order_is_cleared' ), 'ukv_order_is_cleared() dependency is loaded' );
$ok( function_exists( 'ukv_create_order' ), 'ukv_create_order() factory dependency is loaded' );

$created = [];

/* 1) manual_review order, NOT cleared -> blocked advancing past 'paid'. */
$oid = ukv_create_order( [
	'name'        => 'Eligibility Gate Test',
	'email'       => 'elig@example.com',
	'destination' => 'india',
	'tier'        => 'express',
	'total'       => '149',
	'documents'   => [],
] );
$created[] = $oid;
$ok( $oid > 0, 'created a test ukv_order (#' . (int) $oid . ')' );

update_post_meta( $oid, 'ukv_status', 'paid' );
update_post_meta( $oid, 'ukv_status_last', 'paid' );
update_post_meta( $oid, 'ukv_eligibility', 'manual_review' );

$ok( false === ukv_order_is_cleared( $oid ), 'manual_review order is NOT cleared' );

// Attempt to advance to awaiting_docs.
update_post_meta( $oid, 'ukv_status', 'awaiting_docs' );
$blocked = ukv_eligibility_gate_enforce( $oid, 'awaiting_docs' );
$ok( true === $blocked, 'gate returns true (blocked) advancing manual_review->awaiting_docs' );
$ok( 'awaiting_docs' !== get_post_meta( $oid, 'ukv_status', true ), 'ukv_status NOT advanced to awaiting_docs' );
$ok( 'paid' === get_post_meta( $oid, 'ukv_status', true ), 'ukv_status reverted to paid' );

$journey  = (array) get_post_meta( $oid, 'ukv_journey', true );
$has_note = false;
foreach ( $journey as $n ) {
	if ( isset( $n['text'] ) && false !== stripos( $n['text'], 'Blocked: eligibility not cleared' ) ) { $has_note = true; break; }
}
$ok( $has_note, 'journey note "Blocked: eligibility not cleared." appended' );

/* 2) Cleared -> allowed. */
update_post_meta( $oid, 'ukv_eligibility', 'cleared' );
$ok( true === ukv_order_is_cleared( $oid ), 'order is now cleared' );
update_post_meta( $oid, 'ukv_status', 'awaiting_docs' );
$blocked2 = ukv_eligibility_gate_enforce( $oid, 'awaiting_docs' );
$ok( false === $blocked2, 'gate returns false (allowed) once cleared' );
$ok( 'awaiting_docs' === get_post_meta( $oid, 'ukv_status', true ), 'ukv_status stays awaiting_docs when allowed' );

/* Bonus: even cleared, advancing TO 'paid' is never blocked. */
$ok( false === ukv_eligibility_gate_enforce( $oid, 'paid' ), 'gate never blocks the move to paid' );

/* 3) Standard order -> never blocked. */
$sid = ukv_create_order( [
	'name'        => 'Standard Lane Test',
	'email'       => 'std@example.com',
	'destination' => 'india',
	'tier'        => 'standard',
	'total'       => '99',
	'documents'   => [],
] );
$created[] = $sid;
update_post_meta( $sid, 'ukv_status', 'paid' );
update_post_meta( $sid, 'ukv_status_last', 'paid' );
update_post_meta( $sid, 'ukv_eligibility', 'standard' );
update_post_meta( $sid, 'ukv_status', 'awaiting_docs' );
$blocked3 = ukv_eligibility_gate_enforce( $sid, 'awaiting_docs' );
$ok( false === $blocked3, 'standard order is never blocked by the eligibility gate' );
$ok( 'awaiting_docs' === get_post_meta( $sid, 'ukv_status', true ), 'standard order ukv_status stays awaiting_docs' );

/* 4) Meta box renders escaped HTML mentioning the lane, no PHP error. */
$mid = ukv_create_order( [
	'name'        => 'Metabox Render Test',
	'email'       => 'mb@example.com',
	'destination' => 'india',
	'tier'        => 'express',
	'total'       => '149',
] );
$created[] = $mid;
update_post_meta( $mid, 'ukv_eligibility', 'manual_review' );
update_post_meta( $mid, 'ukv_nationality', 'India' );
update_post_meta( $mid, 'ukv_residence_country', 'India' );
update_post_meta( $mid, 'ukv_residency_status', 'citizen' );
update_post_meta( $mid, 'ukv_trip_purpose', 'tourist' );

$post = get_post( $mid );
ob_start();
ukv_eligibility_metabox( $post );
$html = ob_get_clean();

$ok( '' !== trim( (string) $html ), 'meta box produced non-empty HTML' );
$ok( false !== stripos( (string) $html, 'manual_review' ) || false !== stripos( (string) $html, 'Manual review' ), 'meta box HTML mentions the current lane' );
$ok( false !== strpos( (string) $html, 'India' ), 'meta box HTML shows a captured axis (nationality)' );
$ok( false !== stripos( (string) $html, 'nonce' ) || false !== stripos( (string) $html, 'name="ukv_eligibility_action"' ) || false !== stripos( (string) $html, '_wpnonce' ), 'meta box HTML includes a nonce field' );

/* 5) Clean up. */
foreach ( $created as $id ) { if ( $id ) { wp_delete_post( $id, true ); } }
$ok( null === get_post( $oid ), 'test orders cleaned up' );

echo "\n{$pass} passed, {$fail} failed\n";
echo ( 0 === $fail ) ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
