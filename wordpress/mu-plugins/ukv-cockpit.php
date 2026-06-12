<?php
/**
 * Plugin Name: UKV Ops Cockpit
 * Desc: One unified admin page (KPIs + open-orders worklist + barriers + success intelligence).
 *       READ-ONLY surface — reuses ukv-orders / ukv-barriers / ukv-insights helpers (no copies, no writes).
 */
defined( 'ABSPATH' ) || exit;

/**
 * Is an open order past its SLA due time?
 * SLA due = get_post_time('U',true,$id) + ukv_order_sla_hours(tier)*3600.
 * Only meaningful for OPEN orders (callers gate on status).
 */
function ukv_cockpit_is_breached( $order_id ) {
	$status = (string) get_post_meta( $order_id, 'ukv_status', true );
	if ( in_array( $status, UKV_ORDER_CLOSED, true ) ) { return false; }
	$hours = function_exists( 'ukv_order_sla_hours' )
		? ukv_order_sla_hours( get_post_meta( $order_id, 'ukv_tier', true ) )
		: 72;
	$due = (int) get_post_time( 'U', true, $order_id ) + $hours * 3600;
	return time() > $due;
}

/**
 * Top-line KPIs.
 * @return array{open_orders:int,revenue_mtd:float,sla_breaches:int,high_risk:int}
 */
function ukv_cockpit_kpis() {
	$ids = get_posts( [ 'post_type' => 'ukv_order', 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => -1 ] );

	$open        = 0;
	$breaches    = 0;
	$revenue_mtd = 0.0;

	$month_start = (int) mktime( 0, 0, 0, (int) gmdate( 'n' ), 1, (int) gmdate( 'Y' ) );

	foreach ( $ids as $oid ) {
		$status = (string) get_post_meta( $oid, 'ukv_status', true );

		if ( ! in_array( $status, UKV_ORDER_CLOSED, true ) ) {
			$open++;
			if ( ukv_cockpit_is_breached( $oid ) ) { $breaches++; }
		}

		$created = (int) get_post_meta( $oid, 'ukv_created', true );
		if ( $created >= $month_start ) {
			$revenue_mtd += (float) get_post_meta( $oid, 'ukv_total', true );
		}
	}

	$stats     = function_exists( 'ukv_success_stats' ) ? ukv_success_stats() : [];
	$high_risk = isset( $stats['high_risk_open'] ) ? (int) $stats['high_risk_open'] : 0;

	return [
		'open_orders'  => (int) $open,
		'revenue_mtd'  => (float) $revenue_mtd,
		'sla_breaches' => (int) $breaches,
		'high_risk'    => (int) $high_risk,
	];
}

/**
 * Open-orders worklist, SLA-breaches + high-risk first.
 * @return array<int,array{id:int,ref:string,name:string,destination:string,tier:string,status_label:string,sla:string,risk:bool}>
 */
