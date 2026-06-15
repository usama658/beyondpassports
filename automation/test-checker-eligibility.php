<?php
/**
 * Test: UKV Checker Eligibility inputs (Unit 5).
 * Visa checker gives the UK answer ONLY for UK nationality + UK residence; otherwise a "we'll confirm" message.
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-checker-eligibility.php"
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required function + shortcode present.
$check( function_exists( 'ukv_checker_eligibility_result' ), 'ukv_checker_eligibility_result() is defined' );
$check( shortcode_exists( 'ukv_eligibility_checker' ), '[ukv_eligibility_checker] shortcode is registered' );

// 1. Both UK -> lane 'uk'.
$uk = ukv_checker_eligibility_result( 'UK', 'United Kingdom' );
$check( is_array( $uk ) && isset( $uk['lane'], $uk['message'] ), 'UK result is array with lane + message' );
$check( ( $uk['lane'] ?? '' ) === 'uk', "UK+UK -> lane 'uk'; got '" . ( $uk['lane'] ?? '' ) . "'" );

// 2. Non-UK nationality -> lane 'non_standard' + message mentions "confirm".
$ns = ukv_checker_eligibility_result( 'India', 'UK' );
$check( ( $ns['lane'] ?? '' ) === 'non_standard', "India+UK -> lane 'non_standard'; got '" . ( $ns['lane'] ?? '' ) . "'" );
$check( false !== stripos( (string) ( $ns['message'] ?? '' ), 'confirm' ), 'non_standard message contains "confirm"' );

// Extra: UK nationality but non-UK residence is also non_standard (both must be UK).
$mixed = ukv_checker_eligibility_result( 'UK', 'France' );
$check( ( $mixed['lane'] ?? '' ) === 'non_standard', "UK nationality + France residence -> 'non_standard'" );

// 3. Shortcode renders non-empty HTML with no PHP error.
$html = do_shortcode( '[ukv_eligibility_checker]' );
$check( is_string( $html ) && '' !== trim( $html ), '[ukv_eligibility_checker] returns non-empty HTML' );
$check( false !== strpos( (string) $html, '<form' ), 'rendered HTML contains a <form>' );

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
