<?php
/**
 * Test: UKV GDPR Document Retention + Auto-purge (Gap #71) — ukv-retention.php
 * Proves: configurable retention window, purge of past-retention closed orders only,
 *         force-deletes attachment files, never deletes the order, idempotency, journey note.
 * Run: cd /c/xampp/htdocs/ukvisa && /c/xampp/php/php.exe -d memory_limit=512M wp-cli.phar eval-file "<abs path>" 2>&1 | tail -40
 */
defined( 'ABSPATH' ) || exit;

$pass = 0;
$fail = 0;
$ok = static function ( bool $cond, string $msg ) use ( &$pass, &$fail ) {
	if ( $cond ) { $pass++; echo "PASS — {$msg}\n"; }
	else { $fail++; echo "FAIL — {$msg}\n"; }
};

/** Helper: create a real attachment from a tiny in-memory file. Returns [att_id, abs_path]. */
$make_attachment = static function ( string $name ) {
	$up = wp_upload_bits( $name, null, 'retention-test-bytes' );
	if ( ! empty( $up['error'] ) ) { return [ 0, '' ]; }
	$att_id = wp_insert_attachment( [
		'post_title'     => $name,
		'post_mime_type' => $up['type'] ?: 'text/plain',
		'post_status'    => 'inherit',
	], $up['file'] );
	return [ (int) $att_id, (string) $up['file'] ];
};

$created_orders = [];
$created_atts   = [];

/* -- Dependencies present. -- */
$ok( function_exists( 'ukv_retention_purge' ), 'ukv_retention_purge() is defined' );
$ok( function_exists( 'ukv_mark_closed_now' ), 'ukv_mark_closed_now() is defined' );
$ok( function_exists( 'ukv_create_order' ), 'ukv_create_order() factory dependency is loaded' );
$ok( 90 === (int) get_option( 'ukv_retention_days', 90 ), 'retention days default is 90' );

/* =========================================================================
 * 1) Old closed order (closed 100 days ago) WITH a real attachment.
 * ====================================================================== */
list( $att_old, $path_old ) = $make_attachment( 'retention-old.txt' );
$created_atts[] = $att_old;
$ok( $att_old > 0 && file_exists( $path_old ), 'created a real attachment for the old order (file on disk)' );

$old = ukv_create_order( [ 'name' => 'Old Closed', 'destination' => 'india', 'documents' => [ $att_old ] ] );
$created_orders[] = $old;
update_post_meta( $old, 'ukv_status', 'delivered' );
ukv_mark_closed_now( $old );
// Backdate the closure to 100 days ago (older than the 90-day window).
update_post_meta( $old, 'ukv_closed_at', time() - ( 100 * 86400 ) );
$ok( $old > 0 && (int) get_post_meta( $old, 'ukv_closed_at', true ) > 0, 'old order created + closed_at backdated 100 days' );
$ok( in_array( $att_old, (array) get_post_meta( $old, 'ukv_documents', true ), true ), 'old order has the attachment in ukv_documents' );

/* =========================================================================
 * 2) Recent closed order (closed 10 days ago) WITH a real attachment.
 *    Must NOT be purged (still inside the 90-day window).
 * ====================================================================== */
list( $att_new, $path_new ) = $make_attachment( 'retention-new.txt' );
$created_atts[] = $att_new;
$ok( $att_new > 0 && file_exists( $path_new ), 'created a real attachment for the recent order' );

$recent = ukv_create_order( [ 'name' => 'Recent Closed', 'destination' => 'egypt', 'documents' => [ $att_new ] ] );
$created_orders[] = $recent;
update_post_meta( $recent, 'ukv_status', 'won' );
ukv_mark_closed_now( $recent );
update_post_meta( $recent, 'ukv_closed_at', time() - ( 10 * 86400 ) );
$ok( $recent > 0 && (int) get_post_meta( $recent, 'ukv_closed_at', true ) > 0, 'recent order created + closed_at 10 days ago' );

/* =========================================================================
 * 3) Run the purge.
 * ====================================================================== */
$purged = ukv_retention_purge();
$ok( is_array( $purged ), 'ukv_retention_purge() returns an array' );

/* -- Old order: purged. -- */
$ok( in_array( $old, $purged, true ), 'purge result contains the OLD order id' );
$ok( null === get_post( $att_old ), 'old attachment post no longer exists (get_post is null)' );
$ok( ! file_exists( $path_old ), 'old attachment FILE force-deleted from disk' );
$ok( [] === (array) get_post_meta( $old, 'ukv_documents', true ), 'old order ukv_documents cleared to []' );
$ok( '1' === (string) get_post_meta( $old, 'ukv_docs_purged', true ), "old order ukv_docs_purged = '1'" );
$journey = (array) get_post_meta( $old, 'ukv_journey', true );
$last = $journey ? end( $journey ) : [];
$ok( is_array( $last ) && false !== strpos( (string) ( $last['text'] ?? '' ), 'purged per retention policy' ), 'old order got a journey purge note' );
$ok( is_array( $last ) && 'system' === ( $last['agent'] ?? '' ) && 'internal' === ( $last['channel'] ?? '' ), 'journey note is agent=system, channel=internal' );
$ok( null !== get_post( $old ), 'OLD ORDER ITSELF still exists (only docs purged, never the order)' );

/* -- Recent order: untouched. -- */
$ok( ! in_array( $recent, $purged, true ), 'purge result does NOT contain the RECENT order id' );
$ok( null !== get_post( $att_new ), 'recent attachment still exists' );
$ok( file_exists( $path_new ), 'recent attachment file still on disk' );
$ok( in_array( $att_new, (array) get_post_meta( $recent, 'ukv_documents', true ), true ), 'recent order still has its document' );
$ok( '' === (string) get_post_meta( $recent, 'ukv_docs_purged', true ), 'recent order NOT marked purged' );

/* =========================================================================
 * 4) Idempotency: a second run does not re-purge the old order.
 * ====================================================================== */
$purged2 = ukv_retention_purge();
$ok( is_array( $purged2 ) && ! in_array( $old, $purged2, true ), 'second purge run does NOT re-purge the old order (idempotent)' );

/* =========================================================================
 * Pending-count helper sanity (if exposed).
 * ====================================================================== */
if ( function_exists( 'ukv_retention_pending_count' ) ) {
	$ok( is_int( ukv_retention_pending_count() ), 'ukv_retention_pending_count() returns an int' );
}

/* =========================================================================
 * 5) Clean up: orders + any leftover attachments/files.
 * ====================================================================== */
foreach ( $created_orders as $oid ) {
	if ( $oid ) { wp_delete_post( $oid, true ); }
}
foreach ( $created_atts as $aid ) {
	if ( $aid && get_post( $aid ) ) { wp_delete_attachment( $aid, true ); }
}
// Belt-and-braces: remove any test files still on disk.
foreach ( [ $path_old, $path_new ] as $p ) {
	if ( $p && file_exists( $p ) ) { @unlink( $p ); }
}
$cleanup_ok = true;
foreach ( $created_orders as $oid ) { if ( $oid && null !== get_post( $oid ) ) { $cleanup_ok = false; } }
foreach ( [ $path_old, $path_new ] as $p ) { if ( $p && file_exists( $p ) ) { $cleanup_ok = false; } }
$ok( $cleanup_ok, 'cleanup: test orders + attachment files removed' );

echo "\n{$pass} passed, {$fail} failed\n";
echo ( 0 === $fail ) ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
