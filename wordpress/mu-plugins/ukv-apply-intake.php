<?php
/**
 * Plugin Name: UKV Apply Intake Capture (#79)
 * Desc: Enriches the freshly-created ukv_order with three pieces of intake data captured on the
 *       Apply Forminator form (#300) at checkout: passport-expiry date, intended travel date, and
 *       story-consent. The Apply form charges via Stripe, ukv-hubspot.php creates the ukv_order on
 *       `forminator_custom_form_after_stripe_charge`; this plugin hooks the SAME action at a LATER
 *       priority (25) to enrich that order — exactly like ukv-story-consent.php. It NEVER edits
 *       ukv-hubspot.php and NEVER fatals on missing fields (every field is optional / guarded).
 *
 *       Order meta written (all `ukv_`-prefixed, per UKV conventions):
 *         ukv_passport_expiry  (Y-m-d date; read by the passport-validity rule)
 *         ukv_travel_date      (Y-m-d date)
 *         ukv_story_consent    ('1' when consented, '' otherwise)
 *
 *       Public surface:
 *         ukv_apply_intake_apply( int $order_id, array $data ): void  — testable core.
 *
 *       AUTOMATION NOTE (launch task): the live Apply Forminator form #300 must have THREE fields
 *       added in the Forminator form builder for this capture to do anything:
 *         1. a passport-expiry DATE field (Y-m-d),
 *         2. an intended travel DATE field (Y-m-d),
 *         3. a story-consent CHECKBOX ("I'm happy to share an anonymised review").
 *       This plugin reads those fields from the Stripe-charge prepared_data WHEN PRESENT (checking the
 *       likely Forminator field keys) and silently no-ops for any field that is absent. Until the
 *       fields exist on #300, the capture is a harmless no-op.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Testable core: sanitise the supplied intake values and write them onto an order.
 *
 * @param int   $order_id Target ukv_order ID.
 * @param array $data     Keys (all optional):
 *                          'passport_expiry' => date string (any parseable form),
 *                          'travel_date'     => date string (any parseable form),
 *                          'consent'         => bool-ish (truthy => consented).
 *                        A date that cannot be parsed to a real Y-m-d is NOT stored.
 */
function ukv_apply_intake_apply( int $order_id, array $data ): void {
	if ( $order_id <= 0 ) { return; }

	// Normalise an arbitrary date input to a valid Y-m-d, or '' when invalid/empty.
	$to_ymd = static function ( $raw ): string {
		if ( is_array( $raw ) ) { $raw = reset( $raw ); }
		$raw = is_string( $raw ) ? trim( $raw ) : ( is_scalar( $raw ) ? (string) $raw : '' );
		if ( '' === $raw ) { return ''; }
		$ts = strtotime( $raw );
		if ( false === $ts ) { return ''; }
		$ymd = gmdate( 'Y-m-d', $ts );
		// Round-trip guard: reject junk that strtotime coerced into something nonsensical.
		return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $ymd ) ? $ymd : '';
	};

	if ( array_key_exists( 'passport_expiry', $data ) ) {
		$pe = $to_ymd( $data['passport_expiry'] );
		if ( '' !== $pe ) {
			update_post_meta( $order_id, 'ukv_passport_expiry', $pe );
		}
	}

	if ( array_key_exists( 'travel_date', $data ) ) {
		$td = $to_ymd( $data['travel_date'] );
		if ( '' !== $td ) {
			update_post_meta( $order_id, 'ukv_travel_date', $td );
		}
	}

	if ( array_key_exists( 'consent', $data ) ) {
		update_post_meta( $order_id, 'ukv_story_consent', $data['consent'] ? '1' : '' );
	}
}

/**
 * Checkout capture: pull the three intake fields out of the Stripe-charge prepared_data and apply
 * them to the most-recent ukv_order matching the submitted email. Hooked LATER (priority 25) than
 * ukv-hubspot.php so the order already exists. Guarded throughout — never fatal on missing keys.
 */
add_action( 'forminator_custom_form_after_stripe_charge', function ( $module, $field, $stripe_entry_data, $prepared_data, $field_data_array ) {
	$pd = is_array( $prepared_data ) ? $prepared_data : [];

	// First non-empty value among the candidate keys (handles array-wrapped Forminator values).
	$first = static function ( array $keys ) use ( $pd ) {
		foreach ( $keys as $k ) {
			if ( ! array_key_exists( $k, $pd ) ) { continue; }
			$v = $pd[ $k ];
			if ( is_array( $v ) ) { $v = reset( $v ); }
			$v = is_scalar( $v ) ? trim( (string) $v ) : '';
			if ( '' !== $v ) { return $v; }
		}
		return '';
	};

	$truthy = static function ( array $keys ) use ( $pd ) {
		foreach ( $keys as $k ) {
			if ( ! array_key_exists( $k, $pd ) ) { continue; }
			$v = $pd[ $k ];
			if ( is_array( $v ) ) { if ( ! empty( array_filter( $v ) ) ) { return true; } continue; }
			$s = is_string( $v ) ? strtolower( trim( $v ) ) : $v;
			if ( in_array( $s, [ '1', 'on', 'true', 'yes', true, 1 ], true ) || ( is_numeric( $s ) && (int) $s > 0 ) ) {
				return true;
			}
		}
		return false;
	};

	$email = isset( $pd['email-1'] ) ? sanitize_email( (string) $pd['email-1'] ) : '';
	if ( '' === $email ) { return; }

	// Most-recent order for this email (the one this charge just created).
	$matches = get_posts( [
		'post_type'      => 'ukv_order',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'meta_key'       => 'ukv_email',
		'meta_value'     => $email,
		'fields'         => 'ids',
	] );
	if ( empty( $matches ) ) { return; }
	$order_id = (int) $matches[0];

	// Candidate Forminator field keys (finalised on #300 at launch — we probe the likely ones).
	$data = [
		'passport_expiry' => $first( [ 'date-1', 'date-2', 'passport_expiry' ] ),
		'travel_date'     => $first( [ 'date-2', 'date-3', 'travel_date' ] ),
		'consent'         => $truthy( [ 'checkbox-1', 'consent-1' ] ),
	];

	ukv_apply_intake_apply( $order_id, $data );
}, 25, 5 );
