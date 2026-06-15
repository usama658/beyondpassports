<?php
// Phase 1 Unit 1 — eligibility schema + router. Run: wp eval-file automation/test-eligibility.php
function e( $c, $m ) { echo ( $c ? 'PASS' : 'FAIL' ) . " — $m\n"; if ( ! $c ) { $GLOBALS['ef'] = true; } }
$GLOBALS['ef'] = false;
$std = [ 'nationality' => 'UK', 'residence_country' => 'United Kingdom', 'residency_status' => 'citizen', 'trip_purpose' => 'tourist' ];

e( 'standard' === ukv_eligibility_evaluate( $std ), 'UK+UK+citizen+tourist+no-refusal -> standard' );
e( 'manual_review' === ukv_eligibility_evaluate( array_merge( $std, [ 'nationality' => 'India' ] ) ), 'non-UK passport -> manual_review' );
e( 'manual_review' === ukv_eligibility_evaluate( array_merge( $std, [ 'residence_country' => 'Spain' ] ) ), 'non-UK residence -> manual_review' );
e( 'manual_review' === ukv_eligibility_evaluate( array_merge( $std, [ 'residency_status' => 'visa_holder' ] ) ), 'non-citizen status -> manual_review' );
e( 'manual_review' === ukv_eligibility_evaluate( array_merge( $std, [ 'trip_purpose' => 'business' ] ) ), 'business purpose -> manual_review' );
e( 'manual_review' === ukv_eligibility_evaluate( array_merge( $std, [ 'prior_refusal' => true ] ) ), 'prior refusal -> manual_review' );
e( 'manual_review' === ukv_eligibility_evaluate( array_merge( $std, [ 'is_minor' => true ] ) ), 'minor -> manual_review' );
e( ukv_funnel_is_standard( 'UK', 'UK', 'citizen', 'tourist' ) && ! ukv_funnel_is_standard( 'India', 'UK', 'citizen' ), 'funnel_is_standard helper' );

// apply + store on a real order
$oid = ukv_create_order( [ 'order_ref' => 'ELG-1', 'name' => 'Test', 'email' => 't@e.com', 'destination' => 'Egypt', 'tier' => 'Standard', 'total' => 49 ] );
ukv_eligibility_apply( $oid, [ 'nationality' => 'India', 'residence_country' => 'UK', 'residency_status' => 'visa_holder', 'trip_purpose' => 'tourist', 'is_minor' => false, 'prior_refusal' => false ] );
e( 'India' === get_post_meta( $oid, 'ukv_nationality', true ), 'nationality stored' );
e( 'manual_review' === get_post_meta( $oid, 'ukv_eligibility', true ), 'non-standard order -> manual_review' );
e( false === ukv_order_is_cleared( $oid ), 'manual_review not cleared' );

// a standard order
$oid2 = ukv_create_order( [ 'order_ref' => 'ELG-2', 'name' => 'Brit', 'email' => 'b@e.com', 'destination' => 'Egypt', 'tier' => 'Standard', 'total' => 49 ] );
ukv_eligibility_apply( $oid2, [ 'nationality' => 'UK', 'residence_country' => 'UK', 'residency_status' => 'citizen', 'trip_purpose' => 'tourist' ] );
e( 'standard' === get_post_meta( $oid2, 'ukv_eligibility', true ), 'standard order -> standard' );
e( true === ukv_order_is_cleared( $oid2 ), 'standard is cleared' );

// agent decision not overwritten by re-apply
update_post_meta( $oid, 'ukv_eligibility', 'cleared' );
ukv_eligibility_apply( $oid, [ 'nationality' => 'India', 'residence_country' => 'UK', 'residency_status' => 'visa_holder' ] );
e( 'cleared' === get_post_meta( $oid, 'ukv_eligibility', true ), 're-apply does not overwrite a cleared decision' );

wp_delete_post( $oid, true ); wp_delete_post( $oid2, true );
echo $GLOBALS['ef'] ? "\nRESULT: FAILURES PRESENT\n" : "\nRESULT: ALL PASS\n";
