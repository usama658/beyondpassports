<?php
/**
 * Test: UKV Apply Intake Capture (#79).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-apply-intake.php"
 */

$pass = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

$check( function_exists( 'ukv_apply_intake_apply' ), 'ukv_apply_intake_apply() is defined' );
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() factory available' );

// ----------------------------------------------------------------- 1. happy path
$oid = ukv_create_order( [
	'order_ref'   => 'UKV-TEST-' . substr( (string) time(), -6 ),
	'name'        => 'Sam Tester',
	'email'       => 'sam@example.com',
	'destination' => 'Egypt',
	'tier'        => 'Standard',
	'total'       => 49,
] );
$check( $oid > 0, "created order (#{$oid})" );

ukv_apply_intake_apply( $oid, [
	'passport_expiry' => '2026-12-01',
	'travel_date'     => '2026-08-01',
	'consent'         => true,
] );
$check( get_post_meta( $oid, 'ukv_passport_expiry', true ) === '2026-12-01', "ukv_passport_expiry === '2026-12-01'" );
$check( get_post_meta( $oid, 'ukv_travel_date', true ) === '2026-08-01', "ukv_travel_date === '2026-08-01'" );
$check( get_post_meta( $oid, 'ukv_story_consent', true ) === '1', "ukv_story_consent === '1'" );

// ----------------------------------------------------------------- 2. consent = false
$oid2 = ukv_create_order( [
	'order_ref'   => 'UKV-TEST-' . substr( (string) ( time() + 1 ), -6 ),
	'name'        => 'No Consent',
	'email'       => 'noconsent@example.com',
	'destination' => 'India',
	'tier'        => 'Standard',
	'total'       => 49,
] );
$check( $oid2 > 0, "created order (#{$oid2})" );
ukv_apply_intake_apply( $oid2, [ 'consent' => false ] );
$check( get_post_meta( $oid2, 'ukv_story_consent', true ) !== '1', "consent=false -> ukv_story_consent is not '1'" );

// ----------------------------------------------------------------- 3. invalid date -> not stored, no fatal
$oid3 = ukv_create_order( [
	'order_ref'   => 'UKV-TEST-' . substr( (string) ( time() + 2 ), -6 ),
	'name'        => 'Bad Date',
	'email'       => 'baddate@example.com',
	'destination' => 'Turkey',
	'tier'        => 'Standard',
	'total'       => 49,
] );
$check( $oid3 > 0, "created order (#{$oid3})" );
ukv_apply_intake_apply( $oid3, [ 'passport_expiry' => 'not-a-date', 'travel_date' => '' ] );
$pe3 = get_post_meta( $oid3, 'ukv_passport_expiry', true );
$td3 = get_post_meta( $oid3, 'ukv_travel_date', true );
$check( $pe3 === '' || $pe3 === false, "invalid passport_expiry not stored (got: '" . var_export( $pe3, true ) . "')" );
$check( $td3 === '' || $td3 === false, "empty travel_date not stored (got: '" . var_export( $td3, true ) . "')" );

// guard: zero / missing order id must not fatal
ukv_apply_intake_apply( 0, [ 'consent' => true ] );
$check( true, 'ukv_apply_intake_apply(0, ...) did not fatal' );

// ----------------------------------------------------------------- 4. clean up
foreach ( [ $oid, $oid2, $oid3 ] as $id ) {
	if ( $id > 0 ) { wp_delete_post( $id, true ); }
}
echo "INFO — cleaned up orders #{$oid}, #{$oid2}, #{$oid3}\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
