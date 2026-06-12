<?php
/**
 * Plugin Name: UKV Refunds (Refund / Cancellation flow — Gap #72)
 * Desc: On a government refusal or cancellation we refund OUR SERVICE FEE. The
 *       government fee is non-refundable (already paid to the authority). Records
 *       the business action + journey audit; the actual Stripe API refund is
 *       triggered manually / at launch (needs live keys — out of scope here).
 */
defined( 'ABSPATH' ) || exit;

/**
 * Refundable amount = our service fee. NEVER the government fee.
 * Falls back to (total - govt_fee), floored at 0, if service_fee is not set.
 */
function ukv_refund_amount( int $order_id ): float {
	$service = get_post_meta( $order_id, 'ukv_service_fee', true );
	if ( '' !== $service && null !== $service ) {
		return max( 0.0, (float) $service );
	}
	$total = (float) get_post_meta( $order_id, 'ukv_total', true );
	$govt  = (float) get_post_meta( $order_id, 'ukv_govt_fee', true );
	return max( 0.0, $total - $govt );
}

/**
 * Record a refund: refunds the service fee only; govt fee stays non-refundable.
 * Sets status + refund meta, appends a journey audit note, and attempts customer comms.
 *
 * NOTE: The actual money movement (Stripe Refund API call) is NOT performed here —
 * it requires live Stripe keys and is triggered manually / at launch. This function
 * records the business decision and the audit trail.
 *
 * @return array{amount:float,status:string,reason:string}
 */
function ukv_process_refund( int $order_id, string $reason ): array {
	$reason = sanitize_text_field( $reason );
	$amount = ukv_refund_amount( $order_id );

	update_post_meta( $order_id, 'ukv_status', 'refunded' );
	update_post_meta( $order_id, 'ukv_refund_reason', $reason );
	update_post_meta( $order_id, 'ukv_refund_amount', $amount );
	update_post_meta( $order_id, 'ukv_refunded_at', time() );

	// Journey audit note (matches ukv-orders journey schema: date/agent/channel/text).
	$journey = get_post_meta( $order_id, 'ukv_journey', true );
	$journey = is_array( $journey ) ? $journey : [];
	$journey[] = [
		'date'    => gmdate( 'Y-m-d H:i' ),
		'agent'   => 'system',
		'channel' => 'internal',
		'text'    => sprintf(
			'Refund processed: GBP%s (service fee). Govt fee non-refundable. Reason: %s',
			number_format( $amount, 2 ),
			$reason
		),
	];
	update_post_meta( $order_id, 'ukv_journey', $journey );

	// Customer comms — attempt if the email engine has a 'refunded' event.
	// (The 'refunded' event may not exist yet; that's fine — we just attempt it.)
	if ( function_exists( 'ukv_email_fire' ) ) {
		ukv_email_fire( 'refunded', $order_id );
	}

	// TODO (launch): trigger the real Stripe Refund here once live keys exist,
	// e.g. \Stripe\Refund::create([ 'charge' => $charge_id, 'amount' => $amount*100 ]).
	// Until then this function only records the business action above.

	return [
		'amount' => $amount,
		'status' => 'refunded',
		'reason' => $reason,
	];
}

/* -------------------------------------------------------------------------
 * Admin: "Process refund" meta box on the order (server-side only).
 * ---------------------------------------------------------------------- */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ukv_refund', 'Process refund', 'ukv_refund_metabox', 'ukv_order', 'side', 'low' );
} );

function ukv_refund_metabox( $post ) {
	$pid    = (int) $post->ID;
	$status = get_post_meta( $pid, 'ukv_status', true );
	$amount = ukv_refund_amount( $pid );

	if ( 'refunded' === $status ) {
		$when   = (int) get_post_meta( $pid, 'ukv_refunded_at', true );
		$rsn    = (string) get_post_meta( $pid, 'ukv_refund_reason', true );
		$done   = (float) get_post_meta( $pid, 'ukv_refund_amount', true );
		echo '<p><strong>Already refunded:</strong> £' . esc_html( number_format( $done, 2 ) ) . '</p>';
		echo '<p style="color:#555">' . ( $when ? esc_html( gmdate( 'Y-m-d H:i', $when ) ) . ' · ' : '' ) . esc_html( $rsn ) . '</p>';
		echo '<p style="color:#555;font-size:11px">Govt fee was non-refundable.</p>';
		return;
	}

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'ukv_process_refund_' . $pid, 'ukv_refund_nonce' );
	echo '<input type="hidden" name="action" value="ukv_process_refund">';
	echo '<input type="hidden" name="order_id" value="' . esc_attr( $pid ) . '">';
	echo '<p><strong>Refundable (service fee):</strong> £' . esc_html( number_format( $amount, 2 ) ) . '</p>';
	echo '<p style="color:#555;font-size:11px">Government fee is non-refundable (already paid to the authority).</p>';
	echo '<p><label for="ukv_refund_reason">Reason</label>';
	echo '<textarea id="ukv_refund_reason" name="reason" rows="3" style="width:100%" placeholder="e.g. Government refusal / customer cancellation" required></textarea></p>';
	echo '<p><button type="submit" class="button button-primary" onclick="return confirm(\'Record refund of £' . esc_attr( number_format( $amount, 2 ) ) . ' (service fee)?\')">Process refund</button></p>';
	echo '<p style="color:#555;font-size:11px">Records the refund + audit note. The Stripe money refund is triggered manually / at launch.</p>';
	echo '</form>';
}

add_action( 'admin_post_ukv_process_refund', function () {
	$pid = isset( $_POST['order_id'] ) ? (int) $_POST['order_id'] : 0;
	if ( ! $pid
		|| ! isset( $_POST['ukv_refund_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ukv_refund_nonce'] ) ), 'ukv_process_refund_' . $pid )
		|| ! current_user_can( 'edit_post', $pid )
	) {
		wp_die( esc_html__( 'Not allowed.', 'ukv' ), 403 );
	}

	$reason = isset( $_POST['reason'] ) ? wp_unslash( $_POST['reason'] ) : '';
	ukv_process_refund( $pid, $reason );

	wp_safe_redirect( add_query_arg(
		[ 'post' => $pid, 'action' => 'edit', 'ukv_refunded' => '1' ],
		admin_url( 'post.php' )
	) );
	exit;
} );

add_action( 'admin_notices', function () {
	if ( isset( $_GET['ukv_refunded'] ) && '1' === $_GET['ukv_refunded'] ) {
		echo '<div class="notice notice-success is-dismissible"><p>Refund recorded (service fee). Govt fee non-refundable. Trigger the Stripe money refund manually if not yet automated.</p></div>';
	}
} );
