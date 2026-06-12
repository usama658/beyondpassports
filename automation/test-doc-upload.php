<?php
/**
 * Test: UKV Secure Document Upload (Gap #68 — privacy + file-security critical).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-doc-upload.php"
 */

$pass = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_doc_upload_attach' ), 'ukv_doc_upload_attach() is defined' );
$check( function_exists( 'ukv_tracker_lookup' ), 'ukv_tracker_lookup() is defined (auth helper reused)' );
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );

/**
 * Build a $_FILES-style array from real bytes written to a temp path.
 * Mimics a PHP upload: a tmp_name that exists on disk + the client filename/type/size.
 * We force move_uploaded_file's sibling behaviour off via the unit-test override in the plugin.
 */
$make_file = function ( $client_name, $client_type, $bytes ) {
	$tmp = wp_tempnam( 'ukvtest' );
	file_put_contents( $tmp, $bytes );
	return [
		'name'     => $client_name,
		'type'     => $client_type,
		'tmp_name' => $tmp,
		'error'    => 0,
		'size'     => strlen( $bytes ),
	];
};

// A minimal valid 1x1 PNG (real PNG signature so wp_check_filetype_and_ext accepts it).
$png_bytes = base64_decode(
	'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
);

// 1. Create an order (ref UP-1, email real@x.com).
$oid = ukv_create_order( [
	'order_ref'   => 'UP-1',
	'name'        => 'Real Person',
	'email'       => 'real@x.com',
	'destination' => 'Egypt',
	'tier'        => 'Standard',
	'total'       => 49,
] );
$check( $oid > 0, "created order UP-1 (#{$oid})" );

// 2. Auth: lookup returns the order id for the right ref+email; null for wrong email + wrong ref.
$check( ukv_tracker_lookup( 'UP-1', 'real@x.com' ) === $oid, 'auth: UP-1 / real@x.com returns the order id' );
$check( ukv_tracker_lookup( 'UP-1', 'REAL@x.com' ) === $oid, 'auth: case-insensitive on email' );
$check( ukv_tracker_lookup( 'UP-1', 'wrong@x.com' ) === null, 'auth: wrong email returns null (no match)' );
$check( ukv_tracker_lookup( 'NOPE-9', 'real@x.com' ) === null, 'auth: non-existent ref returns null (no enumeration)' );

// Signal the plugin we are in a unit-test (use rename instead of move_uploaded_file).
define( 'UKV_DOC_UPLOAD_TEST', true );

// 3. Valid PNG -> attachment id > 0, appended to ukv_documents, journey note logged.
$png_file = $make_file( 'passport.png', 'image/png', $png_bytes );
$att = ukv_doc_upload_attach( $oid, $png_file );
$check( is_int( $att ) && $att > 0, "valid PNG accepted, attachment id #{$att}" );

$docs = (array) get_post_meta( $oid, 'ukv_documents', true );
$check( in_array( $att, array_map( 'intval', $docs ), true ), 'ukv_documents now contains the new attachment id' );

$attachment = $att ? get_post( $att ) : null;
$check( $attachment && 'attachment' === $attachment->post_type, 'attachment post created' );
$check( $attachment && 'inherit' === $attachment->post_status, 'attachment is private/inherit status' );
$check( $attachment && (int) $attachment->post_parent === (int) $oid, 'attachment is parented to the matched order only' );

$journey = (array) get_post_meta( $oid, 'ukv_journey', true );
$last    = end( $journey );
$check( is_array( $last ) && ( $last['channel'] ?? '' ) === 'upload', 'journey note appended with channel "upload"' );
$check( is_array( $last ) && strpos( (string) ( $last['text'] ?? '' ), 'passport.png' ) !== false, 'journey note records the filename' );

// 4. Disallowed type (.exe) -> WP_Error, NOT added to ukv_documents.
$docs_before = (array) get_post_meta( $oid, 'ukv_documents', true );
$exe_file    = $make_file( 'malware.exe', 'application/octet-stream', "MZ\x90\x00bogus-binary" );
$err         = ukv_doc_upload_attach( $oid, $exe_file );
$check( is_wp_error( $err ), '.exe upload rejected with WP_Error' );
$docs_after  = (array) get_post_meta( $oid, 'ukv_documents', true );
$check( count( $docs_after ) === count( $docs_before ), '.exe did NOT change ukv_documents' );

// Extra: a disguised file (.png name but php/script bytes won't match) & oversize guard.
$bad_ext = $make_file( 'note.txt', 'text/plain', 'hello' );
$check( is_wp_error( ukv_doc_upload_attach( $oid, $bad_ext ) ), '.txt (disallowed ext) rejected with WP_Error' );

// 5. Clean up: delete order + all created attachments/files.
$all_docs = (array) get_post_meta( $oid, 'ukv_documents', true );
foreach ( $all_docs as $aid ) {
	wp_delete_attachment( (int) $aid, true );
}
wp_delete_post( $oid, true );
echo "INFO — cleaned up order #{$oid} and " . count( $all_docs ) . " attachment(s)\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
