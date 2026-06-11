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

// Prefill the Apply form's destination dropdown from ?dest= (money-page CTAs pass it)
add_action( 'wp_footer', function () {
	if ( ! is_page( 'apply' ) ) { return; }
	echo '<script>document.addEventListener("DOMContentLoaded",function(){var d=new URLSearchParams(location.search).get("dest");if(!d)return;var t=setInterval(function(){var s=document.querySelector("select[name=select-1]");if(s){s.value=d;s.dispatchEvent(new Event("change",{bubbles:true}));clearInterval(t);}},300);setTimeout(function(){clearInterval(t);},6000);});</script>';
} );

// Expand [ukv_dest_fee] inside Forminator hidden field (render + calculation) so the total computes
add_filter( 'forminator_field_hidden_field_value', function ( $value ) {
	if ( is_string( $value ) && false !== strpos( $value, '[ukv_dest_fee' ) ) {
		$n = do_shortcode( $value );
		return ( '' === trim( $n ) ) ? '0' : $n;
	}
	return $value;
}, 10, 1 );
add_filter( 'forminator_field_hidden_calculable_value', function ( $calc, $submitted = null, $settings = null ) {
	if ( is_array( $settings ) && isset( $settings['custom_value'] ) && false !== strpos( (string) $settings['custom_value'], '[ukv_dest_fee' ) ) {
		$n = do_shortcode( (string) $settings['custom_value'] );
		return is_numeric( $n ) ? (float) $n : 0;
	}
	if ( ! is_numeric( $calc ) ) { return 0; }
	return $calc;
}, 10, 3 );

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

// [ukv_dest_grid] -> conversion destination cards linking to money pages
add_shortcode( 'ukv_dest_grid', function () {
	if ( ! function_exists( 'pods' ) ) { return ''; }
	$ids = get_posts( [ 'post_type' => 'destination', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'fields' => 'ids' ] );
	$cards = '';
	foreach ( $ids as $id ) {
		$p     = pods( 'destination', $id );
		$name  = esc_html( get_the_title( $id ) );
		$slug  = esc_attr( get_post_field( 'post_name', $id ) );
		$req   = $p->field( 'required_for_uk' );
		$type  = esc_html( $p->field( 'visa_type' ) );
		$badge = $req
			? '<span style="background:#1456B8;color:#fff;font-size:12px;padding:3px 8px;border-radius:20px">' . $type . '</span>'
			: '<span style="background:#0f7b3f;color:#fff;font-size:12px;padding:3px 8px;border-radius:20px">visa-free</span>';
		$cards .= '<a href="/ukvisa/' . $slug . '/" style="display:block;text-decoration:none;border:1px solid #e3e8f0;border-radius:10px;padding:18px;background:#fff;color:#0A2540;transition:.15s">'
			. '<div style="font-size:18px;font-weight:700;margin-bottom:6px">' . $name . '</div>' . $badge . '</a>';
	}
	return '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;font-family:Inter,sans-serif">' . $cards . '</div>';
} );

// [ukv_idp_table] -> IDP requirement by destination + licence type (crawlable; encodes the photocard rule)
add_shortcode( 'ukv_idp_table', function () {
	if ( ! function_exists( 'pods' ) ) { return ''; }
	$ids = get_posts( [ 'post_type' => 'destination', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'fields' => 'ids' ] );
	$yn = function ( $v ) { return $v ? '<strong style="color:#1456B8">Yes</strong>' : '<span style="color:#0f7b3f">No</span>'; };
	$rows = '';
	foreach ( $ids as $id ) {
		$p    = pods( 'destination', $id );
		$name = esc_html( get_the_title( $id ) );
		$conv = esc_html( $p->field( 'idp_permit_type' ) );
		$rows .= '<tr><td>' . $name . '</td><td>' . $conv . '</td><td>' . $yn( $p->field( 'idp_required_photocard' ) ) . '</td><td>' . $yn( $p->field( 'idp_required_paper' ) ) . '</td></tr>';
	}
	return '<table class="ukv-idp-table" style="width:100%;border-collapse:collapse;font-family:Inter,sans-serif">'
		. '<thead><tr style="background:#0A2540;color:#fff"><th style="padding:10px;text-align:left">Destination</th><th style="padding:10px;text-align:left">Convention</th><th style="padding:10px;text-align:left">Photocard licence</th><th style="padding:10px;text-align:left">Paper licence</th></tr></thead>'
		. '<tbody>' . $rows . '</tbody></table>'
		. '<p style="font-size:13px;color:#5a6577">"Yes" = an International Driving Permit is needed. With a UK photocard licence you do <strong>not</strong> need an IDP in the EU/EEA, Switzerland, Norway, Iceland or Liechtenstein. IDPs are issued in person at PayPoint for &pound;5.50.</p>';
} );
