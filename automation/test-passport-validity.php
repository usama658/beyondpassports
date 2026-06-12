<?php
/**
 * Test: UKV Passport Validity barrier rule (Gap #74).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-passport-validity.php"
 */

$pass  = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// Sanity: required functions present.
$check( function_exists( 'ukv_create_order' ), 'ukv_create_order() is defined' );
$check( function_exists( 'ukv_seed_passport_validity' ), 'ukv_seed_passport_validity() is defined' );
$check( function_exists( 'ukv_passport_expiry' ), 'ukv_passport_expiry() is defined' );
$check( function_exists( 'ukv_auto_detect_barriers' ), 'ukv_auto_detect_barriers() is defined' );
$check( function_exists( 'ukv_dest_value' ), 'ukv_dest_value() is defined' );

$created  = [];
$barriers = [];

// Helper: find barrier IDs by rule_key.
$find_barriers = function ( $rule_key ) {
	return get_posts( [
		'post_type'   => 'ukv_barrier',
		'post_status' => 'publish',
		'fields'      => 'ids',
		'numberposts' => -1,
		'meta_query'  => [ [ 'key' => 'rule_key', 'value' => $rule_key ] ],
	] );
};

// 1. Seed runs; Egypt now returns 6 for passport_validity_months.
$n = ukv_seed_passport_validity();
echo "INFO — ukv_seed_passport_validity() updated {$n} destination(s)\n";
$egypt_val = (int) ukv_dest_value( 'egypt', 'passport_validity_months' );
$check( 6 === $egypt_val, "ukv_dest_value('egypt','passport_validity_months') === 6; got {$egypt_val}" );

// 2. Order whose passport expires SHORTER than 6 months beyond travel -> barrier fires.
$travel    = gmdate( 'Y-m-d', time() + 30 * 86400 );          // ~30 days out
$expiry_bad = gmdate( 'Y-m-d', time() + 60 * 86400 );          // ~1 month after travel (< 6mo validity)
$ref_bad   = 'UKV-PV-BAD-' . substr( (string) time(), -6 );
$oid_bad   = ukv_create_order( [
	'order_ref'   => $ref_bad,
	'name'        => 'Short Validity',
	'email'       => 'short.validity@example.com',
	'destination' => 'Egypt',
	'tier'        => 'Standard',
] );
$created[] = $oid_bad;
$check( $oid_bad > 0, "created short-validity Egypt order (#{$oid_bad})" );
update_post_meta( $oid_bad, 'ukv_status', 'paid' ); // open
update_post_meta( $oid_bad, 'ukv_travel_date', $travel );
update_post_meta( $oid_bad, 'ukv_passport_expiry', $expiry_bad );
$check( ukv_passport_expiry( $oid_bad ) === $expiry_bad, "ukv_passport_expiry() reads back '{$expiry_bad}'" );

ukv_auto_detect_barriers();

$key_bad   = "{$ref_bad}:passport_validity";
$hit_bad   = $find_barriers( $key_bad );
$barriers  = array_merge( $barriers, $hit_bad );
$check( count( $hit_bad ) >= 1, "barrier created with rule_key '{$key_bad}'" );

// Idempotency: a second run creates no duplicate.
ukv_auto_detect_barriers();
$hit_bad2 = $find_barriers( $key_bad );
$check( count( $hit_bad2 ) === count( $hit_bad ), 'second detect run created no duplicate (idempotent)' );

// 3. Order with passport expiry WELL beyond 6 months after travel -> no passport_validity barrier.
$expiry_ok = gmdate( 'Y-m-d', time() + 400 * 86400 );          // ~13 months out (well > travel + 6mo)
$ref_ok    = 'UKV-PV-OK-' . substr( (string) time(), -6 );
$oid_ok    = ukv_create_order( [
	'order_ref'   => $ref_ok,
	'name'        => 'Long Validity',
	'email'       => 'long.validity@example.com',
	'destination' => 'Egypt',
	'tier'        => 'Standard',
] );
$created[] = $oid_ok;
$check( $oid_ok > 0, "created long-validity Egypt order (#{$oid_ok})" );
update_post_meta( $oid_ok, 'ukv_status', 'paid' );
update_post_meta( $oid_ok, 'ukv_travel_date', $travel );
update_post_meta( $oid_ok, 'ukv_passport_expiry', $expiry_ok );

ukv_auto_detect_barriers();

$key_ok  = "{$ref_ok}:passport_validity";
$hit_ok  = $find_barriers( $key_ok );
$barriers = array_merge( $barriers, $hit_ok );
$check( count( $hit_ok ) === 0, "no passport_validity barrier for the compliant order ('{$key_ok}')" );

// 4. Clean up created orders + any barriers they spawned (all rule_keys for both refs).
foreach ( [ $ref_bad, $ref_ok ] as $r ) {
	foreach ( get_posts( [
		'post_type'   => 'ukv_barrier',
		'post_status' => 'publish',
		'fields'      => 'ids',
		'numberposts' => -1,
		'meta_query'  => [ [ 'key' => 'order_ref', 'value' => $r ] ],
	] ) as $bid ) {
		wp_delete_post( $bid, true );
	}
}
foreach ( $created as $id ) { if ( $id ) { wp_delete_post( $id, true ); } }
echo 'INFO — cleaned up orders: ' . implode( ', ', array_map( fn( $i ) => "#{$i}", $created ) ) . "\n";
echo "INFO — Pods passport_validity_months seeding left in place (real config).\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