function ukv_cockpit_open_orders() {
	$ids = get_posts( [ 'post_type' => 'ukv_order', 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => -1 ] );

	$rows = [];
	foreach ( $ids as $oid ) {
		$status = (string) get_post_meta( $oid, 'ukv_status', true );
		if ( in_array( $status, UKV_ORDER_CLOSED, true ) ) { continue; }

		$breached = ukv_cockpit_is_breached( $oid );
		$hours    = function_exists( 'ukv_order_sla_hours' )
			? ukv_order_sla_hours( get_post_meta( $oid, 'ukv_tier', true ) )
			: 72;
		$due      = (int) get_post_time( 'U', true, $oid ) + $hours * 3600;
		if ( $breached ) {
			$sla = 'breach';
		} elseif ( time() > $due - 6 * 3600 ) {
			$sla = 'due';
		} else {
			$sla = 'ok';
		}

		$risk = '1' === (string) get_post_meta( $oid, 'ukv_risk_flag', true );

		$rows[] = [
			'id'           => (int) $oid,
			'ref'          => (string) get_post_meta( $oid, 'ukv_order_ref', true ),
			'name'         => (string) get_post_meta( $oid, 'ukv_name', true ),
			'destination'  => (string) get_post_meta( $oid, 'ukv_destination', true ),
			'tier'         => (string) get_post_meta( $oid, 'ukv_tier', true ),
			'status_label' => UKV_ORDER_STATUSES[ $status ] ?? $status,
			'sla'          => $sla,
			'risk'         => (bool) $risk,
		];
	}

	// Sort: breaches first, then risk, then nearest SLA pill (breach>due>ok).
	$weight = [ 'breach' => 0, 'due' => 1, 'ok' => 2 ];
	usort( $rows, function ( $a, $b ) use ( $weight ) {
		$ab = ( 'breach' === $a['sla'] ) ? 0 : 1;
		$bb = ( 'breach' === $b['sla'] ) ? 0 : 1;
		if ( $ab !== $bb ) { return $ab <=> $bb; }
		if ( $a['risk'] !== $b['risk'] ) { return $a['risk'] ? -1 : 1; }
		return ( $weight[ $a['sla'] ] ?? 3 ) <=> ( $weight[ $b['sla'] ] ?? 3 );
	} );

	return $rows;
}

/** Small coloured pill helper (returns escaped HTML). */
function ukv_cockpit_pill( $text, $bg, $fg ) {
	return '<span style="display:inline-block;padding:1px 8px;border-radius:9px;font-size:11px;background:'
		. esc_attr( $bg ) . ';color:' . esc_attr( $fg ) . '">' . esc_html( $text ) . '</span>';
}

/** Register the top-level admin page. */
add_action( 'admin_menu', function () {
	add_menu_page(
		'UKV Cockpit',
		'UKV Cockpit',
		'edit_posts',
		'ukv-cockpit',
		'ukv_cockpit_render',
		'dashicons-dashboard',
		2
	);
} );

