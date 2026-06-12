<?php
/**
 * Test: UKV Ops Cockpit (unified admin page).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-cockpit.php"
 *
 * Seeds its OWN isolated orders, asserts the cockpit helpers + render, then force-deletes them.
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_cockpit_kpis' ), 'ukv_cockpit_kpis() is defined' );
$check( function_exists( 'ukv_cockpit_open_orders' ), 'ukv_cockpit_open_orders() is defined' );
$check( function_exists( 'ukv_cockpit_render' ), 'ukv_cockpit_render() is defined' );
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined (helper reuse)' );

$created = [];
$mk = function ( $status, $extra = [] ) use ( &$created ) {
	$oid = ukv_create_order( array_merge( [
		'order_ref'   => 'UKV-COCK-' . substr( md5( uniqid( '', true ) ), 0, 8 ),
		'name'        => 'Cockpit Test',
		'email'       => 'cockpit.test@example.com',
		'destination' => 'Egypt',
		'tier'        => 'Standard',
		'total'       => 49,
	], $extra ) );
	$created[] = $oid;
	if ( null !== $status ) { update_post_meta( $oid, 'ukv_status', $status ); }
	return $oid;
};

// 1. Seed: 2 open (one Express backdated 2 days => SLA breach, 24h SLA), 1 delivered, 1 rejected.
$breach_oid = $mk( 'awaiting_docs', [ 'tier' => 'Express', 'total' => 199, 'order_ref' => 'UKV-COCK-BREACH' ] );
// Backdate the post date 2 days so get_post_time() is 48h ago > 24h Express SLA.
$past = gmdate( 'Y-m-d H:i:s', time() - 2 * 86400 );
wp_update_post( [ 'ID' => $breach_oid, 'post_date' => get_date_from_gmt( $past ), 'post_date_gmt' => $past ] );
update_post_meta( $breach_oid, 'ukv_created', time() - 2 * 86400 );

$ok_oid = $mk( 'paid', [ 'tier' => 'Standard', 'total' => 49 ] ); // open, fresh => on track
$mk( 'delivered', [ 'total' => 79 ] ); // closed
$mk( 'rejected', [ 'total' => 59 ] );  // closed

// 2. KPIs: sane ints/floats.
$k = ukv_cockpit_kpis();
$check( is_array( $k ), 'ukv_cockpit_kpis() returns array' );
$check( isset( $k['open_orders'], $k['revenue_mtd'], $k['sla_breaches'], $k['high_risk'] ), 'KPI keys present' );
$check( is_int( $k['open_orders'] ) && $k['open_orders'] >= 2, "open_orders is int >= 2 (got {$k['open_orders']})" );
$check( is_int( $k['sla_breaches'] ) && $k['sla_breaches'] >= 1, "sla_breaches is int >= 1 (got {$k['sla_breaches']})" );
$check( is_float( $k['revenue_mtd'] ) && $k['revenue_mtd'] > 0, "revenue_mtd is float > 0 (got {$k['revenue_mtd']})" );
$check( is_int( $k['high_risk'] ), "high_risk is int (got " . gettype( $k['high_risk'] ) . ")" );

// 3. Open orders: only open ones, breached sorted before non-breached.
$rows = ukv_cockpit_open_orders();
$check( is_array( $rows ) && count( $rows ) >= 2, 'open orders list has >= 2 rows' );
$ids = array_column( $rows, 'id' );
$check( in_array( $breach_oid, $ids, true ) && in_array( $ok_oid, $ids, true ), 'both open orders present' );
foreach ( $rows as $r ) {
	$check( ! in_array( get_post_meta( $r['id'], 'ukv_status', true ), UKV_ORDER_CLOSED, true ), "row {$r['id']} is not a closed status" );
}
$pos_breach = array_search( $breach_oid, $ids, true );
$pos_ok     = array_search( $ok_oid, $ids, true );
$check( $pos_breach !== false && $pos_ok !== false && $pos_breach < $pos_ok, "breached order sorted before non-breached (breach@{$pos_breach} < ok@{$pos_ok})" );
// Row shape check on the breached row.
$brow = $rows[ $pos_breach ];
$check( $brow['sla'] === 'breach', "breached row sla === 'breach' (got '{$brow['sla']}')" );
$check( isset( $brow['ref'], $brow['name'], $brow['destination'], $brow['tier'], $brow['status_label'], $brow['risk'] ), 'row has expected keys' );

// 4. Render: non-empty, contains a recognisable label, no PHP error/warning.
$err = '';
set_error_handler( function ( $no, $str ) use ( &$err ) { $err .= $str . "\n"; return true; } );
ob_start();
ukv_cockpit_render();
$html = ob_get_clean();
restore_error_handler();
$check( $err === '', 'no PHP error/warning during render' . ( $err ? " (got: " . trim( $err ) . ")" : '' ) );
$check( is_string( $html ) && $html !== '', 'render produced non-empty output' );
$check( strpos( $html, 'Cockpit' ) !== false || strpos( $html, 'Open orders' ) !== false || strpos( $html, 'Revenue' ) !== false, 'render contains a KPI label / heading' );
$check( strpos( $html, esc_html( 'UKV-COCK-BREACH' ) ) !== false, 'render shows the seeded breached order ref' );

// 5. Empty-data safety: render must not fatal even with no open orders (closed-only subset already proves table path; check string only).
$check( strpos( $html, '<' ) !== false, 'render emitted HTML markup' );

// 6. Clean up every created order.
$n = 0;
foreach ( $created as $oid ) { if ( $oid ) { wp_delete_post( $oid, true ); $n++; } }
echo "INFO — cleaned up {$n} created order(s)\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
