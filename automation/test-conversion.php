<?php
/**
 * Test: UKV Conversion-optimisation features (trust bar, testimonials, exit-intent modal).
 * Run: cd /c/xampp/htdocs/ukvisa && php -d memory_limit=512M wp-cli.phar eval-file "<path>/automation/test-conversion.php"
 */

$pass = true;
$check = function ( $cond, $msg ) use ( &$pass ) {
	if ( $cond ) { echo "PASS — {$msg}\n"; }
	else { echo "FAIL — {$msg}\n"; $pass = false; }
};

// 1. Shortcodes registered.
$check( shortcode_exists( 'ukv_trust_bar' ), "shortcode 'ukv_trust_bar' is registered" );
$check( shortcode_exists( 'ukv_testimonials' ), "shortcode 'ukv_testimonials' is registered" );

// 2. Trust bar renders expected copy without a PHP error.
$bar = do_shortcode( '[ukv_trust_bar]' );
$check( is_string( $bar ) && strpos( $bar, 'Independent service' ) !== false, "trust bar contains 'Independent service'" );
$check( strpos( $bar, 'Not a government website' ) !== false, "trust bar contains 'Not a government website'" );

// 3. Testimonials: ensure category exists, create a published post in it, assert it renders.
$term = term_exists( 'testimonial', 'category' );
if ( ! $term ) { $term = wp_insert_term( 'Testimonials', 'category', array( 'slug' => 'testimonial' ) ); }
$check( ! is_wp_error( $term ) && ! empty( $term ), "category 'testimonial' exists" );
$cat_id = (int) ( is_array( $term ) ? $term['term_id'] : $term );

$post_id = wp_insert_post( array(
	'post_title'   => 'Great service',
	'post_excerpt' => 'Smooth and fast.',
	'post_content' => 'Full body of the review.',
	'post_status'  => 'publish',
	'post_type'    => 'post',
	'post_category' => array( $cat_id ),
) );
$check( $post_id > 0 && ! is_wp_error( $post_id ), "created published testimonial post (#{$post_id})" );

$out = do_shortcode( '[ukv_testimonials]' );
$check( is_string( $out ) && strpos( $out, 'Great service' ) !== false, "testimonials output contains 'Great service'" );

// 4. With the post deleted -> still a string (empty ok), no fatal.
wp_delete_post( $post_id, true );
clean_post_cache( $post_id );
$out2 = do_shortcode( '[ukv_testimonials]' );
$check( is_string( $out2 ), 'after delete, testimonials returns a string (empty ok), no fatal' );

echo "INFO — cleaned up testimonial post #{$post_id}\n";

echo $pass ? "RESULT: ALL PASS\n" : "RESULT: FAILURES PRESENT\n";
