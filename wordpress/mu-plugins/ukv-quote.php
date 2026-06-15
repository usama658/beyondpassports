<?php
/**
 * Plugin Name: UKV Bespoke Quote (manual-review pricing — Unit 7)
 * Desc: Manual-review orders are NOT priced on the fixed tiers. An agent sets a bespoke
 *       price reflecting the real work, then sends a Stripe Payment Link. This lane
 *       replaces the fixed tiers for those orders; the standard self-serve lane keeps
 *       its fixed tiers untouched. Stores the quote amount, status (none/sent/paid),
 *       the Payment Link URL and the sent timestamp, and writes a journey audit note
 *       when a quote is sent. Admin meta box on the order; gated save.
 */
defined( 'ABSPATH' ) || exit;

const UKV_QUOTE_STATUSES = [
	'none' => 'No quote',
	'sent' => 'Sent',
	'paid' => 'Paid',
];

// Default the stored quote status to 'none' so a raw get_post_meta() read returns the
// explicit default before any quote is set (rather than an empty string).
add_filter( 'default_post_metadata', function ( $value, $object_id, $meta_key, $single ) {
	if ( 'ukv_quote_status' === $meta_key && 'ukv_order' === get_post_type( $object_id ) ) {
		return $single ? 'none' : [ 'none' ];
	}
	return $value;
}, 10, 4 );

// Placeholder Payment Link. The real per-order Stripe Payment Link is generated via the
// live Stripe key at launch (Stripe Payment Links API) and written here in place of this.
const UKV_QUOTE_PLACEHOLDER_LINK = 'https://buy.stripe.com/PLACEHOLDER';

/**
 * Store a bespoke quote amount on an order. Does NOT change the quote status —
 * sending is a separate, explicit action (see ukv_quote_send()).
 */
function ukv_set_quote( int $order_id, float $amount ): void {
	update_post_meta( $order_id, 'ukv_quote_amount', (float) $amount );
	// Initialise the status meta to its default if a quote has never been sent,
	// so the stored value is the explicit 'none' (not an empty string).
	if ( '' === (string) get_post_meta( $order_id, 'ukv_quote_status', true ) ) {
		update_post_meta( $order_id, 'ukv_quote_status', 'none' );
	}
}

/**
 * Current bespoke quote amount for an order (0.0 when none set).
 */
function ukv_quote_amount( int $order_id ): float {
	return (float) get_post_meta( $order_id, 'ukv_quote_amount', true );
}

/**
 * Current quote status for an order. One of UKV_QUOTE_STATUSES; defaults to 'none'.
 */
function ukv_quote_status( int $order_id ): string {
	$s = (string) get_post_meta( $order_id, 'ukv_quote_status', true );
	return isset( UKV_QUOTE_STATUSES[ $s ] ) ? $s : 'none';
}

/**
 * Send the bespoke payment link for an order.
 *
 * Requires an amount > 0 (nothing to send otherwise -> returns false). On success:
 * sets status 'sent', stamps ukv_quote_sent_at, stores a placeholder Payment Link
 * (the real link is generated via the live Stripe key at launch) and appends a
 * journey audit note "Quote sent: GBP{amount}". Returns true.
 */
function ukv_quote_send( int $order_id ): bool {
	$amount = ukv_quote_amount( $order_id );
	if ( $amount <= 0 ) {
		return false;
	}

	update_post_meta( $order_id, 'ukv_quote_status', 'sent' );
	update_post_meta( $order_id, 'ukv_quote_sent_at', time() );
	// Placeholder until live Stripe keys — real Payment Link generated at launch.
	update_post_meta( $order_id, 'ukv_quote_link', UKV_QUOTE_PLACEHOLDER_LINK );

	$journey   = get_post_meta( $order_id, 'ukv_journey', true );
	$journey   = is_array( $journey ) ? $journey : [];
	$journey[] = [
		'date'    => gmdate( 'Y-m-d H:i' ),
		'agent'   => 'system',
		'channel' => 'internal',
		'text'    => sprintf( 'Quote sent: GBP%s', number_format( $amount, 2 ) ),
	];
	update_post_meta( $order_id, 'ukv_journey', $journey );

	return true;
}

