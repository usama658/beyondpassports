<?php
// Per-destination service-fee tiers (sellable destinations only). Idempotent. Run via wp eval-file.
// Visa-free destinations (turkey/morocco/uae/schengen) are not sold — left at defaults.
$pricing = [
	'usa'       => [ 25, 39, 59 ],  // simple eTA (ESTA)
	'australia' => [ 25, 39, 59 ],  // simple eTA (eVisitor)
	'egypt'     => [ 29, 49, 79 ],  // standard eVisa
	'india'     => [ 35, 55, 85 ],  // more complex eVisa
];
foreach ( $pricing as $slug => $t ) {
	$p = get_page_by_path( $slug, OBJECT, 'destination' );
	if ( ! $p ) { continue; }
	update_post_meta( $p->ID, 'tier_standard_gbp', $t[0] );
	update_post_meta( $p->ID, 'tier_express_gbp', $t[1] );
	update_post_meta( $p->ID, 'tier_premium_gbp', $t[2] );
	echo "$slug -> {$t[0]}/{$t[1]}/{$t[2]}\n";
}
