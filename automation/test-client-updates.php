<?php
/**
 * Test: UKV Client Updates (Smart Stories P12).
 * Run: cd /c/xampp/htdocs/ukvisa && php wp-cli.phar eval-file "<path>/automation/test-client-updates.php"
 */

$pass = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_draft_client_update' ), 'ukv_draft_client_update() is defined' );
$check( function_exists( 'ukv_send_client_update' ), 'ukv_send_client_update() is defined' );
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );
$check( function_exists( 'ukv_barrier_create' ), 'ukv_barrier_create() is defined' );

// 1. Create an Egypt order + a destination barrier.
$order_id = ukv_create_order( [
	'order_ref'   => 'UKV-TEST-' . substr( (string) time(), -6 ),
	'name'        => 'Test Traveller',
	'email'       => 'test.traveller@example.com',
	'destination' => 'Egypt',
	'tier'        => 'Standard',
	'total'       => 49,
] );
$check( $order_id > 0, "created Egypt order (#{$order_id})" );

$guidance = 'Egypt e-visa portal is down for ~48h; no action needed from you, we will resubmit automatically.';
$barrier_id = ukv_barrier_create( [
	'nature'      => 'temporary',
	'scope'       => 'destination',
	'destination' => 'egypt',
	'guidance'    => $guidance,
] );
$check( $barrier_id > 0, "created destination barrier (#{$barrier_id})" );

$order_ref = (string) get_post_meta( $order_id, 'ukv_order_ref', true );

// The order is open (status 'paid') so it should be in the barrier's audience.
$affected = ukv_affected_orders( $barrier_id );
$check( in_array( (int) $order_id, array_map( 'intval', $affected ), true ), 'order is an affected order for the barrier' );

// 2. Draft assertions.
$d = ukv_draft_client_update( $barrier_id, $order_id );
$check( is_array( $d ) && isset( $d['subject'], $d['body'], $d['wa_link'], $d['call_task'] ), 'draft returns subject/body/wa_link/call_task' );
$check( strpos( $d['body'], $guidance ) !== false, 'body contains the barrier guidance text' );
$check( strpos( $d['body'], $order_ref ) !== false, "body contains the order_ref ({$order_ref})" );
$check( strpos( $d['body'], 'Independent' ) !== false, 'body contains the compliance word "Independent"' );
$check( strpos( $d['subject'], 'Egypt' ) !== false && strpos( $d['subject'], $order_ref ) !== false, 'subject references Egypt + order_ref' );

// 3. Send appends exactly one journey audit note.
$before = count( (array) get_post_meta( $order_id, 'ukv_journey', true ) );
$sent   = ukv_send_client_update( $barrier_id, $order_id );
$check( true === $sent, 'ukv_send_client_update returned true' );

$journey = (array) get_post_meta( $order_id, 'ukv_journey', true );
$after   = count( $journey );
$check( $after === $before + 1, "journey grew by 1 ({$before} -> {$after})" );
$last    = end( $journey );
$check( is_array( $last ) && strpos( (string) ( $last['text'] ?? '' ), 'Proactive update' ) !== false, 'last journey note text contains "Proactive update"' );
$check( ( $last['channel'] ?? '' ) === 'email' && ( $last['agent'] ?? '' ) === 'system', 'last note channel=email, agent=system' );

// 4. Clean up.
wp_delete_post( $order_id, true );
wp_delete_post( $barrier_id, true );
echo "INFO — cleaned up order #{$order_id} and barrier #{$barrier_id}\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
