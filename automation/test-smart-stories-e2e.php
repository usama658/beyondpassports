<?php
// Smart Stories P16 — end-to-end integration test across P11+P12+P13+P14.
// One scripted pass on shared seeded data. Run: wp eval-file automation/test-smart-stories-e2e.php
function e( $cond, $msg ) { echo ( $cond ? 'PASS' : 'FAIL' ) . " — $msg\n"; if ( ! $cond ) { $GLOBALS['ef'] = true; } }
$GLOBALS['ef'] = false;
$orders = []; $barriers = []; $posts = [];

function mk_order( $ref, $dest, $status, $tier, $days_old, $name, $email, &$orders ) {
	$oid = ukv_create_order( [ 'order_ref' => $ref, 'name' => $name, 'email' => $email, 'destination' => $dest, 'tier' => $tier, 'total' => 49 ] );
	update_post_meta( $oid, 'ukv_status', $status );
	if ( $days_old ) { $t = gmdate( 'Y-m-d H:i:s', time() - $days_old * 86400 ); wp_update_post( [ 'ID' => $oid, 'post_date' => $t, 'post_date_gmt' => $t ] ); }
	$orders[] = $oid; return $oid;
}

echo "== Seed ==\n";
$eg1 = mk_order( 'E2E-EG-1', 'Testlandia', 'paid', 'Standard', 0, 'Alice A', 'alice@x.com', $orders );
$eg2 = mk_order( 'E2E-EG-2', 'Testlandia', 'awaiting_docs', 'Standard', 0, 'Bob B', 'bob@x.com', $orders );
$egDone = mk_order( 'E2E-EG-D', 'Testlandia', 'delivered', 'Standard', 0, 'Carol C', 'carol@x.com', $orders );
$tk1 = mk_order( 'E2E-TK-1', 'Turkey', 'paid', 'Standard', 0, 'Dan D', 'dan@x.com', $orders );

// CHECK 1: destination barrier surfaces on every OPEN Testlandia order via live query; zero duplication.
echo "== Check 1: fan-out + no duplication ==\n";
$bid = ukv_barrier_create( [ 'nature' => 'temporary', 'scope' => 'destination', 'destination' => 'testlandia',
	'guidance' => 'The Testlandia e-visa portal is briefly down; no action needed, we resubmit automatically.' ] );
$barriers[] = $bid;
$aff = ukv_affected_orders( $bid ); sort( $aff ); $exp = [ $eg1, $eg2 ]; sort( $exp );
e( $aff === $exp, 'barrier fans out to the 2 OPEN Testlandia orders only (delivered + Turkey excluded)' );
e( in_array( $bid, ukv_barriers_for_order( $eg1 ), true ) && in_array( $bid, ukv_barriers_for_order( $eg2 ), true ), 'both open Testlandia orders surface it live' );
e( ! in_array( $bid, ukv_barriers_for_order( $tk1 ), true ), 'Turkey order does not surface it' );
$stored = get_posts( [ 'post_type' => 'ukv_barrier', 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => -1, 'meta_query' => [ [ 'key' => 'destination', 'value' => 'testlandia' ], [ 'key' => 'status', 'value' => 'open' ] ] ] );
e( count( $stored ) === 1, 'single stored record — fan-out is by query, never copied' );

// CHECK 2: auto-detect cron idempotent.
echo "== Check 2: auto-detect idempotent ==\n";
$sla = mk_order( 'E2E-SLA', 'India', 'paid', 'Standard', 5, 'Eve E', 'eve@x.com', $orders ); // 5d old, 72h SLA -> breach (India, to keep Testlandia fan-out = 2)
ukv_auto_detect_barriers(); ukv_auto_detect_barriers();
$slaB = get_posts( [ 'post_type' => 'ukv_barrier', 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => -1, 'meta_query' => [ [ 'key' => 'rule_key', 'value' => 'E2E-SLA:sla_breach' ] ] ] );
e( count( $slaB ) === 1, 'two cron runs create exactly one SLA barrier (idempotent)' );
foreach ( $slaB as $b ) { $barriers[] = $b; }
foreach ( ukv_open_barriers() as $b ) { if ( 'auto' === get_post_meta( $b, 'detected_by', true ) && 0 === strpos( (string) get_post_meta( $b, 'order_ref', true ), 'E2E-' ) ) { $barriers[] = $b; } }

// CHECK 3: client updates — one per affected order, correct guidance, journey audit on send.
echo "== Check 3: proactive client updates ==\n";
$d1 = ukv_draft_client_update( $bid, $eg1 );
e( false !== strpos( $d1['body'], 'e-visa portal' ) && false !== strpos( $d1['body'], 'E2E-EG-1' ), 'draft carries the barrier guidance + order ref' );
$before = count( (array) get_post_meta( $eg1, 'ukv_journey', true ) );
$sent = 0; foreach ( ukv_affected_orders( $bid ) as $oid ) { if ( ukv_send_client_update( $bid, $oid ) ) { $sent++; } }
e( 2 === $sent, 'one update sent per affected open order (2)' );
$after = count( (array) get_post_meta( $eg1, 'ukv_journey', true ) );
e( $after === $before + 1, 'send leaves a journey-note audit trail on the order' );

// CHECK 4: public content from a resolved barrier — no PII/competitor tokens, draft only.
echo "== Check 4: anonymised public content ==\n";
update_post_meta( $bid, 'status', 'resolved' );
$cid = ukv_generate_story_draft( $bid );
$posts[] = $cid;
e( $cid > 0, 'content draft generated' );
e( 'draft' === get_post_status( $cid ), 'content is DRAFT, never auto-published' );
e( [] === ukv_story_has_leak( get_post( $cid )->post_content ), 'content passes the leak gate (no PII / competitor tokens)' );

// CHECK 5: closing the barrier removes it from all affected orders' live surface.
echo "== Check 5: close removes from surface ==\n";
e( ! in_array( $bid, ukv_barriers_for_order( $eg1 ), true ) && ! in_array( $bid, ukv_barriers_for_order( $eg2 ), true ), 'resolved barrier no longer surfaces on any affected order' );

// CHECK 6: consented testimonial tier honours the consent gate.
echo "== Check 6: consent gate ==\n";
e( 0 === ukv_generate_testimonial_draft( $egDone ), 'no consent -> no testimonial' );
ukv_set_story_consent( $egDone, true );
$tid = ukv_generate_testimonial_draft( $egDone );
$posts[] = $tid;
e( $tid > 0 && 'draft' === get_post_status( $tid ), 'with consent -> draft testimonial created' );
e( [] === ukv_story_has_leak( get_post( $tid )->post_content, [ 'name' => 'Carol C', 'email' => 'carol@x.com' ] ), 'testimonial is leak-clean' );

// Cleanup
foreach ( array_unique( $posts ) as $p ) { if ( $p ) { wp_delete_post( $p, true ); } }
foreach ( array_unique( $barriers ) as $b ) { wp_delete_post( $b, true ); }
foreach ( array_unique( $orders ) as $o ) { wp_delete_post( $o, true ); }
echo $GLOBALS['ef'] ? "\nRESULT: FAILURES PRESENT\n" : "\nRESULT: ALL PASS\n";
