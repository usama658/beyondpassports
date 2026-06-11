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

// Phase 2 (completeness) + Phase 3 (SLA): extra columns
add_filter( 'manage_ukv_order_posts_columns', function ( $c ) {
	$new = [];
	foreach ( $c as $k => $v ) { $new[ $k ] = $v; if ( 'ukv_total' === $k ) { $new['ukv_docs'] = 'Docs'; $new['ukv_sla'] = 'SLA'; } }
	return $new;
} );
function ukv_order_sla_hours( $tier ) {
	$t = strtolower( (string) $tier );
	if ( strpos( $t, 'express' ) !== false ) { return 24; }
	if ( strpos( $t, 'premium' ) !== false ) { return 12; }
	return 72;
}
add_action( 'manage_ukv_order_posts_custom_column', function ( $col, $pid ) {
	if ( 'ukv_docs' === $col ) {
		$n = count( array_filter( (array) get_post_meta( $pid, 'ukv_documents', true ) ) );
		echo $n ? '<span style="color:#0f7b3f">' . (int) $n . ' file(s)</span>' : '<span style="color:#c00">No docs</span>';
	}
	if ( 'ukv_sla' === $col ) {
		$st = get_post_meta( $pid, 'ukv_status', true );
		if ( in_array( $st, [ 'delivered', 'won', 'refunded', 'rejected' ], true ) ) { echo '&mdash;'; return; }
		$due = (int) get_post_meta( $pid, 'ukv_created', true ) + ukv_order_sla_hours( get_post_meta( $pid, 'ukv_tier', true ) ) * 3600;
		$now = time();
		if ( $now > $due ) { echo '<span style="color:#c00;font-weight:700">Overdue</span>'; }
		elseif ( $now > $due - 6 * 3600 ) { echo '<span style="color:#b8860b">Due soon</span>'; }
		else { echo '<span style="color:#0f7b3f">On track</span>'; }
	}
}, 9, 2 );

// Phase 4: ops insights dashboard widget
add_action( 'wp_dashboard_setup', function () {
	wp_add_dashboard_widget( 'ukv_orders_insights', 'UKVisaCo — Orders insights', function () {
		$ids = get_posts( [ 'post_type' => 'ukv_order', 'posts_per_page' => -1, 'fields' => 'ids', 'post_status' => 'publish' ] );
		$by = []; $rev = 0;
		foreach ( $ids as $pid ) {
			$s = get_post_meta( $pid, 'ukv_status', true ) ?: 'paid';
			$by[ $s ] = ( $by[ $s ] ?? 0 ) + 1;
			if ( ! in_array( $s, [ 'refunded', 'rejected' ], true ) ) { $rev += (float) get_post_meta( $pid, 'ukv_total', true ); }
		}
		echo '<p><strong>Total orders:</strong> ' . count( $ids ) . ' &middot; <strong>Revenue:</strong> £' . number_format( $rev, 2 ) . '</p><ul style="margin-left:1em">';
		foreach ( UKV_ORDER_STATUSES as $k => $label ) { if ( ! empty( $by[ $k ] ) ) { echo '<li>' . esc_html( $label ) . ': <strong>' . (int) $by[ $k ] . '</strong></li>'; } }
		echo '</ul>';
	} );
} );
