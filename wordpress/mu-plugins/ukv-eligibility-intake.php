<?php
/**
 * Plugin Name: UKV Eligibility Intake Capture (Unit 4)
 * Desc: Reads the eligibility intake axes captured on the Apply Forminator form (#299) at checkout and
 *       feeds them to the eligibility router (ukv-eligibility.php). Mirrors ukv-apply-intake.php exactly:
 *       hooks the SAME action `forminator_custom_form_after_stripe_charge` at a LATER priority (25) so the
 *       ukv_order created by ukv-hubspot.php already exists, matches the most-recent ukv_order by ukv_email,
 *       and NEVER fatals on missing fields (every field is optional / guarded). It NEVER edits
 *       ukv-hubspot.php or ukv-eligibility.php.
 *
 *       Axes read (all optional, guarded) and passed to ukv_eligibility_apply():
 *         nationality, residence_country, residency_status, trip_purpose, prior_refusal, applicant_name.
 *       ukv_eligibility_apply() sanitises + stores them as ukv_-prefixed meta and computes ukv_eligibility
 *       (standard vs manual_review).
 *
 *       Public surface:
 *         ukv_eligibility_intake_from_prepared( array $prepared_data ): array — testable mapper
 *           (prepared_data -> axes array), so tests need not run the full Stripe-charge hook.
 *
 *       AUTOMATION NOTE (operator launch task): the live Apply Forminator form #299 must have the
 *       eligibility intake fields added in the Forminator form builder for this capture to do anything.
 *       This plugin probes the LIKELY Forminator field keys (multiple fallbacks) and silently no-ops for
 *       any axis that is absent. Until the operator adds the fields to #299, the capture is a harmless
 *       no-op. Field key candidates (operator should confirm / wire the actual #299 keys):
 *         nationality       <- select-2 | text-2 | nationality
 *         residence_country <- select-3 | residence
 *         residency_status  <- select-4 | residency_status
 *         trip_purpose      <- select-5 | purpose
 *         prior_refusal     <- checkbox-2 | prior_refusal   (checkbox / truthy)
 *         applicant_name    <- text-3 | applicant_name
 */
defined( 'ABSPATH' ) || exit;

/**
 * Testable core mapper: extract the eligibility axes from a Forminator prepared_data array.
 * Probes multiple candidate field keys per axis (best-effort until #299 keys are finalised), handles
 * array-wrapped Forminator values, and OMITS any axis whose fields are all absent/empty so the router
 * never stores bogus values for fields the operator hasn't added yet.
 *
 * @param array $prepared_data Forminator prepared_data (field key => value).
 * @return array Axes for ukv_eligibility_apply(): only the keys actually present are included.
 *               prior_refusal is included as a bool only when a candidate key is present.
 */
function ukv_eligibility_intake_from_prepared( array $prepared_data ): array {
	$pd = $prepared_data;

	// First non-empty scalar value among the candidate keys (handles array-wrapped values).
	$first = static function ( array $keys ) use ( $pd ) {
		foreach ( $keys as $k ) {
			if ( ! array_key_exists( $k, $pd ) ) { continue; }
			$v = $pd[ $k ];
			if ( is_array( $v ) ) { $v = reset( $v ); }
			$v = is_scalar( $v ) ? trim( (string) $v ) : '';
			if ( '' !== $v ) { return $v; }
		}
		return null; // null => no candidate key present/non-empty.
	};

	// Whether ANY candidate key is present (regardless of truthiness), and the resolved truthiness.
	$bool = static function ( array $keys ) use ( $pd ) {
		$present = false;
		$truthy  = false;
		foreach ( $keys as $k ) {
			if ( ! array_key_exists( $k, $pd ) ) { continue; }
			$present = true;
			$v = $pd[ $k ];
			if ( is_array( $v ) ) { if ( ! empty( array_filter( $v ) ) ) { $truthy = true; } continue; }
			$s = is_string( $v ) ? strtolower( trim( $v ) ) : $v;
			if ( in_array( $s, [ '1', 'on', 'true', 'yes', true, 1 ], true ) || ( is_numeric( $s ) && (int) $s > 0 ) ) {
				$truthy = true;
			}
		}
		return [ $present, $truthy ];
	};

	$axes = [];

	$map = [
		'nationality'       => [ 'select-2', 'text-2', 'nationality' ],
		'residence_country' => [ 'select-3', 'residence' ],
		'residency_status'  => [ 'select-4', 'residency_status' ],
		'trip_purpose'      => [ 'select-5', 'purpose' ],
		'applicant_name'    => [ 'text-3', 'applicant_name' ],
	];
	foreach ( $map as $axis => $keys ) {
		$val = $first( $keys );
		if ( null !== $val ) { $axes[ $axis ] = $val; }
	}

	[ $refusal_present, $refusal_truthy ] = $bool( [ 'checkbox-2', 'prior_refusal' ] );
	if ( $refusal_present ) { $axes['prior_refusal'] = $refusal_truthy; }

	return $axes;
}

/**
 * Checkout capture: pull the eligibility axes out of the Stripe-charge prepared_data and feed them to
 * the eligibility router for the most-recent ukv_order matching the submitted email. Hooked LATER
 * (priority 25) than ukv-hubspot.php so the order already exists. Guarded throughout — never fatal.
 */
add_action( 'forminator_custom_form_after_stripe_charge', function ( $module, $field, $stripe_entry_data, $prepared_data, $field_data_array ) {
	if ( ! function_exists( 'ukv_eligibility_apply' ) ) { return; }

	$pd = is_array( $prepared_data ) ? $prepared_data : [];

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

	$axes = ukv_eligibility_intake_from_prepared( $pd );
	if ( empty( $axes ) ) { return; } // nothing captured -> harmless no-op until #299 fields exist.

	ukv_eligibility_apply( $order_id, $axes );
}, 25, 5 );
