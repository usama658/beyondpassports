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

// [ukv_visa_table] -> server-rendered "do I need a visa?" table of all destinations (crawlable, SEO)
add_shortcode( 'ukv_visa_table', function () {
	if ( ! function_exists( 'pods' ) ) { return ''; }
	$ids = get_posts( [ 'post_type' => 'destination', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'fields' => 'ids' ] );
	$rows = '';
	foreach ( $ids as $id ) {
		$p     = pods( 'destination', $id );
		$name  = get_the_title( $id );
		$slug  = get_post_field( 'post_name', $id );
		$req   = $p->field( 'required_for_uk' );
		$type  = esc_html( $p->field( 'visa_type' ) );
		$stay  = (int) $p->field( 'max_stay_days' );
		$status = $req ? '<strong style="color:#1456B8">' . $type . ' required</strong>' : '<span style="color:#0f7b3f">No visa needed</span>';
		$rows .= '<tr><td><a href="/ukvisa/' . esc_attr( $slug ) . '/">' . esc_html( $name ) . '</a></td><td>' . $status . '</td><td>' . $stay . ' days</td></tr>';
	}
	return '<table class="ukv-checker-table" style="width:100%;border-collapse:collapse;font-family:Inter,sans-serif">'
		. '<thead><tr style="background:#0A2540;color:#fff"><th style="padding:10px;text-align:left">Destination</th><th style="padding:10px;text-align:left">UK citizens</th><th style="padding:10px;text-align:left">Max stay</th></tr></thead>'
		. '<tbody>' . $rows . '</tbody></table>';
} );