/** Render the whole cockpit. READ-ONLY; all output escaped; no fatal on empty data. */
function ukv_cockpit_render() {
	$k = ukv_cockpit_kpis();

	echo '<div class="wrap"><h1>UKV Ops Cockpit</h1>';

	// --- KPI strip (4 cards) ---
	$cards = [
		[ 'Open orders', number_format_i18n( $k['open_orders'] ), '#2271b1' ],
		[ 'Revenue (MTD)', '£' . number_format( $k['revenue_mtd'], 2 ), '#0f7b3f' ],
		[ 'SLA breaches', number_format_i18n( $k['sla_breaches'] ), $k['sla_breaches'] ? '#c00' : '#50575e' ],
		[ 'High-risk open', number_format_i18n( $k['high_risk'] ), $k['high_risk'] ? '#996800' : '#50575e' ],
	];
	echo '<div style="display:flex;gap:16px;flex-wrap:wrap;margin:16px 0">';
	foreach ( $cards as $c ) {
		echo '<div style="flex:1;min-width:160px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:14px 16px">'
			. '<div style="font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#646970">' . esc_html( $c[0] ) . '</div>'
			. '<div style="font-size:28px;font-weight:700;color:' . esc_attr( $c[2] ) . '">' . esc_html( $c[1] ) . '</div>'
			. '</div>';
	}
	echo '</div>';

	// --- Open-orders worklist ---
	echo '<h2>Open orders</h2>';
	$rows = ukv_cockpit_open_orders();
	if ( ! $rows ) {
		echo '<p>No open orders.</p>';
	} else {
		echo '<table class="widefat striped"><thead><tr>'
			. '<th>Order</th><th>Customer</th><th>Destination</th><th>Tier</th><th>Status</th><th>SLA</th><th>Risk</th><th>Docs</th>'
			. '</tr></thead><tbody>';
		foreach ( $rows as $r ) {
			$edit = (string) get_edit_post_link( $r['id'] );

			switch ( $r['sla'] ) {
				case 'breach': $sla_pill = ukv_cockpit_pill( 'Breached', '#fbe5e5', '#c00' ); break;
				case 'due':    $sla_pill = ukv_cockpit_pill( 'Due soon', '#fcf0d6', '#996800' ); break;
				default:       $sla_pill = ukv_cockpit_pill( 'On track', '#d6f0df', '#0f7b3f' );
			}
			$risk_pill = $r['risk'] ? ukv_cockpit_pill( 'High risk', '#fbe5e5', '#c00' ) : '&mdash;';
			$docs      = function_exists( 'ukv_doc_review_badge' ) ? ukv_doc_review_badge( $r['id'] ) : '&mdash;';

			echo '<tr>';
			echo '<td><a href="' . esc_url( $edit ) . '"><strong>' . esc_html( $r['ref'] ?: ( '#' . $r['id'] ) ) . '</strong></a></td>';
			echo '<td>' . esc_html( $r['name'] ) . '</td>';
			echo '<td>' . esc_html( $r['destination'] ) . '</td>';
			echo '<td>' . esc_html( ucfirst( $r['tier'] ) ) . '</td>';
			echo '<td>' . esc_html( $r['status_label'] ) . '</td>';
			echo '<td>' . $sla_pill . '</td>'; // pill already escaped
			echo '<td>' . $risk_pill . '</td>'; // pill already escaped
			echo '<td>' . wp_kses_post( $docs ) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}

	// --- Open barriers by destination ---
	echo '<h2>Open barriers by destination</h2>';
	$by = [];
	if ( function_exists( 'ukv_open_barriers' ) ) {
		foreach ( ukv_open_barriers() as $bid ) {
			$slug = ( function_exists( 'ukv_dest_slug' ) ? ukv_dest_slug( get_post_meta( $bid, 'destination', true ) ) : (string) get_post_meta( $bid, 'destination', true ) );
			$slug = $slug ?: '(unspecified)';
			$by[ $slug ] = ( $by[ $slug ] ?? 0 ) + 1;
		}
	}
	if ( ! $by ) {
		echo '<p>No open barriers.</p>';
	} else {
		arsort( $by );
		echo '<ul style="margin-left:1em">';
		foreach ( $by as $slug => $n ) {
			echo '<li><strong>' . esc_html( ucwords( str_replace( '-', ' ', $slug ) ) ) . '</strong>: ' . (int) $n . ' open</li>';
		}
		echo '</ul>';
	}

	// --- Success intelligence ---
	echo '<h2>Success intelligence</h2>';
	if ( function_exists( 'ukv_success_stats' ) ) {
		$s = ukv_success_stats();
		echo '<p><strong>Overall success rate:</strong> ' . esc_html( number_format( ( $s['overall']['rate'] ?? 0 ) * 100, 1 ) ) . '% '
			. '<span style="color:#646970">(' . (int) ( $s['overall']['success'] ?? 0 ) . ' won/delivered, '
			. (int) ( $s['overall']['fail'] ?? 0 ) . ' rejected/refunded)</span></p>';
		echo '<p><strong>Avg processing time:</strong> ' . esc_html( number_format( $s['avg_processing_days'] ?? 0, 1 ) ) . ' days</p>';

		$rej = [];
		foreach ( (array) ( $s['by_destination'] ?? [] ) as $slug => $row ) {
			if ( '' === $slug ) { continue; }
			$rej[ $slug ] = (float) ( $row['rejection_rate'] ?? 0 );
		}
		arsort( $rej );
		$rej = array_slice( $rej, 0, 5, true );
		if ( $rej ) {
			echo '<p style="margin-bottom:2px"><strong>Top destinations by refusal rate</strong></p><ul style="margin-left:1em">';
			foreach ( $rej as $slug => $rate ) {
				echo '<li>' . esc_html( ucwords( str_replace( '-', ' ', $slug ) ) ) . ': ' . esc_html( number_format( $rate * 100, 1 ) ) . '%</li>';
			}
			echo '</ul>';
		} else {
			echo '<p><em>No resolved cases yet.</em></p>';
		}
	} else {
		echo '<p><em>Success stats unavailable.</em></p>';
	}

	echo '</div>';
}
