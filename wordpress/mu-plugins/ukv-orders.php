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

// Phase 7: Lead Journey meta box (critical header + note timeline)
const UKV_BLOCKERS = [ 'none' => 'None', 'docs_missing' => 'Docs missing', 'payment_pending' => 'Payment pending', 'eligibility' => 'Eligibility issue', 'customer_deciding' => 'Customer deciding' ];
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ukv_journey', 'Lead Journey & decision', 'ukv_journey_metabox', 'ukv_order', 'normal', 'high' );
} );
function ukv_journey_metabox( $post ) {
	$pid = $post->ID; wp_nonce_field( 'ukv_journey', 'ukv_journey_nonce' );
	$g = fn( $k, $d = '' ) => get_post_meta( $pid, $k, true ) ?: $d;
	echo '<style>.ukvj label{display:block;font-weight:600;margin:8px 0 2px}.ukvj input,.ukvj select,.ukvj textarea{width:100%}.ukvj-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}</style><div class="ukvj">';
	echo '<div class="ukvj-grid">';
	echo '<div><label>Stage</label><select name="ukv_status">'; foreach ( UKV_ORDER_STATUSES as $k => $v ) { echo '<option value="' . esc_attr( $k ) . '" ' . selected( $g( 'ukv_status', 'paid' ), $k, false ) . '>' . esc_html( $v ) . '</option>'; } echo '</select></div>';
	echo '<div><label>Blocker</label><select name="ukv_blocker">'; foreach ( UKV_BLOCKERS as $k => $v ) { echo '<option value="' . esc_attr( $k ) . '" ' . selected( $g( 'ukv_blocker', 'none' ), $k, false ) . '>' . esc_html( $v ) . '</option>'; } echo '</select></div>';
	echo '<div><label>Priority</label><select name="ukv_priority">'; foreach ( [ 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent' ] as $k => $v ) { echo '<option value="' . esc_attr( $k ) . '" ' . selected( $g( 'ukv_priority', 'normal' ), $k, false ) . '>' . esc_html( $v ) . '</option>'; } echo '</select></div>';
	echo '<div><label>Next action</label><input name="ukv_next_action" value="' . esc_attr( $g( 'ukv_next_action' ) ) . '"></div>';
	echo '<div><label>Next action due</label><input type="date" name="ukv_next_due" value="' . esc_attr( $g( 'ukv_next_due' ) ) . '"></div>';
	echo '<div><label>Travel date</label><input type="date" name="ukv_travel_date" value="' . esc_attr( $g( 'ukv_travel_date' ) ) . '"></div>';
	echo '</div>';
	echo '<p><label style="display:inline;font-weight:600"><input type="checkbox" name="ukv_risk_flag" value="1" ' . checked( $g( 'ukv_risk_flag' ), '1', false ) . '> Risk flag (rejection-likely)</label></p>';
	echo '<label>Value / upsell note</label><input name="ukv_value_note" value="' . esc_attr( $g( 'ukv_value_note' ) ) . '">';
	echo '<hr><label>Add journey note (logs to the story below)</label><textarea name="ukv_new_note" rows="2" placeholder="What happened on this call / chat..."></textarea>';
	echo '<label style="display:inline">Channel:</label> <select name="ukv_new_note_channel" style="width:auto"><option value="call">Call</option><option value="whatsapp">WhatsApp</option><option value="email">Email</option><option value="internal">Internal</option></select>';
	$journey = (array) get_post_meta( $pid, 'ukv_journey', true );
	echo '<hr><strong>Story so far</strong><ul style="margin-top:6px;max-height:240px;overflow:auto">';
	if ( ! $journey ) { echo '<li><em>No notes yet — add the first above.</em></li>'; }
	foreach ( array_reverse( $journey ) as $n ) {
		echo '<li style="border-bottom:1px solid #eee;padding:4px 0"><strong>' . esc_html( $n['date'] ?? '' ) . '</strong> · ' . esc_html( $n['agent'] ?? '' ) . ' · <em>' . esc_html( $n['channel'] ?? '' ) . '</em><br>' . esc_html( $n['text'] ?? '' ) . '</li>';
	}
	echo '</ul></div>';
}
add_action( 'save_post_ukv_order', function ( $pid ) {
	if ( ! isset( $_POST['ukv_journey_nonce'] ) || ! wp_verify_nonce( $_POST['ukv_journey_nonce'], 'ukv_journey' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	foreach ( [ 'ukv_status', 'ukv_blocker', 'ukv_priority', 'ukv_next_action', 'ukv_next_due', 'ukv_travel_date', 'ukv_value_note' ] as $f ) {
		if ( isset( $_POST[ $f ] ) ) { update_post_meta( $pid, $f, sanitize_text_field( wp_unslash( $_POST[ $f ] ) ) ); }
	}
	update_post_meta( $pid, 'ukv_risk_flag', isset( $_POST['ukv_risk_flag'] ) ? '1' : '' );
	$note = isset( $_POST['ukv_new_note'] ) ? trim( wp_unslash( $_POST['ukv_new_note'] ) ) : '';
	if ( '' !== $note ) {
		$j = (array) get_post_meta( $pid, 'ukv_journey', true );
		$u = wp_get_current_user();
		$j[] = [ 'date' => gmdate( 'Y-m-d H:i' ), 'agent' => $u->display_name ?: 'agent', 'channel' => sanitize_text_field( wp_unslash( $_POST['ukv_new_note_channel'] ?? 'internal' ) ), 'text' => sanitize_textarea_field( $note ) ];
		update_post_meta( $pid, 'ukv_journey', $j );
	}
} );
