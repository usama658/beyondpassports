<?php
/**
 * Plugin Name: UKV Government Submission Fields (Gap #69)
 * Desc: Structured government-submission fields on the order — govt reference, govt fee paid flag/timestamp, appointment ref + date. Adds a meta box, helpers and an admin list column.
 */
defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------
 * Helpers
 * ------------------------------------------------------------------------- */

/** The government submission reference number for an order. */
function ukv_govt_ref( int $order_id ): string {
	return (string) get_post_meta( $order_id, 'ukv_govt_ref', true );
}

/** Whether the government fee has been marked paid on this order. */
function ukv_govt_fee_is_paid( int $order_id ): bool {
	return '1' === (string) get_post_meta( $order_id, 'ukv_govt_fee_paid', true );
}

/* ---------------------------------------------------------------------------
 * Meta box
 * ------------------------------------------------------------------------- */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ukv_govt', 'Government submission', 'ukv_govt_metabox', 'ukv_order', 'normal', 'default' );
} );

function ukv_govt_metabox( $post ) {
	$pid = $post->ID;
	wp_nonce_field( 'ukv_govt_save', 'ukv_govt_nonce' );
	$g = fn( $k, $d = '' ) => get_post_meta( $pid, $k, true ) ?: $d;
	$paid_at = (int) $g( 'ukv_govt_fee_paid_at' );
	echo '<style>.ukvg label{display:block;font-weight:600;margin:8px 0 2px}.ukvg input{width:100%}.ukvg-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}</style><div class="ukvg">';
	echo '<div class="ukvg-grid">';
	echo '<div><label>Government reference (GWF / IHS / submission no.)</label><input name="ukv_govt_ref" value="' . esc_attr( $g( 'ukv_govt_ref' ) ) . '"></div>';
	echo '<div><label>Appointment reference (VFS / TLS / embassy)</label><input name="ukv_appointment_ref" value="' . esc_attr( $g( 'ukv_appointment_ref' ) ) . '"></div>';
	echo '<div><label>Appointment date</label><input type="date" name="ukv_appointment_at" value="' . esc_attr( $g( 'ukv_appointment_at' ) ) . '"></div>';
	echo '<div><label>&nbsp;</label><label style="display:inline;font-weight:600"><input type="checkbox" name="ukv_govt_fee_paid" value="1" ' . checked( $g( 'ukv_govt_fee_paid' ), '1', false ) . '> Government fee paid</label>';
	if ( $paid_at ) { echo '<br><span style="color:#0f7b3f;font-size:11px">Marked paid: ' . esc_html( gmdate( 'Y-m-d H:i', $paid_at ) ) . ' UTC</span>'; }
	echo '</div>';
	echo '</div></div>';
}

add_action( 'save_post_ukv_order', function ( $pid ) {
	if ( ! isset( $_POST['ukv_govt_nonce'] ) || ! wp_verify_nonce( $_POST['ukv_govt_nonce'], 'ukv_govt_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $pid ) ) { return; }

	foreach ( [ 'ukv_govt_ref', 'ukv_appointment_ref', 'ukv_appointment_at' ] as $f ) {
		if ( isset( $_POST[ $f ] ) ) { update_post_meta( $pid, $f, sanitize_text_field( wp_unslash( $_POST[ $f ] ) ) ); }
	}

	$was_paid = ukv_govt_fee_is_paid( $pid );
	$now_paid = ! empty( $_POST['ukv_govt_fee_paid'] );
	update_post_meta( $pid, 'ukv_govt_fee_paid', $now_paid ? '1' : '' );
	if ( $now_paid && ! $was_paid && ! (int) get_post_meta( $pid, 'ukv_govt_fee_paid_at', true ) ) {
		update_post_meta( $pid, 'ukv_govt_fee_paid_at', time() );
	}
} );

/* ---------------------------------------------------------------------------
 * Admin list column: govt ref + paid/unpaid badge
 * ------------------------------------------------------------------------- */

add_filter( 'manage_ukv_order_posts_columns', function ( $c ) {
	// Priority 20 so this runs AFTER ukv-orders.php (loads later alphabetically) rebuilds the column set.
	if ( isset( $c['ukv_govt'] ) ) { return $c; }
	$new = [];
	foreach ( $c as $k => $v ) { $new[ $k ] = $v; if ( 'ukv_status' === $k ) { $new['ukv_govt'] = 'Govt ref / fee'; } }
	if ( ! isset( $new['ukv_govt'] ) ) {
		// No ukv_status anchor — insert before the date column if present, else append.
		$new = [];
		foreach ( $c as $k => $v ) { if ( 'date' === $k ) { $new['ukv_govt'] = 'Govt ref / fee'; } $new[ $k ] = $v; }
		if ( ! isset( $new['ukv_govt'] ) ) { $new['ukv_govt'] = 'Govt ref / fee'; }
	}
	return $new;
}, 20 );

add_action( 'manage_ukv_order_posts_custom_column', function ( $col, $pid ) {
	if ( 'ukv_govt' !== $col ) { return; }
	$ref  = ukv_govt_ref( (int) $pid );
	$paid = ukv_govt_fee_is_paid( (int) $pid );
	$badge = $paid
		? '<span style="color:#0f7b3f;font-weight:700">Fee paid</span>'
		: '<span style="color:#c00">Fee unpaid</span>';
	echo $ref ? esc_html( $ref ) . '<br>' : '<span style="color:#888">—</span><br>';
	echo $badge; // phpcs:ignore — static markup, ref already escaped above
}, 10, 2 );
