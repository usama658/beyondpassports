<?php
/**
 * Test: UKV Reports (admin Reports page + CSV export).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-reports.php"
 */

$pass = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_report_summary' ), 'ukv_report_summary() is defined' );
$check( function_exists( 'ukv_report_csv_rows' ), 'ukv_report_csv_rows() is defined' );
$check( function_exists( 'ukv_report_csv_string' ), 'ukv_report_csv_string() is defined' );
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );

// 1. Seed 3 isolated orders with known totals + varied statuses (incl 1 rejected).
$created = [];
$ref_a   = 'UKV-RPT-' . substr( md5( uniqid( '', true ) ), 0, 8 );
$ref_b   = 'UKV-RPT-' . substr( md5( uniqid( '', true ) ), 0, 8 );
$ref_c   = 'UKV-RPT-' . substr( md5( uniqid( '', true ) ), 0, 8 );

$seed = [
	[ 'order_ref' => $ref_a, 'name' => 'Report Test A', 'email' => 'rpt.a@example.com', 'destination' => 'Egypt',    'tier' => 'Standard', 'total' => 49, 'status' => 'paid' ],
	[ 'order_ref' => $ref_b, 'name' => 'Report Test B', 'email' => 'rpt.b@example.com', 'destination' => 'Thailand', 'tier' => 'Express',  'total' => 79, 'status' => 'won' ],
	[ 'order_ref' => $ref_c, 'name' => 'Report Test C', 'email' => 'rpt.c@example.com', 'destination' => 'Kenya',    'tier' => 'Standard', 'total' => 25, 'status' => 'rejected' ],
];
foreach ( $seed as $s ) {
	$oid = ukv_create_order( $s );
	$created[] = $oid;
	update_post_meta( $oid, 'ukv_status', $s['status'] );
}

// 2. ukv_report_summary() assertions.
$sum = ukv_report_summary();
$check( is_array( $sum ), 'ukv_report_summary() returns an array' );
$check( isset( $sum['revenue_all'] ) && $sum['revenue_all'] >= 153, 'revenue_all >= 153 (got ' . ( $sum['revenue_all'] ?? 'n/a' ) . ')' );
$check( isset( $sum['revenue_mtd'] ) && is_numeric( $sum['revenue_mtd'] ), 'revenue_mtd is numeric (got ' . ( $sum['revenue_mtd'] ?? 'n/a' ) . ')' );
$check( isset( $sum['by_status'] ) && is_array( $sum['by_status'] ), 'by_status is an array' );
$check( ( $sum['by_status']['paid'] ?? 0 ) >= 1, 'by_status[paid] >= 1 (got ' . ( $sum['by_status']['paid'] ?? 0 ) . ')' );
$check( ( $sum['by_status']['won'] ?? 0 ) >= 1, 'by_status[won] >= 1 (got ' . ( $sum['by_status']['won'] ?? 0 ) . ')' );
$check( ( $sum['by_status']['rejected'] ?? 0 ) >= 1, 'by_status[rejected] >= 1 (got ' . ( $sum['by_status']['rejected'] ?? 0 ) . ')' );
$check( isset( $sum['orders_total'] ) && $sum['orders_total'] >= 3, 'orders_total >= 3 (got ' . ( $sum['orders_total'] ?? 'n/a' ) . ')' );
$check( isset( $sum['avg_processing_days'] ) && is_numeric( $sum['avg_processing_days'] ), 'avg_processing_days is numeric (got ' . ( $sum['avg_processing_days'] ?? 'n/a' ) . ')' );
$check( isset( $sum['top_rejection'] ) && is_array( $sum['top_rejection'] ) && count( $sum['top_rejection'] ) <= 5, 'top_rejection is an array of <=5 (got ' . count( (array) ( $sum['top_rejection'] ?? [] ) ) . ')' );

// 3. ukv_report_csv_rows() + ukv_report_csv_string().
$rows = ukv_report_csv_rows();
$check( is_array( $rows ) && ! empty( $rows ), 'ukv_report_csv_rows() returns a non-empty array' );
$header = $rows[0] ?? [];
$check( is_array( $header ) && in_array( 'ref', array_map( 'strtolower', $header ), true ), 'first row is the header (contains "ref")' );

$found_ref = false;
foreach ( array_slice( $rows, 1 ) as $r ) {
	if ( isset( $r[0] ) && $r[0] === $ref_a ) { $found_ref = true; break; }
}
$check( $found_ref, "a data row has ref === {$ref_a}" );

$csv = ukv_report_csv_string();
$check( is_string( $csv ) && strpos( $csv, $ref_a ) !== false, "csv string contains {$ref_a}" );
$check( strpos( $csv, ',' ) !== false, 'csv string contains a comma' );

// 4. Clean up created orders.
$n = 0;
foreach ( $created as $oid ) { if ( $oid ) { wp_delete_post( $oid, true ); $n++; } }
echo "INFO — cleaned up {$n} created order(s)\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
