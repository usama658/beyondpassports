<?php
/**
 * Test: UKV Discounts — loyalty fast-track (#83) + review incentive (#87) — ukv-discounts.php
 * Proves: deterministic single-use code issue/validate/redeem; returning-customer detection;
 *         loyalty + review issuance with a ukv_journey audit note.
 * Run: cd /c/xampp/htdocs/ukvisa && /c/xampp/php/php.exe -d memory_limit=512M wp-cli.phar eval-file "<abs path>" 2>&1 | tail -40
 */
defined( 'ABSPATH' ) || exit;

$pass = 0;
$fail = 0;
$ok = static function ( bool $cond, string $msg ) use ( &$pass, &$fail ) {
	if ( $cond ) { $pass++; echo "PASS — {$msg}\n"; }
	else { $fail++; echo "FAIL — {$msg}\n"; }
};

// Snapshot the option so the test restores it on the way out.
$option_before = get_option( UKV_DISCOUNTS_OPTION, [] );

$created_orders = [];

/* -- Dependencies present. -- */
$ok( function_exists( 'ukv_issue_discount' ), 'ukv_issue_discount() is defined' );
$ok( function_exists( 'ukv_validate_discount' ), 'ukv_validate_discount() is defined' );
$ok( function_exists( 'ukv_redeem_discount' ), 'ukv_redeem_discount() is defined' );
$ok( function_exists( 'ukv_is_returning_customer' ), 'ukv_is_returning_customer() is defined' );
$ok( function_exists( 'ukv_issue_loyalty_discount' ), 'ukv_issue_loyalty_discount() is defined' );
$ok( function_exists( 'ukv_issue_review_discount' ), 'ukv_issue_review_discount() is defined' );
$ok( function_exists( 'ukv_create_order' ), 'ukv_create_order() factory dependency is loaded' );

/* =========================================================================
 * 1) Issue / validate / redeem — single-use.
 * ====================================================================== */
$code = ukv_issue_discount( 10.0, 'test', 'a@x.com' );
$ok( is_string( $code ) && '' !== $code, "ukv_issue_discount returns a non-empty code ({$code})" );

$rec = ukv_validate_discount( $code );
$ok( is_array( $rec ), 'ukv_validate_discount returns a record for the fresh code' );
$ok( is_array( $rec ) && abs( (float) $rec['amount'] - 10.0 ) < 0.001, 'validated record has amount 10.0' );

$ok( true === ukv_redeem_discount( $code, 'REF-1' ), 'ukv_redeem_discount returns true on first redeem' );
$ok( false === ukv_redeem_discount( $code, 'REF-1' ), 'second redeem returns false (already used)' );
$ok( null === ukv_validate_discount( $code ), 'ukv_validate_discount returns null after redemption (used)' );
$ok( null === ukv_validate_discount( 'NOPE-99999999' ), 'unknown code validates to null' );

/* =========================================================================
 * 2) Loyalty (#83) — returning-customer detection + loyalty discount.
 * ====================================================================== */
$order1 = ukv_create_order( [ 'name' => 'Repeat Cust', 'email' => 'repeat@x.com', 'destination' => 'india' ] );
update_post_meta( $order1, 'ukv_status', 'delivered' );
$created_orders[] = $order1;
$ok( $order1 > 0, 'created order #1 for repeat@x.com (delivered)' );

$order2 = ukv_create_order( [ 'name' => 'Repeat Cust', 'email' => 'repeat@x.com', 'destination' => 'india' ] );
$created_orders[] = $order2;
$ok( $order2 > 0, 'created order #2 for the same email' );

$ok( true === ukv_is_returning_customer( 'repeat@x.com' ), 'ukv_is_returning_customer true for repeat@x.com (2 orders)' );

$loyal = ukv_issue_loyalty_discount( $order2 );
$ok( is_string( $loyal ) && '' !== $loyal, "ukv_issue_loyalty_discount returns a non-empty code ({$loyal})" );

$j2   = (array) get_post_meta( $order2, 'ukv_journey', true );
$last = $j2 ? end( $j2 ) : [];
$ok( is_array( $last ) && false !== stripos( (string) ( $last['text'] ?? '' ), 'Loyalty' ), 'order2 journey note mentions "Loyalty"' );
$ok( is_array( $last ) && false !== strpos( (string) ( $last['text'] ?? '' ), $loyal ), 'order2 journey note contains the issued code' );

// Brand-new email: not returning, loyalty returns ''.
$fresh = ukv_create_order( [ 'name' => 'New Cust', 'email' => 'brandnew@x.com', 'destination' => 'egypt' ] );
$created_orders[] = $fresh;
$ok( false === ukv_is_returning_customer( 'never-seen@x.com' ), 'ukv_is_returning_customer false for a never-seen email' );
$ok( '' === ukv_issue_loyalty_discount( $fresh ), 'ukv_issue_loyalty_discount returns "" for a non-returning customer' );

/* =========================================================================
 * 3) Review (#87) — next-order discount + journey note.
 * ====================================================================== */
$review = ukv_issue_review_discount( $order1 );
$ok( is_string( $review ) && '' !== $review, "ukv_issue_review_discount returns a non-empty code ({$review})" );

$j1    = (array) get_post_meta( $order1, 'ukv_journey', true );
$last1 = $j1 ? end( $j1 ) : [];
$ok( is_array( $last1 ) && false !== stripos( (string) ( $last1['text'] ?? '' ), 'Review' ), 'order1 journey note mentions "Review"' );
$ok( is_array( $last1 ) && false !== strpos( (string) ( $last1['text'] ?? '' ), $review ), 'order1 journey note contains the review code' );

// Issued review code is a valid, single-use, unredeemed code.
$rrec = ukv_validate_discount( $review );
$ok( is_array( $rrec ) && empty( $rrec['used'] ), 'review code validates as an unused code' );

/* =========================================================================
 * 4) Clean up: orders + restore the option to its prior value.
 * ====================================================================== */
foreach ( $created_orders as $oid ) {
	if ( $oid ) { wp_delete_post( $oid, true ); }
}
update_option( UKV_DISCOUNTS_OPTION, $option_before, false );

$cleanup_ok = true;
foreach ( $created_orders as $oid ) { if ( $oid && null !== get_post( $oid ) ) { $cleanup_ok = false; } }
$restored = get_option( UKV_DISCOUNTS_OPTION, [] );
$ok( $cleanup_ok, 'cleanup: test orders removed' );
$ok( $restored === $option_before, 'cleanup: ukv_discount_codes option restored to prior value' );

echo "\n{$pass} passed, {$fail} failed\n";
echo ( 0 === $fail ) ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
