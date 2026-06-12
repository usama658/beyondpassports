<?php
/**
 * Plugin Name: UKV Discounts (Loyalty fast-track #83 + Review incentive #87)
 * Desc: Discount-code engine. Issues deterministic single-use codes, validates + redeems them,
 *       and powers two flows: a loyalty discount for returning customers and a review-incentive
 *       discount on the next order. Codes are stored merge-safe in the ukv_discount_codes option
 *       and every issuance is logged to the order's ukv_journey audit trail.
 */
defined( 'ABSPATH' ) || exit;

const UKV_DISCOUNTS_OPTION = 'ukv_discount_codes';
const UKV_LOYALTY_AMOUNT   = 10.0;
const UKV_REVIEW_AMOUNT    = 10.0;

/* =========================================================================
 * 1) Code store — merge-safe read/write of the ukv_discount_codes option.
 * ====================================================================== */

/** Read the full code store. Always an array of CODE => record. */
function ukv_discount_codes(): array {
	$codes = get_option( UKV_DISCOUNTS_OPTION, [] );
	return is_array( $codes ) ? $codes : [];
}

/**
 * Merge-safe store of a single code record. Never overwrites the whole option
 * destructively — reads current, sets the one key, writes back.
 */
function ukv_discount_store( string $code, array $record ): void {
	$codes          = ukv_discount_codes();
	$codes[ $code ] = $record;
	update_option( UKV_DISCOUNTS_OPTION, $codes, false );
}

/* =========================================================================
 * 2) Issue — generate a deterministic-ish, context-prefixed code.
 * ====================================================================== */

/**
 * Issue a discount code, store it, return the code.
 * Code = CONTEXT-XXXX where XXXX derives from md5(context.email.amount.count) — no rand/Date.
 * On the (rare) hash collision with an existing code, the existing count grows so the
 * next derivation differs; we extend the slug deterministically until it is unique.
 */
function ukv_issue_discount( float $amount, string $context, string $email = '' ): string {
	$context = sanitize_key( $context ) ?: 'code';
	$prefix  = strtoupper( $context );
	$codes   = ukv_discount_codes();
	$len     = 8;
	$seed    = md5( $context . $email . $amount . count( $codes ) );
	$code    = $prefix . '-' . strtoupper( substr( $seed, 0, $len ) );
	// Deterministic uniqueness: widen the slug from the same seed if already taken.
	while ( isset( $codes[ $code ] ) && $len < 32 ) {
		$len++;
		$code = $prefix . '-' . strtoupper( substr( $seed, 0, $len ) );
	}
	ukv_discount_store( $code, [
		'amount'    => (float) $amount,
		'context'   => $context,
		'email'     => sanitize_email( $email ),
		'used'      => false,
		'order_ref' => '',
	] );
	return $code;
}

/* =========================================================================
 * 3) Validate + redeem (single-use).
 * ====================================================================== */

/** Return the code record if it exists and is not used, else null. */
function ukv_validate_discount( string $code ): ?array {
	$codes = ukv_discount_codes();
	if ( ! isset( $codes[ $code ] ) ) { return null; }
	$rec = $codes[ $code ];
	if ( ! empty( $rec['used'] ) ) { return null; }
	return $rec;
}

/** Mark a code used + record the order ref. False if invalid or already used. */
function ukv_redeem_discount( string $code, string $order_ref ): bool {
	$rec = ukv_validate_discount( $code );
	if ( null === $rec ) { return false; }
	$rec['used']      = true;
	$rec['order_ref'] = sanitize_text_field( $order_ref );
	ukv_discount_store( $code, $rec );
	return true;
}

/* =========================================================================
 * Internal: append a journey audit note (reuse the shared helper if present).
 * ====================================================================== */