/**
 * Is this order on the bespoke-quote (manual-review) lane? Quotes only apply to
 * manual_review (or a manually cleared) order; standard self-serve keeps fixed tiers.
 */
function ukv_quote_applies( int $order_id ): bool {
	$e = (string) get_post_meta( $order_id, 'ukv_eligibility', true );
	if ( 'manual_review' === $e || 'cleared' === $e ) {
		return true;
	}
	// Fall back to the eligibility helper if present (guarded per spec).
	if ( function_exists( 'ukv_order_is_cleared' ) && ukv_order_is_cleared( $order_id ) && 'standard' !== $e ) {
		return true;
	}
	return false;
}

/* -------------------------------------------------------------------------
 * Admin: "Bespoke quote" meta box — manual-review / cleared orders only.
 * ---------------------------------------------------------------------- */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ukv_quote', 'Bespoke quote', 'ukv_quote_metabox', 'ukv_order', 'side', 'high' );
} );

function ukv_quote_metabox( $post ) {
	$pid = (int) $post->ID;

	if ( ! ukv_quote_applies( $pid ) ) {
		echo '<p style="color:#555;font-size:11px">This order is on the standard self-serve lane — it uses the fixed tiers, not a bespoke quote.</p>';
		return;
	}

	wp_nonce_field( 'ukv_quote_' . $pid, 'ukv_quote_nonce' );

	$amount = ukv_quote_amount( $pid );
	$status = ukv_quote_status( $pid );
	$link   = (string) get_post_meta( $pid, 'ukv_quote_link', true );
	$sent   = (int) get_post_meta( $pid, 'ukv_quote_sent_at', true );

	echo '<p style="color:#555;font-size:11px">Manual-review orders are priced bespoke. This quote lane <strong>replaces the fixed tiers</strong> for this order.</p>';

	echo '<p><label for="ukv_quote_amount_f"><strong>Quote amount (GBP)</strong></label><br>';
	echo '<input type="number" step="0.01" min="0" id="ukv_quote_amount_f" name="ukv_quote_amount" value="' . esc_attr( $amount > 0 ? (string) $amount : '' ) . '" style="width:100%" placeholder="e.g. 149.00"></p>';

	echo '<p style="font-size:11px">Status: <strong>' . esc_html( UKV_QUOTE_STATUSES[ $status ] ?? $status ) . '</strong>';
	if ( $sent ) {
		echo ' &middot; sent ' . esc_html( gmdate( 'Y-m-d H:i', $sent ) ) . ' UTC';
	}
	echo '</p>';

	if ( '' !== $link ) {
		echo '<p style="font-size:11px;word-break:break-all">Payment link: <a href="' . esc_url( $link ) . '" target="_blank" rel="noopener">' . esc_html( $link ) . '</a><br><em>Placeholder until live Stripe keys — real Payment Link generated at launch.</em></p>';
	}

	echo '<p><button type="submit" name="ukv_quote_action" value="save" class="button">Save quote</button> ';
	echo '<button type="submit" name="ukv_quote_action" value="send" class="button button-primary">Send payment link</button></p>';
}

add_action( 'save_post_ukv_order', function ( $pid ) {
	if ( ! isset( $_POST['ukv_quote_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ukv_quote_nonce'] ) ), 'ukv_quote_' . $pid )
	) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $pid ) ) {
		return;
	}

	if ( isset( $_POST['ukv_quote_amount'] ) && '' !== trim( (string) wp_unslash( $_POST['ukv_quote_amount'] ) ) ) {
		$amount = (float) wp_unslash( $_POST['ukv_quote_amount'] );
		if ( $amount >= 0 ) {
			ukv_set_quote( $pid, $amount );
		}
	}

	$action = isset( $_POST['ukv_quote_action'] ) ? sanitize_text_field( wp_unslash( $_POST['ukv_quote_action'] ) ) : '';
	if ( 'send' === $action ) {
		ukv_quote_send( $pid );
	}
} );
