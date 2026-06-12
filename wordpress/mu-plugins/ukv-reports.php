<?php
/**
 * Plugin Name: UKV Reports
 * Desc: Admin Reports page (revenue, status counts, top refusal destinations, avg
 *       processing days, total orders) + nonce-gated CSV export of every order.
 *       Reuses ukv-orders + ukv-insights helpers (no copies).
 */
defined( 'ABSPATH' ) || exit;

/**
 * Headline numbers for the Reports page.
 * Returns:
 *   revenue_mtd         => float (sum ukv_total for orders created this calendar month)
 *   revenue_all         => float (sum ukv_total across all orders)
 *   by_status           => [ status => count ]
 *   top_rejection       => [ [ 'dest' => slug, 'rate' => float ], ... up to 5 ] desc
 *   avg_processing_days => float (from ukv_success_stats)
 *   orders_total        => int
 */
function ukv_report_summary() {
	$ids = get_posts( [ 'post_type' => 'ukv_order', 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => -1 ] );

	$revenue_all = 0.0;
	$revenue_mtd = 0.0;
	$by_status   = [];
	$month_start = (int) gmmktime( 0, 0, 0, (int) gmdate( 'n' ), 1, (int) gmdate( 'Y' ) );

	foreach ( $ids as $oid ) {
		$total  = (float) get_post_meta( $oid, 'ukv_total', true );
		$status = (string) get_post_meta( $oid, 'ukv_status', true );
		if ( '' === $status ) { $status = 'paid'; }

		$revenue_all          += $total;
		$by_status[ $status ]  = ( $by_status[ $status ] ?? 0 ) + 1;

		$created = (int) get_post_meta( $oid, 'ukv_created', true );
		if ( $created >= $month_start ) { $revenue_mtd += $total; }
	}

	// Top rejection destinations from the insights helper (sorted by rejection_rate desc).
	$top = [];
	if ( function_exists( 'ukv_success_stats' ) ) {
		$stats = ukv_success_stats();
		foreach ( ( $stats['by_destination'] ?? [] ) as $slug => $row ) {
			if ( '' === $slug ) { continue; }
			$top[] = [ 'dest' => $slug, 'rate' => (float) ( $row['rejection_rate'] ?? 0.0 ) ];
		}
		usort( $top, static function ( $a, $b ) { return $b['rate'] <=> $a['rate']; } );
		$top = array_slice( $top, 0, 5 );
	}

	$avg = ( function_exists( 'ukv_success_stats' ) && isset( $stats['avg_processing_days'] ) )
		? (float) $stats['avg_processing_days'] : 0.0;

	return [
		'revenue_mtd'         => $revenue_mtd,
		'revenue_all'         => $revenue_all,
		'by_status'           => $by_status,
		'top_rejection'       => $top,
		'avg_processing_days' => $avg,
		'orders_total'        => count( $ids ),
	];
}

/**
 * CSV as a 2D array: header row + one row per order.
 * Columns: ref, name, email, destination, tier, status_label, total, created(Y-m-d).
 */
function ukv_report_csv_rows() {
	$rows = [ [ 'ref', 'name', 'email', 'destination', 'tier', 'status_label', 'total', 'created' ] ];

	$ids = get_posts( [ 'post_type' => 'ukv_order', 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => -1, 'orderby' => 'date', 'order' => 'DESC' ] );
	foreach ( $ids as $oid ) {
		$g       = static function ( $k ) use ( $oid ) { return (string) get_post_meta( $oid, $k, true ); };
		$status  = $g( 'ukv_status' ) ?: 'paid';
		$label   = ( defined( 'UKV_ORDER_STATUSES' ) && isset( UKV_ORDER_STATUSES[ $status ] ) ) ? UKV_ORDER_STATUSES[ $status ] : $status;
		$created = (int) get_post_meta( $oid, 'ukv_created', true );

		$rows[] = [
			$g( 'ukv_order_ref' ),
			$g( 'ukv_name' ),
			$g( 'ukv_email' ),
			$g( 'ukv_destination' ),
			$g( 'ukv_tier' ),
			$label,
			$g( 'ukv_total' ),
			$created ? gmdate( 'Y-m-d', $created ) : '',
		];
	}
	return $rows;
}

