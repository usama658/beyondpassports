<?php
/**
 * Test: UKV Stage-transition Gates (Production Line #90) — ukv-stage-gates.php
 * Proves: orders can only ADVANCE when the target stage's entry criteria are met;
 * the gate reverts ukv_status, logs a journey note, and DELEGATES 'submitted' to the
 * existing QA gate (never blocking it here).
 * Run: cd /c/xampp/htdocs/ukvisa && /c/xampp/php/php.exe -d memory_limit=512M wp-cli.phar eval-file "<abs path>" 2>&1 | tail -40
 */
defined( 'ABSPATH' ) || exit;

$pass = 0;
$fail = 0;
$ok = static function ( bool $cond, string $msg ) use ( &$pass, &$fail ) {
	if ( $cond ) { $pass++; echo "PASS — {$msg}\n"; }
	else { $fail++; echo "FAIL — {$msg}\n"; }
};

$ok( function_exists( 'ukv_stage_entry_requirements' ), 'ukv_stage_entry_requirements() is defined' );
$ok( function_exists( 'ukv_stage_can_enter' ), 'ukv_stage_can_enter() is defined' );
$ok( function_exists( 'ukv_stage_gate_enforce' ), 'ukv_stage_gate_enforce() is defined' );
$ok( function_exists( 'ukv_create_order' ), 'ukv_create_order() factory dependency is loaded' );

$created = [];

/* 1) Order at 'paid' with NO documents -> cannot enter doc_review; gate blocks + reverts + logs. */
$oid = ukv_create_order( [
	'name'        => 'Stage Gate Test',
	'email'       => 'stage@example.com',
	'destination' => 'india',
	'tier'        => 'express',
	'total'       => '149',
	'documents'   => [],
] );
$created[] = $oid;
$ok( $oid > 0, 'created a test ukv_order (#' . (int) $oid . ')' );

// Established prior valid stage.
update_post_meta( $oid, 'ukv_status_last', 'paid' );

$c1 = ukv_stage_can_enter( $oid, 'doc_review' );
$ok( is_array( $c1 ) && isset( $c1['ok'], $c1['reasons'] ), 'ukv_stage_can_enter returns [ok, reasons]' );
$ok( false === $c1['ok'], 'doc_review with no docs: ok === false (needs a document)' );
$ok( ! empty( $c1['reasons'] ), 'doc_review with no docs: reasons listed (' . count( $c1['reasons'] ) . ')' );

// Attempt the move to doc_review.
update_post_meta( $oid, 'ukv_status', 'doc_review' );
$blocked = ukv_stage_gate_enforce( $oid, 'doc_review' );
$ok( true === $blocked, 'ukv_stage_gate_enforce returns true (blocked) entering doc_review with no docs' );
$ok( 'doc_review' !== get_post_meta( $oid, 'ukv_status', true ), 'ukv_status NOT changed to doc_review after block' );
$ok( 'paid' === get_post_meta( $oid, 'ukv_status', true ), 'ukv_status reverted to previous stage (paid)' );

$journey  = (array) get_post_meta( $oid, 'ukv_journey', true );
$has_note = false;
foreach ( $journey as $n ) {
	if ( isset( $n['text'] ) && false !== stripos( $n['text'], 'Stage move to doc_review blocked' ) ) { $has_note = true; break; }
}
$ok( $has_note, 'journey note "Stage move to doc_review blocked: ..." was appended (agent=system, channel=internal)' );

/* 2) Add a document -> can enter doc_review; gate allows. */
update_post_meta( $oid, 'ukv_documents', [ 9001 ] );
$c2 = ukv_stage_can_enter( $oid, 'doc_review' );
$ok( true === $c2['ok'], 'doc_review with a document: ok === true' );
$ok( empty( $c2['reasons'] ), 'doc_review with a document: no failing reasons' );

update_post_meta( $oid, 'ukv_status', 'doc_review' );
$blocked2 = ukv_stage_gate_enforce( $oid, 'doc_review' );
$ok( false === $blocked2, 'ukv_stage_gate_enforce returns false (allowed) entering doc_review with a document' );
$ok( 'doc_review' === get_post_meta( $oid, 'ukv_status', true ), 'ukv_status stays doc_review when allowed' );

/* 3) delivered requires a recorded government reference. */
update_post_meta( $oid, 'ukv_govt_ref', '' );
$c3a = ukv_stage_can_enter( $oid, 'delivered' );
$ok( false === $c3a['ok'], 'delivered with empty ukv_govt_ref: ok === false (cannot deliver an unsubmitted order)' );

update_post_meta( $oid, 'ukv_govt_ref', 'G123' );
$c3b = ukv_stage_can_enter( $oid, 'delivered' );
$ok( true === $c3b['ok'], 'delivered with ukv_govt_ref=G123: ok === true' );

/* 4) 'submitted' is delegated to the QA gate — this engine never blocks it. */
$c4 = ukv_stage_can_enter( $oid, 'submitted' );
$ok( true === $c4['ok'], "ukv_stage_can_enter(submitted) ok === true (delegated to QA gate)" );
$ok( false === ukv_stage_gate_enforce( $oid, 'submitted' ), 'ukv_stage_gate_enforce(submitted) returns false (engine does not block it)' );

/* Bonus: a stage with no defined requirements is always enterable. */
$ok( true === ukv_stage_can_enter( $oid, 'awaiting_docs' )['ok'], 'stage with no requirements (awaiting_docs) is enterable' );

/* 5) Clean up. */
foreach ( $created as $id ) { if ( $id ) { wp_delete_post( $id, true ); } }
$ok( null === get_post( $oid ), 'test order(s) cleaned up' );

echo "\n{$pass} passed, {$fail} failed\n";
echo ( 0 === $fail ) ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
