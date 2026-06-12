<?php
/**
 * Plugin Name: UKV Passport Return (tracked passport-return logistics — Gap #86)
 * Desc: Sticker-visa passports are returned to the customer by a tracked + insured
 *       courier (we pay for it — locked policy). Records the return carrier, tracking
 *       number and status (none/pending/dispatched/delivered), stamps the dispatch
 *       time and writes a journey audit note so the trail shows when + how a passport
 *       went back. Admin meta box on the order; gated save.
 */
defined( 'ABSPATH' ) || exit;

const UKV_RETURN_STATUSES = [
	'none'       => 'No return needed',
	'pending'    => 'Pending dispatch',
	'dispatched' => 'Dispatched (tracked)',
	'delivered'  => 'Delivered',
];

/**
 * Current passport-return status for an order. Defaults to 'none'.
 */
function ukv_return_status( int $order_id ): string {
	$s = (string) get_post_meta( $order_id, 'ukv_return_status', true );
	return isset( UKV_RETURN_STATUSES[ $s ] ) ? $s : 'none';
}

/**
 * Set the passport-return details on an order.
 *
 * $data keys (all optional): carrier (string), tracking (string), status (one of
 * UKV_RETURN_STATUSES). When the status becomes 'dispatched' for the first time we
 * stamp ukv_return_dispatched_at and append a journey audit note recording the
 * carrier + tracking number, so the timeline shows when and how the passport went back.
 */
function ukv_set_return( int $order_id, array $data ): void {
	if ( array_key_exists( 'carrier', $data ) ) {
		update_post_meta( $order_id, 'ukv_return_carrier', sanitize_text_field( (string) $data['carrier'] ) );
	}
	if ( array_key_exists( 'tracking', $data ) ) {
		update_post_meta( $order_id, 'ukv_return_tracking', sanitize_text_field( (string) $data['tracking'] ) );
	}

	if ( ! array_key_exists( 'status', $data ) ) {
		return;
	}

	$new = (string) $data['status'];
	if ( ! isset( UKV_RETURN_STATUSES[ $new ] ) ) {
		$new = 'none';
	}
	$old = ukv_return_status( $order_id );
	update_post_meta( $order_id, 'ukv_return_status', $new );

	// First transition into 'dispatched': stamp the time + log a journey note.
	if ( 'dispatched' === $new && 'dispatched' !== $old ) {
		if ( ! get_post_meta( $order_id, 'ukv_return_dispatched_at', true ) ) {
			update_post_meta( $order_id, 'ukv_return_dispatched_at', time() );
		}

		$carrier  = (string) get_post_meta( $order_id, 'ukv_return_carrier', true );
		$tracking = (string) get_post_meta( $order_id, 'ukv_return_tracking', true );

		$journey   = get_post_meta( $order_id, 'ukv_journey', true );
		$journey   = is_array( $journey ) ? $journey : [];
		$journey[] = [
			'date'    => gmdate( 'Y-m-d H:i' ),
			'agent'   => 'system',
			'channel' => 'internal',
			'text'    => trim( sprintf(
				'Passport dispatched: %s %s',
				$carrier,
				$tracking
			) ),
		];
		update_post_meta( $order_id, 'ukv_journey', $journey );
	}
}

/* -------------------------------------------------------------------------
 * Admin: "Passport return" meta box on the order.
 * ---------------------------------------------------------------------- */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ukv_passport_return', 'Passport return', 'ukv_passport_return_metabox', 'ukv_order', 'side', 'default' );
} );

function ukv_passport_return_metabox( $post ) {
	$pid      = (int) $post->ID;
	wp_nonce_field( 'ukv_passport_return_' . $pid, 'ukv_return_nonce' );

	$status   = ukv_return_status( $pid );
	$carrier  = (string) get_post_meta( $pid, 'ukv_return_carrier', true );
	$tracking = (string) get_post_meta( $pid, 'ukv_return_tracking', true );
	$disp     = (int) get_post_meta( $pid, 'ukv_return_dispatched_at', true );

	echo '<p style="color:#555;font-size:11px">Sticker-visa passports return by tracked + insured courier (we pay).</p>';

	echo '<p><label for="ukv_return_status_f"><strong>Status</strong></label><br>';
	echo '<select id="ukv_return_status_f" name="ukv_return_status" style="width:100%">';
	foreach ( UKV_RETURN_STATUSES as $k => $label ) {
		echo '<option value="' . esc_attr( $k ) . '" ' . selected( $status, $k, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select></p>';

	echo '<p><label for="ukv_return_carrier_f"><strong>Carrier</strong></label><br>';
	echo '<input type="text" id="ukv_return_carrier_f" name="ukv_return_carrier" value="' . esc_attr( $carrier ) . '" style="width:100%" placeholder="e.g. Royal Mail Special Delivery"></p>';

	echo '<p><label for="ukv_return_tracking_f"><strong>Tracking number</strong></label><br>';
	echo '<input type="text" id="ukv_return_tracking_f" name="ukv_return_tracking" value="' . esc_attr( $tracking ) . '" style="width:100%" placeholder="e.g. RM123456789GB"></p>';

	if ( $disp ) {
		echo '<p style="color:#555;font-size:11px">Dispatched: ' . esc_html( gmdate( 'Y-m-d H:i', $disp ) ) . ' UTC</p>';
	}
}

add_action( 'save_post_ukv_order', function ( $pid ) {
	if ( ! isset( $_POST['ukv_return_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ukv_return_nonce'] ) ), 'ukv_passport_return_' . $pid )
	) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $pid ) ) {
		return;
	}

	$data = [];
	if ( isset( $_POST['ukv_return_carrier'] ) ) {
		$data['carrier'] = wp_unslash( $_POST['ukv_return_carrier'] );
	}
	if ( isset( $_POST['ukv_return_tracking'] ) ) {
		$data['tracking'] = wp_unslash( $_POST['ukv_return_tracking'] );
	}
	if ( isset( $_POST['ukv_return_status'] ) ) {
		$data['status'] = sanitize_text_field( wp_unslash( $_POST['ukv_return_status'] ) );
	}

	if ( $data ) {
		ukv_set_return( $pid, $data );
	}
} );
