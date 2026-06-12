<?php
// #91 Production Line end-to-end: drive one order through every stage, asserting the whole machine
// interoperates (gates, required-docs, QA, govt fields, emails, appointment, group, discounts, supply chain,
// SOP, owner digest). Isolation-hardened: synthetic destination 'Prodland' + unique refs; cleans up.
// Run: wp eval-file automation/test-production-line-e2e.php
function e( $c, $m ) { echo ( $c ? 'PASS' : 'FAIL' ) . " — $m\n"; if ( ! $c ) { $GLOBALS['ef'] = true; } }
function have( $fn ) { return function_exists( $fn ); }
$GLOBALS['ef'] = false;
wp_set_current_user( 1 );
$orders = [];

$oid = ukv_create_order( [ 'order_ref' => 'PL-E2E-1', 'name' => 'Pat Pipeline', 'email' => 'pat@prod.test', 'destination' => 'Prodland', 'tier' => 'Standard', 'service_fee' => 49, 'govt_fee' => 25, 'total' => 74 ] );
$orders[] = $oid;
update_post_meta( $oid, 'ukv_travel_date', gmdate( 'Y-m-d', time() + 40 * 86400 ) );

echo "== Stage gate: can't enter doc_review with no documents ==\n";
if ( have( 'ukv_stage_gate_enforce' ) ) {
	update_post_meta( $oid, 'ukv_status', 'doc_review' );
	$blocked = ukv_stage_gate_enforce( $oid, 'doc_review' );
	e( $blocked && 'doc_review' !== get_post_meta( $oid, 'ukv_status', true ), 'move to doc_review blocked + reverted when no docs' );
} else { e( false, 'ukv_stage_gate_enforce missing' ); }

echo "== Add documents -> can enter doc_review ==\n";
update_post_meta( $oid, 'ukv_documents', [ 9001, 9002 ] ); // 2 dummy attachment ids (meet default required = 2)
if ( have( 'ukv_sync_required_count' ) ) { ukv_sync_required_count( $oid ); }
if ( have( 'ukv_stage_can_enter' ) ) { $r = ukv_stage_can_enter( $oid, 'doc_review' ); e( ! empty( $r['ok'] ), 'doc_review entry allowed once docs present' ); }
if ( have( 'ukv_order_docs_complete' ) ) { e( ukv_order_docs_complete( $oid ), 'required-docs completeness met (2/2)' ); }
update_post_meta( $oid, 'ukv_status', 'doc_review' );
update_post_meta( $oid, 'ukv_status_last', 'doc_review' );

echo "== QA gate: can't submit without sign-off ==\n";
if ( have( 'ukv_qa_can_submit' ) ) {
	$r = ukv_qa_can_submit( $oid ); e( empty( $r['ok'] ), 'submit blocked before sign-off' );
	update_post_meta( $oid, 'ukv_qa_signed_off', '1' );
	$r = ukv_qa_can_submit( $oid ); e( ! empty( $r['ok'] ), 'submit allowed after docs complete + sign-off' );
}

echo "== Submit fires the submitted email ==\n";
if ( have( 'ukv_email_on_status_change' ) ) {
	update_post_meta( $oid, 'ukv_status', 'submitted' ); update_post_meta( $oid, 'ukv_status_last', 'submitted' );
	ukv_email_on_status_change( $oid, 'submitted' );
	$sent = (array) get_post_meta( $oid, 'ukv_email_sent', true );
	e( in_array( 'submitted', $sent, true ), 'submitted email fired + logged' );
}

echo "== Govt ref recorded -> can advance toward delivered ==\n";
update_post_meta( $oid, 'ukv_govt_ref', 'PLGOV-123' );
if ( have( 'ukv_stage_can_enter' ) ) {
	$r = ukv_stage_can_enter( $oid, 'awaiting_decision' ); e( ! empty( $r['ok'] ), 'awaiting_decision allowed once govt ref set' );
	$r = ukv_stage_can_enter( $oid, 'delivered' ); e( ! empty( $r['ok'] ), 'delivered allowed once govt ref set' );
}

echo "== Appointment workflow ==\n";
if ( have( 'ukv_set_appointment' ) ) {
	ukv_set_appointment( $oid, [ 'centre' => 'VFS Prodland', 'ref' => 'APT-PL', 'date' => '2026-08-01', 'status' => 'booked' ] );
	e( 'booked' === ukv_appointment_status( $oid ), 'appointment status = booked' );
	e( false !== stripos( ukv_appointment_pack( $oid ), 'passport' ), 'appointment pack lists passport' );
}

echo "== Group / linked orders ==\n";
if ( have( 'ukv_link_orders' ) ) {
	$oid2 = ukv_create_order( [ 'order_ref' => 'PL-E2E-2', 'name' => 'Sam Sibling', 'email' => 'sam@prod.test', 'destination' => 'Prodland', 'tier' => 'Standard', 'total' => 74 ] );
	$orders[] = $oid2;
	$gid = ukv_link_orders( [ $oid, $oid2 ] );
	e( $gid && in_array( $oid2, ukv_group_orders( $gid ), true ), 'two orders linked as a group' );
}

echo "== Discounts: returning customer + review incentive ==\n";
if ( have( 'ukv_is_returning_customer' ) ) {
	$rep = ukv_create_order( [ 'order_ref' => 'PL-E2E-REP', 'name' => 'Pat Pipeline', 'email' => 'pat@prod.test', 'destination' => 'Prodland', 'tier' => 'Standard', 'total' => 74 ] );
	$orders[] = $rep;
	e( ukv_is_returning_customer( 'pat@prod.test' ), 'returning customer detected (2 orders, same email)' );
	if ( have( 'ukv_issue_loyalty_discount' ) ) { e( '' !== ukv_issue_loyalty_discount( $rep ), 'loyalty discount issued for returning customer' ); }
	if ( have( 'ukv_issue_review_discount' ) ) { e( '' !== ukv_issue_review_discount( $oid ), 'review-incentive discount issued' ); }
}

echo "== Supply chain + SOP + owner digest ==\n";
if ( have( 'ukv_supply_for_order' ) ) { e( count( ukv_supply_for_order( $oid ) ) > 0, 'supply-chain nodes resolve for the order (globals)' ); }
if ( have( 'ukv_stage_sop' ) ) { $sop = ukv_stage_sop( 'submitted' ); e( ! empty( $sop ), 'SOP exists for the current stage' ); }
if ( have( 'ukv_set_owner' ) && have( 'ukv_owner_digest' ) ) {
	ukv_set_owner( $oid, 1 );
	$dg = ukv_owner_digest( 1 );
	e( is_array( $dg ) && isset( $dg['open'] ) && $dg['open'] >= 1, 'owner digest includes the owned open order' );
}

// Cleanup
foreach ( array_unique( $orders ) as $o ) { wp_delete_post( $o, true ); }
echo $GLOBALS['ef'] ? "\nRESULT: FAILURES PRESENT\n" : "\nRESULT: ALL PASS\n";