/** Render the CSV rows as a CSV string (proper escaping/quoting via fputcsv). */
function ukv_report_csv_string() {
	$fh = fopen( 'php://temp', 'r+' );
	if ( false === $fh ) {
		// Extremely defensive fallback: should never happen.
		return implode( "\n", array_map( static function ( $r ) { return implode( ',', (array) $r ); }, ukv_report_csv_rows() ) );
	}
	foreach ( ukv_report_csv_rows() as $row ) {
		fputcsv( $fh, $row );
	}
	rewind( $fh );
	$out = stream_get_contents( $fh );
	fclose( $fh );
	return (string) $out;
}

/** Reports admin page under the Orders menu (capability: edit_posts). */
add_action( 'admin_menu', function () {
	add_submenu_page(
		'edit.php?post_type=ukv_order',
		'UKV Reports',
		'Reports',
		'edit_posts',
		'ukv-reports',
		'ukv_reports_page'
	);
} );

function ukv_reports_page() {
	if ( ! current_user_can( 'edit_posts' ) ) { wp_die( esc_html__( 'Insufficient permissions.', 'default' ) ); }
	$s = ukv_report_summary();

	echo '<div class="wrap"><h1>UKV Reports</h1>';

	// Headline cards.
	echo '<p style="font-size:14px">';
	echo '<strong>Revenue (this month):</strong> £' . esc_html( number_format( (float) $s['revenue_mtd'], 2 ) ) . ' &nbsp;&middot;&nbsp; ';
	echo '<strong>Revenue (all time):</strong> £' . esc_html( number_format( (float) $s['revenue_all'], 2 ) ) . ' &nbsp;&middot;&nbsp; ';
	echo '<strong>Total orders:</strong> ' . (int) $s['orders_total'] . ' &nbsp;&middot;&nbsp; ';
	echo '<strong>Avg processing:</strong> ' . esc_html( number_format( (float) $s['avg_processing_days'], 1 ) ) . ' days';
	echo '</p>';

	// Counts by status.
	echo '<h2>Orders by status</h2>';
	echo '<table class="widefat striped" style="max-width:480px"><thead><tr><th>Status</th><th>Count</th></tr></thead><tbody>';
	$labels = defined( 'UKV_ORDER_STATUSES' ) ? UKV_ORDER_STATUSES : [];
	$shown  = [];
	foreach ( $labels as $key => $label ) {
		$shown[ $key ] = true;
		echo '<tr><td>' . esc_html( $label ) . '</td><td>' . (int) ( $s['by_status'][ $key ] ?? 0 ) . '</td></tr>';
	}
	// Any status not in the canonical map (defensive).
	foreach ( $s['by_status'] as $key => $count ) {
		if ( isset( $shown[ $key ] ) ) { continue; }
		echo '<tr><td>' . esc_html( $key ) . '</td><td>' . (int) $count . '</td></tr>';
	}
	echo '</tbody></table>';

	// Top rejection destinations.
	echo '<h2>Top refusal destinations</h2>';
	if ( ! empty( $s['top_rejection'] ) ) {
		echo '<table class="widefat striped" style="max-width:480px"><thead><tr><th>Destination</th><th>Refusal rate</th></tr></thead><tbody>';
		foreach ( $s['top_rejection'] as $r ) {
			$name = ucwords( str_replace( '-', ' ', (string) $r['dest'] ) );
			echo '<tr><td>' . esc_html( $name ) . '</td><td>' . esc_html( number_format( (float) $r['rate'] * 100, 1 ) ) . '%</td></tr>';
		}
		echo '</tbody></table>';
	} else {
		echo '<p><em>No resolved cases yet.</em></p>';
	}

	// Download CSV (nonce-protected admin-post form).
	echo '<h2>Export</h2>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'ukv_report_csv', 'ukv_report_csv_nonce' );
	echo '<input type="hidden" name="action" value="ukv_report_csv">';
	echo '<button type="submit" class="button button-primary">Download CSV</button>';
	echo '</form>';

	echo '</div>';
}

/** Stream the CSV export. Nonce + capability gated. */
add_action( 'admin_post_ukv_report_csv', function () {
	if ( ! isset( $_POST['ukv_report_csv_nonce'] ) || ! wp_verify_nonce( $_POST['ukv_report_csv_nonce'], 'ukv_report_csv' ) ) {
		wp_die( 'Invalid request.' );
	}
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( 'Not allowed.' );
	}

	$filename = 'ukv-orders-' . gmdate( 'Y-m-d' ) . '.csv';
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	echo ukv_report_csv_string();
	exit;
} );
