<?php
/**
 * Test: UKV Smart Stories — Consented Testimonials (P14).
 * Run: cd /c/xampp/htdocs/ukvisa && php wp-cli.phar eval-file "<path>/automation/test-story-consent.php"
 */

$pass = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_set_story_consent' ), 'ukv_set_story_consent() is defined' );
$check( function_exists( 'ukv_has_story_consent' ), 'ukv_has_story_consent() is defined' );
$check( function_exists( 'ukv_generate_testimonial_draft' ), 'ukv_generate_testimonial_draft() is defined' );
$check( function_exists( 'ukv_story_has_leak' ), 'ukv_story_has_leak() (P13) is loaded for reuse' );

// 1. Create a delivered Egypt order WITHOUT consent -> draft returns 0.
$oid = ukv_create_order( [
	'order_ref'       => 'UKV-TEST-' . substr( (string) time(), -6 ),
	'name'            => 'Jane Doe',
	'email'           => 'jane@x.com',
	'destination'     => 'Egypt',
	'tier'            => 'Standard',
	'total'           => 49,
	'passport_number' => '123456789',
] );
$check( $oid > 0, "created Egypt order (#{$oid})" );
update_post_meta( $oid, 'ukv_status', 'delivered' );
update_post_meta( $oid, 'ukv_name', 'Jane Doe' );
update_post_meta( $oid, 'ukv_email', 'jane@x.com' );

$check( ukv_has_story_consent( $oid ) === false, 'order has NO consent initially' );
$check( ukv_generate_testimonial_draft( $oid ) === 0, 'no consent -> testimonial draft returns 0 (hard gate)' );

// 2. Record consent.
ukv_set_story_consent( $oid, true );
$check( ukv_has_story_consent( $oid ) === true, 'after ukv_set_story_consent(true), ukv_has_story_consent() is true' );

// 3. Add a journey note containing PII, then generate the testimonial.
$j = (array) get_post_meta( $oid, 'ukv_journey', true );
$j[] = [ 'date' => gmdate( 'Y-m-d H:i' ), 'agent' => 'agent', 'channel' => 'call', 'text' => 'Spoke to Jane Doe on jane@x.com about passport 123456789' ];
update_post_meta( $oid, 'ukv_journey', $j );

$id = ukv_generate_testimonial_draft( $oid );
$check( $id > 0, "consented -> testimonial draft created (#{$id})" );
$check( get_post_status( $id ) === 'draft', 'generated post status is draft (never published)' );

$post = get_post( $id );
$content = $post ? (string) $post->post_content : '';
$leaks = ukv_story_has_leak( $content, [ 'name' => 'Jane Doe', 'email' => 'jane@x.com', 'passport' => '123456789' ] );
$check( empty( $leaks ), 'leak gate reports the generated body is CLEAN (redaction reuse proven)' );
$check( strpos( $content, 'Jane Doe' ) === false, 'body does NOT contain "Jane Doe"' );
$check( strpos( $content, 'jane@x.com' ) === false, 'body does NOT contain "jane@x.com"' );

// 4. Clean up.
wp_delete_post( $oid, true );
if ( $id > 0 ) { wp_delete_post( $id, true ); }
echo "INFO — cleaned up order #{$oid} and post #{$id}\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
