<?php
/**
 * Plugin Name: UKV Eligibility (intake axes + router) — Phase 1 Unit 1
 * Desc: Captures nationality / residence / status + trip purpose + applicant/minor + refusal + dual-nationality +
 *       insurance-needed on the order, and routes Standard vs Manual-review. Foundation for the eligibility gate,
 *       funnel branch, and bespoke-quote pricing. Spec: docs/superpowers/specs/2026-06-15-residency-eligibility-design.md
 */
defined( 'ABSPATH' ) || exit;

const UKV_RESIDENCY_STATUS = [ 'citizen' => 'Citizen', 'permanent' => 'Permanent resident', 'visa_holder' => 'Visa holder', 'other' => 'Other' ];
const UKV_TRIP_PURPOSE     = [ 'tourist' => 'Tourist', 'business' => 'Business', 'transit' => 'Transit', 'study' => 'Study', 'other' => 'Other' ];

/** Normalise a country value to a slug for comparison (UK / United Kingdom / gb -> uk-ish). */
function ukv_is_uk( $v ) {
	$s = strtolower( trim( (string) $v ) );
	return in_array( $s, [ 'uk', 'gb', 'gbr', 'united kingdom', 'great britain', 'britain', 'england', 'scotland', 'wales', 'northern ireland' ], true );
}

/**
 * Route an order from its intake axes. Standard ONLY when:
 *   passport nationality = UK, residence = UK, status = citizen, purpose = tourist, no prior refusal.
 * Anything else -> manual_review (agent verifies the specific rules + quotes). Decision D6.
 */
function ukv_eligibility_evaluate( array $a ) {
	$nat     = $a['nationality'] ?? '';
	$res     = $a['residence_country'] ?? '';
	$status  = $a['residency_status'] ?? '';
	$purpose = $a['trip_purpose'] ?? 'tourist';
	$refusal = ! empty( $a['prior_refusal'] );
	if ( ukv_is_uk( $nat ) && ukv_is_uk( $res ) && 'citizen' === $status && 'tourist' === $purpose && ! $refusal ) {
		return 'standard';
	}
	return 'manual_review';
}

/** True when the order may proceed through the normal flow (standard, or a cleared manual-review). */
function ukv_order_is_cleared( $order_id ) {
	$e = get_post_meta( (int) $order_id, 'ukv_eligibility', true );
	return in_array( $e, [ 'standard', 'cleared' ], true );
}

/** Sanitise + store the intake axes on an order, then compute + store the eligibility lane. */
function ukv_eligibility_apply( $order_id, array $data ) {
	$order_id = (int) $order_id;
	if ( 'ukv_order' !== get_post_type( $order_id ) ) { return; }
	$text = [ 'nationality', 'residence_country', 'residency_status', 'residency_visa_expiry', 'trip_purpose',
		'visa_entries', 'applicant_name', 'guardian_name', 'dual_nationality' ];
	foreach ( $text as $k ) {
		if ( array_key_exists( $k, $data ) ) {
			update_post_meta( $order_id, 'ukv_' . $k, sanitize_text_field( (string) $data[ $k ] ) );
		}
	}
	foreach ( [ 'is_minor', 'prior_refusal', 'insurance_required' ] as $b ) {
		if ( array_key_exists( $b, $data ) ) {
			update_post_meta( $order_id, 'ukv_' . $b, ! empty( $data[ $b ] ) ? '1' : '' );
		}
	}
	$axes = [
		'nationality'      => (string) get_post_meta( $order_id, 'ukv_nationality', true ),
		'residence_country'=> (string) get_post_meta( $order_id, 'ukv_residence_country', true ),
		'residency_status' => (string) get_post_meta( $order_id, 'ukv_residency_status', true ),
		'trip_purpose'     => (string) ( get_post_meta( $order_id, 'ukv_trip_purpose', true ) ?: 'tourist' ),
		'prior_refusal'    => '1' === get_post_meta( $order_id, 'ukv_prior_refusal', true ),
	];
	$existing = get_post_meta( $order_id, 'ukv_eligibility', true );
	// Don't overwrite an agent decision (cleared/referred); only (re)compute standard/manual_review.
	if ( ! in_array( $existing, [ 'cleared', 'referred' ], true ) ) {
		update_post_meta( $order_id, 'ukv_eligibility', ukv_eligibility_evaluate( $axes ) );
	}
}

/** Helper for the funnel branch (Unit 6): is this combo the standard self-serve lane? */
function ukv_funnel_is_standard( $nationality, $residence, $status, $purpose = 'tourist' ) {
	return 'standard' === ukv_eligibility_evaluate( [
		'nationality' => $nationality, 'residence_country' => $residence,
		'residency_status' => $status, 'trip_purpose' => $purpose,
	] );
}
