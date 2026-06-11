<?php
// P11 self-test: fan-out, idempotency, live surface, close. Run: wp eval-file automation/test-barriers.php
// Creates throwaway orders + barriers, asserts, then deletes them all.
function t_assert( $cond, $msg ) { echo ( $cond ? 'PASS' : 'FAIL' ) . " — $msg\n"; if ( ! $cond ) { $GLOBALS['t_fail'] = true; } }
$GLOBALS['t_fail'] = false;
$created = [];

function t_order( $ref, $destName, $status, $tier = 'Standard', $days_old = 0, &$created ) {
	$oid = ukv_create_order( [ 'order_ref' => $ref, 'name' => 'Test', 'email' => 't@e.com', 'destination' => $destName, 'tier' => $tier, 'total' => 49 ] );
	update_post_meta( $oid, 'ukv_status', $status );
	if ( $days_old ) { wp_update_post( [ 'ID' => $oid, 'post_date' => gmdate( 'Y-m-d H:i:s', time() - $days_old * 86400 ), 'post_date_gmt' => gmdate( 'Y-m-d H:i:s', time() - $days_old * 86400 ) ] ); }
	$created[] = $oid;
	return $oid;
}

// 2 open Egypt, 1 won Egypt (resolved -> excluded from fan-out), 1 open Turkey.
$eg1 = t_order( 'T-EG-1', 'Egypt', 'paid', 'Standard', 0, $created );
$eg2 = t_order( 'T-EG-2', 'Egypt', 'awaiting_docs', 'Standard', 0, $created );
$egW = t_order( 'T-EG-W', 'Egypt', 'won', 'Standard', 0, $created );
$tk1 = t_order( 'T-TK-1', 'Turkey', 'paid', 'Standard', 0, $created );

// 1) Destination barrier (egypt) fan-out = only the 2 OPEN egypt orders.
$bid = ukv_barrier_create( [ 'nature' => 'temporary', 'scope' => 'destination', 'destination' => 'egypt', 'guidance' => 'Egypt portal down 48h.' ] );
$created_b = [ $bid ];
$aff = ukv_affected_orders( $bid );
sort( $aff ); $exp = [ $eg1, $eg2 ]; sort( $exp );
t_assert( $aff === $exp, 'destination barrier fans out to exactly the 2 open Egypt orders (not won, not Turkey)' );

// 2) Live surface: open egypt order sees it; turkey order does not. Zero duplication (1 record).
t_assert( in_array( $bid, ukv_barriers_for_order( $eg1 ), true ), 'open Egypt order surfaces the destination barrier' );
t_assert( ! in_array( $bid, ukv_barriers_for_order( $tk1 ), true ), 'Turkey order does NOT surface the Egypt barrier' );
$all_eg_barriers = get_posts( [ 'post_type' => 'ukv_barrier', 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => -1, 'meta_query' => [ [ 'key' => 'destination', 'value' => 'egypt' ] ] ] );
t_assert( count( $all_eg_barriers ) === 1, 'single stored record for the destination barrier (no copies onto orders)' );

// 3) Auto-detect idempotency: SLA-breached order (4 days old, 72h SLA) -> exactly 1 sla barrier after 2 runs.
$slaO = t_order( 'T-SLA-1', 'Egypt', 'paid', 'Standard', 4, $created );
ukv_auto_detect_barriers();
ukv_auto_detect_barriers();
$sla = get_posts( [ 'post_type' => 'ukv_barrier', 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => -1, 'meta_query' => [ [ 'key' => 'rule_key', 'value' => 'T-SLA-1:sla_breach' ] ] ] );
t_assert( count( $sla ) === 1, 'auto-detect is idempotent — 2 cron runs create exactly 1 SLA barrier' );
foreach ( $sla as $s ) { $created_b[] = $s; }
// sweep any other auto barriers created for our test refs
foreach ( ukv_open_barriers() as $b ) { if ( 'auto' === get_post_meta( $b, 'detected_by', true ) && 0 === strpos( (string) get_post_meta( $b, 'order_ref', true ), 'T-' ) ) { $created_b[] = $b; } }

// 4) Close the destination barrier -> disappears from all affected orders' live surface.
update_post_meta( $bid, 'status', 'resolved' );
t_assert( ! in_array( $bid, ukv_barriers_for_order( $eg1 ), true ), 'closing the barrier removes it from the order live surface' );

// Cleanup
foreach ( array_unique( $created_b ) as $b ) { wp_delete_post( $b, true ); }
foreach ( array_unique( $created ) as $o ) { wp_delete_post( $o, true ); }
echo $GLOBALS['t_fail'] ? "\nRESULT: FAILURES PRESENT\n" : "\nRESULT: ALL PASS\n";
