<?php
/**
 * Test: UKV Eligibility Intake Capture (Unit 4).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-eligibility-intake.php"
 */

$pass = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

$check( function_exists( 'ukv_eligibility_intake_from_prepared' ), 'ukv_eligibility_intake_from_prepared() is defined' );
$check( function_exists( 'ukv_eligibility_apply' ), 'ukv_eligibility_apply() (router) available' );
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() factory available' );

// ----------------------------------------------------------------- 1. mapper maps prepared_data -> axes
$axes = ukv_eligibility_intake_from_prepared( [
	'select-2' => 'India',
	'select-3' => 'UK',
	'select-4' => 'visa_holder',
	'select-5' => 'tourist',
] );
$check( is_array( $axes ), 'mapper returns an array' );
$check( ( $axes['nationality'] ?? null ) === 'India', "mapper nationality === 'India'" );
$check( ( $axes['residence_country'] ?? null ) === 'UK', "mapper residence_country === 'UK'" );
$check( ( $axes['residency_status'] ?? null ) === 'visa_holder', "mapper residency_status === 'visa_holder'" );
$check( ( $axes['trip_purpose'] ?? null ) === 'tourist', "mapper trip_purpose === 'tourist'" );

// ----------------------------------------------------------------- 2. apply mapped axes to an order -> router runs
$oid = ukv_create_order( [
	'order_ref'   => 'UKV-ELIG-' . substr( (string) time(), -6 ),
	'name'        => 'Elig Tester',
	'email'       => 'elig@example.com',
	'destination' => 'India',
	'tier'        => 'Standard',
	'total'       => 49,
] );
$check( $oid > 0, "created order (#{$oid})" );

ukv_eligibility_apply( $oid, $axes );
$check( get_post_meta( $oid, 'ukv_nationality', true ) === 'India', "ukv_nationality === 'India'" );
$check( get_post_meta( $oid, 'ukv_residence_country', true ) === 'UK', "ukv_residence_country === 'UK'" );
$check( get_post_meta( $oid, 'ukv_residency_status', true ) === 'visa_holder', "ukv_residency_status === 'visa_holder'" );
$check( get_post_meta( $oid, 'ukv_eligibility', true ) === 'manual_review', "ukv_eligibility === 'manual_review' (visa_holder => non-standard)" );

// ----------------------------------------------------------------- 3. empty / absent keys -> no fatal, no bogus values
$empty = ukv_eligibility_intake_from_prepared( [] );
$check( $empty === [], 'empty prepared_data -> empty axes array (no bogus keys)' );

// absent keys must NOT appear in the axes (so router never stores bogus meta)
$check( ! array_key_exists( 'nationality', $empty ), 'absent nationality -> not present in axes' );
$check( ! array_key_exists( 'prior_refusal', $empty ), 'absent prior_refusal -> not present in axes' );

// empty-string values are skipped too (treated as absent)
$blank = ukv_eligibility_intake_from_prepared( [ 'select-2' => '', 'select-3' => '   ' ] );
$check( ! array_key_exists( 'nationality', $blank ) && ! array_key_exists( 'residence_country', $blank ), 'blank/whitespace values -> not mapped' );

// applying empty axes to a fresh order must not fatal nor invent values
$oid2 = ukv_create_order( [
	'order_ref'   => 'UKV-ELIG-' . substr( (string) ( time() + 1 ), -6 ),
	'name'        => 'No Axes',
	'email'       => 'noaxes@example.com',
	'destination' => 'Turkey',
	'tier'        => 'Standard',
	'total'       => 49,
] );
$check( $oid2 > 0, "created order (#{$oid2})" );
ukv_eligibility_apply( $oid2, $empty );
$nat2 = get_post_meta( $oid2, 'ukv_nationality', true );
$check( $nat2 === '' || $nat2 === false, "no nationality stored for empty axes (got: '" . var_export( $nat2, true ) . "')" );
$check( true, 'ukv_eligibility_apply(order, []) did not fatal' );

// prior_refusal candidate present + truthy -> mapped as bool true
$ref = ukv_eligibility_intake_from_prepared( [ 'checkbox-2' => '1' ] );
$check( ( $ref['prior_refusal'] ?? null ) === true, 'prior_refusal checkbox-2=1 -> true' );

// ----------------------------------------------------------------- 4. clean up
foreach ( [ $oid, $oid2 ] as $id ) {
	if ( $id > 0 ) { wp_delete_post( $id, true ); }
}
echo "INFO — cleaned up orders #{$oid}, #{$oid2}\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
