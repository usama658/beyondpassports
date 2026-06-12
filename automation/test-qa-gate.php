<?php
/**
 * Test: UKV Pre-submission QA Gate (Gap #75) — ukv-qa-gate.php
 * Proves: incomplete/unsigned orders cannot transition to 'submitted'; the gate
 * reverts ukv_status, logs a journey note, and once complete + signed off it allows.
 * Run: cd /c/xampp/htdocs/ukvisa && /c/xampp/php/php.exe -d memory_limit=512M wp-cli.phar eval-file "<abs path>" 2>&1 | tail -40
 */
defined( 'ABSPATH' ) || exit;

$pass = 0;
$fail = 0;
$ok = static function ( bool $cond, string $msg ) use ( &$pass, &$fail ) {
	if ( $cond ) { $pass++; echo "PASS — {$msg}\n"; }
	else { $fail++; echo "FAIL — {$msg}\n"; }
};

$ok( function_exists( 'ukv_qa_can_submit' ), 'ukv_qa_can_submit() is defined' );
$ok( function_exists( 'ukv_qa_gate_enforce' ), 'ukv_qa_gate_enforce() is defined' );
$ok( function_exists( 'ukv_create_order' ), 'ukv_create_order() factory dependency is loaded' );

$created = [];

/* 1) Order with NO documents, no sign-off -> not OK, with reasons. */
$oid = ukv_create_order( [
	'name'        => 'QA Gate Test',
	'email'       => 'qa@example.com',
	'destination' => 'india',
	'tier'        => 'express',
	'total'       => '149',
	'documents'   => [],
] );
$created[] = $oid;
$ok( $oid > 0, 'created a test ukv_order (#' . (int) $oid . ')' );

$r1 = ukv_qa_can_submit( $oid );
$ok( is_array( $r1 ) && isset( $r1['ok'], $r1['reasons'] ), 'ukv_qa_can_submit returns [ok, reasons]' );
$ok( false === $r1['ok'], 'incomplete order: ok === false' );
$ok( ! empty( $r1['reasons'] ) && is_array( $r1['reasons'] ), 'incomplete order: reasons listed (' . count( $r1['reasons'] ) . ')' );

/* 2) Simulate moving to 'submitted' -> gate blocks, reverts status, logs journey. */
// Put the order in a realistic pre-submit state, then attempt the transition.
update_post_meta( $oid, 'ukv_status_last', 'doc_review' );
update_post_meta( $oid, 'ukv_status', 'submitted' ); // the "attempted" new value
$blocked = ukv_qa_gate_enforce( $oid, 'submitted' );
$ok( true === $blocked, 'ukv_qa_gate_enforce returns true (blocked) for incomplete order' );
$ok( 'submitted' !== get_post_meta( $oid, 'ukv_status', true ), 'ukv_status reverted (NOT submitted) after block' );
$ok( 'doc_review' === get_post_meta( $oid, 'ukv_status', true ), 'ukv_status reverted to previous (doc_review)' );

$journey = (array) get_post_meta( $oid, 'ukv_journey', true );
$has_note = false;
foreach ( $journey as $n ) {
	if ( isset( $n['text'] ) && false !== stripos( $n['text'], 'blocked by QA gate' ) ) { $has_note = true; break; }
}
$ok( $has_note, 'journey note mentioning "blocked by QA gate" was appended' );

/* 3) Add a document + sign-off -> can submit, gate allows. */
update_post_meta( $oid, 'ukv_documents', [ 9999 ] ); // dummy attachment id
update_post_meta( $oid, 'ukv_qa_signed_off', '1' );

$r3 = ukv_qa_can_submit( $oid );
$ok( true === $r3['ok'], 'with doc + sign-off: ok === true' );
$ok( empty( $r3['reasons'] ), 'with doc + sign-off: no failing reasons' );

update_post_meta( $oid, 'ukv_status', 'submitted' );
$blocked2 = ukv_qa_gate_enforce( $oid, 'submitted' );
$ok( false === $blocked2, 'ukv_qa_gate_enforce returns false (allowed) once complete + signed off' );
$ok( 'submitted' === get_post_meta( $oid, 'ukv_status', true ), 'ukv_status stays submitted when allowed' );

/* Bonus: gate only fires for an attempted 'submitted' status. */
$ok( false === ukv_qa_gate_enforce( $oid, 'doc_review' ), 'gate is a no-op (false) for non-submitted target status' );

/* 4) Clean up. */
foreach ( $created as $id ) { if ( $id ) { wp_delete_post( $id, true ); } }
$ok( null === get_post( $oid ), 'test order(s) cleaned up' );

echo "\n{$pass} passed, {$fail} failed\n";
echo ( 0 === $fail ) ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
