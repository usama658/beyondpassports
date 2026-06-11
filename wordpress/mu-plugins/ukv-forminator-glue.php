<?php
/**
 * Plugin Name: UKV Forminator Glue (Pods -> Forminator)
 * Desc: Exposes Pods 'destination' data to Forminator via shortcodes. The only sanctioned custom glue.
 */
defined( 'ABSPATH' ) || exit;

function ukv_dest_resolve( $attr = '' ) {
	$d = sanitize_title( (string) $attr );
	if ( '' === $d && isset( $_GET['dest'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$d = sanitize_title( wp_unslash( $_GET['dest'] ) );
	}
	return $d;
}

function ukv_dest_value( $dest, $field ) {
	$allowed = [ 'required_for_uk', 'visa_type', 'max_stay_days', 'govt_fee_gbp',
		'tier_standard_gbp', 'tier_express_gbp', 'tier_premium_gbp',
		'idp_permit_type', 'idp_required_photocard', 'idp_required_paper' ];
	if ( ! in_array( $field, $allowed, true ) || ! function_exists( 'pods' ) || '' === $dest ) {
		return null;
	}
	$post = get_page_by_path( $dest, OBJECT, 'destination' );
	if ( ! $post ) { return null; }
	return pods( 'destination', $post->ID )->field( $field );
}

// [ukv_dest_fee dest="egypt"] -> plain number for Forminator calculation/hidden default
add_shortcode( 'ukv_dest_fee', function ( $a ) {
	$a = shortcode_atts( [ 'dest' => '' ], $a );
	$v = ukv_dest_value( ukv_dest_resolve( $a['dest'] ), 'govt_fee_gbp' );
	$n = preg_replace( '/[^0-9.]/', '', (string) $v );
	return ( '' === $n ) ? '' : esc_html( $n );
} );

// [ukv_dest_field dest="usa" field="visa_type"] -> sanitised field value
add_shortcode( 'ukv_dest_field', function ( $a ) {
	$a = shortcode_atts( [ 'dest' => '', 'field' => '' ], $a );
	$v = ukv_dest_value( ukv_dest_resolve( $a['dest'] ), sanitize_key( $a['field'] ) );
	if ( null === $v ) { return ''; }
	if ( is_array( $v ) ) { $v = reset( $v ); }
	return esc_html( wp_strip_all_tags( (string) $v ) );
} );
