<?php
/**
 * Plugin Name: UKV Premium Appointment Slot (paid add-on, #84)
 * Desc: Premium / fast-track appointment slot as a PAID add-on (all tiers). The customer pays the
 *       centre's extra fee. Recorded SEPARATELY from the service total: ukv_total stays the service
 *       total; the extra centre fee is stored in ukv_premium_slot_fee and collected/paid to the centre.
 */
defined( 'ABSPATH' ) || exit;

/** Is a premium appointment slot active on this order? */
function ukv_has_premium_slot( int $order_id ): bool {
	return '1' === (string) get_post_meta( $order_id, 'ukv_premium_slot', true );
}

/**
 * Add the premium appointment slot add-on to an order.
 *
 * Records the add-on flag + the centre's extra fee SEPARATELY (in ukv_premium_slot_fee). It does NOT
 * touch ukv_total — that stays the service total; this fee is collected from / paid to the centre.
 * Appends a journey audit note. Idempotent: re-calling refreshes the fee + re-stamps the time.
 */
function ukv_add_premium_slot( int $order_id, float $fee ): void {
	if ( $order_id <= 0 ) { return; }
	update_post_meta( $order_id, 'ukv_premium_slot', '1' );
	update_post_meta( $order_id, 'ukv_premium_slot_fee', (float) $fee );
	update_post_meta( $order_id, 'ukv_premium_slot_added_at', time() );

	ukv_premium_slot_log_note(
		$order_id,
		sprintf( 'Premium appointment slot added: GBP%s (paid to centre)', number_format( (float) $fee, 2 ) )
	);
}

/** Remove the premium appointment slot add-on. Clears the flag + fee; logs the removal. */
function ukv_remove_premium_slot( int $order_id ): void {
	if ( $order_id <= 0 ) { return; }
	$was = ukv_has_premium_slot( $order_id );
	update_post_meta( $order_id, 'ukv_premium_slot', '' );
	delete_post_meta( $order_id, 'ukv_premium_slot_fee' );
	delete_post_meta( $order_id, 'ukv_premium_slot_added_at' );
	if ( $was ) {
		ukv_premium_slot_log_note( $order_id, 'Premium appointment slot removed' );
	}
}

/** Append a note to the order's shared journey timeline (matches ukv-orders.php shape). */
function ukv_premium_slot_log_note( int $order_id, string $text ): void {
	$j = (array) get_post_meta( $order_id, 'ukv_journey', true );
	$u = function_exists( 'wp_get_current_user' ) ? wp_get_current_user() : null;
	$j[] = [
		'date'    => gmdate( 'Y-m-d H:i' ),
		'agent'   => ( $u && $u->display_name ) ? $u->display_name : 'system',
		'channel' => 'internal',
		'text'    => trim( $text ),
	];
	update_post_meta( $order_id, 'ukv_journey', $j );
}

/* ---------------------------------------------------------------------------
 * Admin meta box — Premium appointment slot
 * ------------------------------------------------------------------------- */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ukv_premium_slot', 'Premium appointment slot', 'ukv_premium_slot_metabox', 'ukv_order', 'side', 'default' );
} );

function ukv_premium_slot_metabox( $post ) {
	$pid = (int) $post->ID;
	wp_nonce_field( 'ukv_premium_slot', 'ukv_premium_slot_nonce' );
	$on  = ukv_has_premium_slot( $pid );
	$fee = (string) get_post_meta( $pid, 'ukv_premium_slot_fee', true );
	echo '<p style="margin:.5em 0"><label style="font-weight:600"><input type="checkbox" name="ukv_premium_slot" value="1" ' . checked( $on, true, false ) . '> Premium / fast-track slot (paid add-on)</label></p>';
	echo '<p style="margin:.5em 0"><label style="display:block;font-weight:600;margin-bottom:2px">Extra centre fee (GBP)</label>';
	echo '<input type="number" step="0.01" min="0" name="ukv_premium_slot_fee" value="' . esc_attr( $fee ) . '" style="width:100%"></p>';
	echo '<p style="color:#666;margin:.5em 0;font-size:11px">Paid by the customer to the centre. Recorded separately — does not change the service total.</p>';
	if ( $on ) {
		$at = (int) get_post_meta( $pid, 'ukv_premium_slot_added_at', true );
		echo '<p style="color:#0f7b3f;margin:.5em 0;font-size:11px">Active' . ( $at ? ' since ' . esc_html( gmdate( 'Y-m-d H:i', $at ) ) . ' UTC' : '' ) . '</p>';
	}
}

add_action( 'save_post_ukv_order', function ( $pid ) {
	if ( ! isset( $_POST['ukv_premium_slot_nonce'] ) || ! wp_verify_nonce( $_POST['ukv_premium_slot_nonce'], 'ukv_premium_slot' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $pid ) ) { return; }

	$want = isset( $_POST['ukv_premium_slot'] );
	$fee  = isset( $_POST['ukv_premium_slot_fee'] ) ? (float) wp_unslash( $_POST['ukv_premium_slot_fee'] ) : 0.0;

	if ( $want ) {
		// Enable, or refresh the fee if it changed while already on.
		if ( ! ukv_has_premium_slot( $pid ) || (float) get_post_meta( $pid, 'ukv_premium_slot_fee', true ) !== $fee ) {
			ukv_add_premium_slot( $pid, $fee );
		}
	} elseif ( ukv_has_premium_slot( $pid ) ) {
		ukv_remove_premium_slot( $pid );
	}
} );
