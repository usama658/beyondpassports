<?php
/**
 * Plugin Name: UKV Orders (Smart Orders Hub — Phase 1)
 * Desc: Canonical Order record per paid order + admin dashboard. Created from the Stripe charge hook.
 */
defined( 'ABSPATH' ) || exit;

const UKV_ORDER_STATUSES = [ 'paid' => 'Paid', 'awaiting_docs' => 'Awaiting docs', 'doc_review' => 'Doc review', 'submitted' => 'Submitted', 'awaiting_decision' => 'Awaiting decision', 'delivered' => 'Delivered', 'won' => 'Won', 'rejected' => 'Rejected', 'refunded' => 'Refunded' ];

// CPT (admin only)
add_action( 'init', function () {
	register_post_type( 'ukv_order', [
		'labels'       => [ 'name' => 'Orders', 'singular_name' => 'Order', 'menu_name' => 'Orders' ],
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => true,
		'menu_icon'    => 'dashicons-clipboard',
		'menu_position'=> 3,
		'supports'     => [ 'title' ],
		'capability_type' => 'post',
	] );
} );

/**
 * Create an order record. Returns post ID.
 * $d keys: order_ref, name, email, destination, tier, service_fee, govt_fee, total, passport_number, documents(array), hubspot_deal
 */
function ukv_create_order( array $d ) {
	$ref = $d['order_ref'] ?? ( 'UKV-' . gmdate( 'Y' ) . '-' . substr( (string) time(), -6 ) );
	$pid = wp_insert_post( [
		'post_type'   => 'ukv_order',
		'post_status' => 'publish',
		'post_title'  => $ref . ' — ' . ( $d['destination'] ?? '' ) . ' (' . ( $d['name'] ?? '' ) . ')',
	] );
	if ( is_wp_error( $pid ) || ! $pid ) { return 0; }
	foreach ( [ 'order_ref' => $ref, 'name', 'email', 'destination', 'tier', 'service_fee', 'govt_fee', 'total', 'passport_number', 'hubspot_deal' ] as $k => $v ) {
		$key = is_int( $k ) ? $v : $k;
		$val = is_int( $k ) ? ( $d[ $v ] ?? '' ) : $v;
		update_post_meta( $pid, 'ukv_' . $key, $val );
	}
	update_post_meta( $pid, 'ukv_documents', $d['documents'] ?? [] );
	update_post_meta( $pid, 'ukv_status', 'paid' );
	update_post_meta( $pid, 'ukv_created', time() );
	return $pid;
}

// Admin columns
add_filter( 'manage_ukv_order_posts_columns', function ( $c ) {
	return [ 'cb' => $c['cb'], 'title' => 'Order', 'ukv_customer' => 'Customer', 'ukv_dest' => 'Destination', 'ukv_tier' => 'Tier', 'ukv_total' => 'Total', 'ukv_status' => 'Status', 'date' => 'Date' ];
} );
add_action( 'manage_ukv_order_posts_custom_column', function ( $col, $pid ) {
	$g = fn( $k ) => get_post_meta( $pid, $k, true );
	switch ( $col ) {
		case 'ukv_customer': echo esc_html( $g( 'ukv_name' ) . ' · ' . $g( 'ukv_email' ) ); break;
		case 'ukv_dest': echo esc_html( ucfirst( $g( 'ukv_destination' ) ) ); break;
		case 'ukv_tier': echo esc_html( ucfirst( $g( 'ukv_tier' ) ) ); break;
		case 'ukv_total': echo '£' . esc_html( $g( 'ukv_total' ) ); break;
		case 'ukv_status': $s = $g( 'ukv_status' ); echo '<strong>' . esc_html( UKV_ORDER_STATUSES[ $s ] ?? $s ) . '</strong>'; break;
	}
}, 10, 2 );
