<?php
// Phase 1 Unit 9 — eligibility e2e: drive BOTH lanes. Run: wp eval-file automation/test-eligibility-e2e.php
// Isolation-hardened (synthetic dest 'Eligland'); cleans up.
function e( $c, $m ) { echo ( $c ? 'PASS' : 'FAIL' ) . " — $m\n"; if ( ! $c ) { $GLOBALS['ef'] = true; } }
$GLOBALS['ef'] = false;
$orders = [];

echo "== Standard lane (UK + UK) ==\n";
$std = ukv_create_order( [ 'order_ref' => 'ELE2E-STD', 'name' => 'Brit Tourist', 'email' => 'brit@e2e.test', 'destination' => 'Eligland', 'tier' => 'Standard', 'total' => 49 ] );
$orders[] = $std;
ukv_eligibility_apply( $std, [ 'nationality' => 'UK', 'residence_country' => 'UK', 'residency_status' => 'citizen', 'trip_purpose' => 'tourist' ] );
e( 'standard' === get_post_meta( $std, 'ukv_eligibility', true ), 'UK+UK+citizen+tourist -> standard' );
e( false === ukv_eligibility_gate_enforce( $std, 'awaiting_docs' ), 'standard order NOT blocked from advancing' );
e( ukv_order_is_cleared( $std ), 'standard is cleared (flows on fixed tier)' );

echo "== Manual-review lane (non-UK) -> blocked -> clear -> quote -> flows ==\n";
$mr = ukv_create_order( [ 'order_ref' => 'ELE2E-MR', 'name' => 'Other Pax', 'email' => 'other@e2e.test', 'destination' => 'Eligland', 'tier' => 'Standard', 'total' => 49 ] );
$orders[] = $mr;
ukv_eligibility_apply( $mr, [ 'nationality' => 'India', 'residence_country' => 'UK', 'residency_status' => 'visa_holder', 'trip_purpose' => 'business' ] );
e( 'manual_review' === get_post_meta( $mr, 'ukv_eligibility', true ), 'non-UK/business -> manual_review' );
update_post_meta( $mr, 'ukv_status', 'awaiting_docs' );
e( true === ukv_eligibility_gate_enforce( $mr, 'awaiting_docs' ), 'manual_review BLOCKED from advancing before clearance' );
e( 'awaiting_docs' !== get_post_meta( $mr, 'ukv_status', true ), 'blocked move was reverted' );

// agent sets a bespoke quote + clears
ukv_set_quote( $mr, 149.0 );
e( 149.0 === ukv_quote_amount( $mr ), 'bespoke quote set (not a fixed tier)' );
e( true === ukv_quote_send( $mr ), 'quote/payment-link sent' );
update_post_meta( $mr, 'ukv_eligibility', 'cleared' ); // agent clears after verifying rules + payment
e( false === ukv_eligibility_gate_enforce( $mr, 'awaiting_docs' ), 'cleared manual_review may now advance' );

echo "== Checker lanes ==\n";
e( 'uk' === ukv_checker_eligibility_result( 'UK', 'UK' )['lane'], 'checker: UK+UK -> uk answer' );
e( 'non_standard' === ukv_checker_eligibility_result( 'India', 'UK' )['lane'], 'checker: non-UK -> we-will-confirm' );

foreach ( array_unique( $orders ) as $o ) { wp_delete_post( $o, true ); }
echo $GLOBALS['ef'] ? "\nRESULT: FAILURES PRESENT\n" : "\nRESULT: ALL PASS\n";
