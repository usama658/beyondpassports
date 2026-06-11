<?php
/**
 * Test: UKV Emails (lean email lifecycle engine).
 * Run: cd /c/xampp/htdocs/ukvisa && php wp-cli.phar eval-file "<path>/automation/test-emails.php"
 */

$pass = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_email_template' ), 'ukv_email_template() is defined' );
$check( function_exists( 'ukv_email_send' ), 'ukv_email_send() is defined' );
$check( function_exists( 'ukv_email_fire' ), 'ukv_email_fire() is defined' );
$check( function_exists( 'ukv_email_on_status_change' ), 'ukv_email_on_status_change() is defined' );
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );

// Create an Egypt order with email.
$oid = ukv_create_order( [
	'order_ref'   => 'UKV-TEST-' . substr( (string) time(), -6 ),
	'name'        => 'Test Traveller',
	'email'       => 'test.traveller@example.com',
	'destination' => 'Egypt',
	'tier'        => 'Standard',
	'total'       => 49,
] );
$check( $oid > 0, "created Egypt order (#{$oid})" );

// 1. Each of the 7 events builds a non-empty subject + body with the compliance footer.
$events = [ 'order_paid', 'docs_needed', 'submitted', 'decision', 'delivered', 'review_request', 'checker_abandon' ];
foreach ( $events as $ev ) {
	$tpl = ukv_email_template( $ev, $oid );
	$ok  = is_array( $tpl )
		&& ! empty( $tpl['subject'] )
		&& ! empty( $tpl['body'] )
		&& strpos( $tpl['body'], 'Independent service' ) !== false;
	$check( $ok, "template '{$ev}' has non-empty subject + body with compliance footer" );
}

// 2. Fire order_paid → true, log has 1 entry, sent contains order_paid.
$fired = ukv_email_fire( 'order_paid', $oid );
$check( true === $fired, "ukv_email_fire('order_paid') returned true" );
$log = (array) get_post_meta( $oid, 'ukv_email_log', true );
$check( count( $log ) === 1, 'ukv_email_log has exactly 1 entry' );
$sent = (array) get_post_meta( $oid, 'ukv_email_sent', true );
$check( in_array( 'order_paid', $sent, true ), "ukv_email_sent contains 'order_paid'" );

// 3. Fire order_paid AGAIN → false (idempotent), log still 1 entry.
$again = ukv_email_fire( 'order_paid', $oid );
$check( false === $again, "second ukv_email_fire('order_paid') returned false (idempotent)" );
$log = (array) get_post_meta( $oid, 'ukv_email_log', true );
$check( count( $log ) === 1, 'ukv_email_log still has exactly 1 entry (no resend)' );

// 4. Status change → submitted fires exactly one 'submitted'.
ukv_email_on_status_change( $oid, 'submitted' );
$sent = (array) get_post_meta( $oid, 'ukv_email_sent', true );
$check( in_array( 'submitted', $sent, true ), "after status->submitted, ukv_email_sent contains 'submitted'" );
$log_submitted = array_filter( (array) get_post_meta( $oid, 'ukv_email_log', true ), fn( $e ) => ( $e['event'] ?? '' ) === 'submitted' );
$check( count( $log_submitted ) === 1, "exactly one 'submitted' log entry" );

// 5. Status change → delivered fires BOTH delivered and review_request.
ukv_email_on_status_change( $oid, 'delivered' );
$sent = (array) get_post_meta( $oid, 'ukv_email_sent', true );
$check( in_array( 'delivered', $sent, true ) && in_array( 'review_request', $sent, true ), "after status->delivered, ukv_email_sent contains BOTH 'delivered' and 'review_request'" );

// 6. Clean up.
wp_delete_post( $oid, true );
echo "INFO — cleaned up order #{$oid}\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
