<?php
// Generate the supply-chain capture sheet for all destinations. Run: wp eval-file automation/gen-supply-capture.php
$dir = 'C:/Users/mumya/OneDrive/Desktop/Claude Projects/UK VIsa/exports';
if ( ! is_dir( $dir ) ) { wp_mkdir_p( $dir ); }
$fh = fopen( $dir . '/supply-chain-capture.csv', 'w' );
fputcsv( $fh, [ 'Country', 'Slug', 'Route', 'Application needed?', 'Needs a visa centre?', 'Official portal (VERIFY URL)', 'Passport handling', 'Notes / jurisdiction (research)' ] );

$ids = get_posts( [ 'post_type' => 'destination', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'orderby' => 'title', 'order' => 'ASC' ] );
foreach ( $ids as $id ) {
	$name = get_the_title( $id );
	$slug = get_post_field( 'post_name', $id );
	$type = function_exists( 'pods' ) ? (string) pods( 'destination', $id )->field( 'visa_type' ) : '';
	$t = strtolower( $type );
	if ( false !== strpos( $t, 'visa-free' ) || false !== strpos( $t, 'visa free' ) ) {
		$route = 'Visa-free'; $appNeeded = 'No — valid passport + entry rules only'; $portal = 'n/a';
		$notes = 'No application/processing. Check passport validity + entry conditions only.';
	} elseif ( false !== strpos( $t, 'evisa' ) ) {
		$route = 'eVisa (online)'; $appNeeded = 'Yes — apply online'; $portal = '[VERIFY official eVisa portal]';
		$notes = 'Online only. No centre. Deliver e-visa PDF by email.';
	} elseif ( false !== strpos( $t, 'eta' ) ) {
		$route = 'ETA / authorisation (online)'; $appNeeded = 'Yes — apply online'; $portal = '[VERIFY official ETA/ESTA portal]';
		$notes = 'Authorisation linked to passport — not a document. Online only. No centre.';
	} else {
		$route = $type ?: 'unknown'; $appNeeded = '[VERIFY]'; $portal = '[VERIFY]';
		$notes = '[VERIFY route — if appointment/biometric: add VFS/TLS/embassy centre + jurisdiction]';
	}
	fputcsv( $fh, [ $name, $slug, $route, $appNeeded, 'No', $portal, 'None (online) — no passport held', $notes ] );
}
// Global supply-chain nodes (needed regardless)
fputcsv( $fh, [ '— GLOBAL —', '', '', '', '', '', '', '' ] );
fputcsv( $fh, [ 'Courier (passport return)', 'global', 'Logistics', 'n/a', 'n/a', 'Royal Mail Special Delivery / DHL', 'Tracked + insured, we pay', 'Only used if you add appointment visas that hold passports.' ] );
fputcsv( $fh, [ 'PayPoint (IDP)', 'global', 'IDP self-service', 'n/a', 'n/a', 'paypoint store locator', 'Customer collects in person', 'We advise the right permit; customer obtains it.' ] );
fclose( $fh );
echo "Wrote $dir/supply-chain-capture.csv (".count($ids)." destinations + globals)\n";