function ukv_discount_journey_note( int $order_id, string $text ): void {
	if ( function_exists( 'ukv_quicknote_add' ) ) {
		ukv_quicknote_add( $order_id, $text, 'internal' );
		return;
	}
	// Fallback: append directly in the canonical ukv_journey shape.
	$j   = get_post_meta( $order_id, 'ukv_journey', true );
	$j   = is_array( $j ) ? $j : [];
	$j[] = [
		'date'    => gmdate( 'Y-m-d H:i' ),
		'agent'   => 'system',
		'channel' => 'internal',
		'text'    => sanitize_textarea_field( $text ),
	];
	update_post_meta( $order_id, 'ukv_journey', $j );
}

/* =========================================================================
 * 4) Loyalty (#83) — returning-customer detection + loyalty discount.
 * ====================================================================== */

/** True if there is any ukv_order with this email (returning customer). */
function ukv_is_returning_customer( string $email ): bool {
	$email = sanitize_email( $email );
	if ( '' === $email ) { return false; }
	$ids = get_posts( [
		'post_type'      => 'ukv_order',
		'post_status'    => 'publish',
		'posts_per_page' => 2,
		'fields'         => 'ids',
		'meta_query'     => [ [ 'key' => 'ukv_email', 'value' => $email ] ],
	] );
	return count( $ids ) > 1;
}

/**
 * Issue a loyalty discount for a returning customer (the order's email).
 * Appends a journey note + returns the code. Returns '' if not a returning customer.
 */
function ukv_issue_loyalty_discount( int $order_id ): string {
	$email = (string) get_post_meta( $order_id, 'ukv_email', true );
	if ( '' === $email || ! ukv_is_returning_customer( $email ) ) { return ''; }
	$code = ukv_issue_discount( UKV_LOYALTY_AMOUNT, 'loyal', $email );
	ukv_discount_journey_note( $order_id, 'Loyalty discount issued: ' . $code );
	return $code;
}

/* =========================================================================
 * 5) Review (#87) — next-order discount tied to the order's email.
 * ====================================================================== */

/**
 * Issue a next-order review-incentive discount tied to the order's email.
 * Appends a journey note + returns the code. (Sent with the review-request email elsewhere.)
 */
function ukv_issue_review_discount( int $order_id ): string {
	$email = (string) get_post_meta( $order_id, 'ukv_email', true );
	$code  = ukv_issue_discount( UKV_REVIEW_AMOUNT, 'review', $email );
	ukv_discount_journey_note( $order_id, 'Review-incentive discount issued: ' . $code );
	return $code;
}

/* =========================================================================
 * 6) Admin page (Tools) — list issued codes, escaped.
 * ====================================================================== */
add_action( 'admin_menu', function () {
	add_management_page( 'UKV Discounts', 'UKV Discounts', 'manage_options', 'ukv-discounts', 'ukv_discounts_admin_page' );
} );

function ukv_discounts_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$codes = ukv_discount_codes();
	echo '<div class="wrap"><h1>UKV Discount codes</h1>';
	echo '<p>' . esc_html( count( $codes ) ) . ' code(s) issued.</p>';
	echo '<table class="widefat striped"><thead><tr><th>Code</th><th>Amount</th><th>Context</th><th>Email</th><th>Used</th><th>Order ref</th></tr></thead><tbody>';
	if ( ! $codes ) {
		echo '<tr><td colspan="6"><em>No codes issued yet.</em></td></tr>';
	}
	foreach ( $codes as $code => $rec ) {
		echo '<tr>';
		echo '<td><code>' . esc_html( $code ) . '</code></td>';
		echo '<td>&pound;' . esc_html( number_format( (float) ( $rec['amount'] ?? 0 ), 2 ) ) . '</td>';
		echo '<td>' . esc_html( $rec['context'] ?? '' ) . '</td>';
		echo '<td>' . esc_html( $rec['email'] ?? '' ) . '</td>';
		echo '<td>' . ( ! empty( $rec['used'] ) ? '<strong>Used</strong>' : 'Available' ) . '</td>';
		echo '<td>' . esc_html( $rec['order_ref'] ?? '' ) . '</td>';
		echo '</tr>';
	}
	echo '</tbody></table></div>';
}
