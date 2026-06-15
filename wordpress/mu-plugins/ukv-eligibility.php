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

/** Human label for an eligibility lane key. */
function ukv_eligibility_lane_label( $lane ) {
	$map = [
		'standard'      => 'Standard',
		'manual_review' => 'Manual review',
		'cleared'       => 'Cleared',
		'referred'      => 'Referred',
	];
	return $map[ (string) $lane ] ?? (string) $lane;
}

/* =========================================================================
 * Unit 2 — Eligibility meta box (agent Clear / Refer)
 * ====================================================================== */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ukv_eligibility', 'Eligibility', 'ukv_eligibility_metabox', 'ukv_order', 'side', 'default' );
} );

/**
 * Render the Eligibility meta box: the captured intake axes + the current lane,
 * plus a nonce-protected Clear / Refer action with a free-text note.
 *
 * @param WP_Post $post
 */
function ukv_eligibility_metabox( $post ) {
	$pid = (int) $post->ID;
	wp_nonce_field( 'ukv_eligibility_' . $pid, 'ukv_eligibility_nonce' );

	$g = static function ( $k ) use ( $pid ) {
		return (string) get_post_meta( $pid, $k, true );
	};

	$lane      = $g( 'ukv_eligibility' );
	$lane_lbl  = ukv_eligibility_lane_label( $lane );
	$note      = $g( 'ukv_eligibility_note' );
	$is_minor  = '1' === $g( 'ukv_is_minor' );
	$refusal   = '1' === $g( 'ukv_prior_refusal' );

	$status_lbl = function ( $v ) {
		return isset( UKV_RESIDENCY_STATUS[ $v ] ) ? UKV_RESIDENCY_STATUS[ $v ] : $v;
	};
	$purpose_lbl = function ( $v ) {
		return isset( UKV_TRIP_PURPOSE[ $v ] ) ? UKV_TRIP_PURPOSE[ $v ] : $v;
	};

	$badge_colour = [
		'standard'      => '#0f7b3f',
		'cleared'       => '#0f7b3f',
		'manual_review' => '#b8860b',
		'referred'      => '#c00',
	];
	$colour = $badge_colour[ $lane ] ?? '#555';

	echo '<p style="margin:0 0 8px"><strong>Lane:</strong> ';
	echo '<span style="font-weight:700;color:' . esc_attr( $colour ) . '">' . esc_html( $lane_lbl ) . '</span></p>';

	$axes = [
		'Nationality'      => $g( 'ukv_nationality' ),
		'Residence'        => $g( 'ukv_residence_country' ),
		'Residency status' => $status_lbl( $g( 'ukv_residency_status' ) ),
		'Trip purpose'     => $purpose_lbl( $g( 'ukv_trip_purpose' ) ),
		'Prior refusal'    => $refusal ? 'Yes' : 'No',
		'Dual nationality' => $g( 'ukv_dual_nationality' ),
		'Is minor'         => $is_minor ? 'Yes' : 'No',
	];

	echo '<table style="width:100%;font-size:12px;border-collapse:collapse">';
	foreach ( $axes as $label => $value ) {
		$value = trim( (string) $value );
		echo '<tr><td style="padding:2px 6px 2px 0;color:#555;white-space:nowrap">' . esc_html( $label ) . '</td>';
		echo '<td style="padding:2px 0">' . ( '' === $value ? '<em style="color:#999">—</em>' : esc_html( $value ) ) . '</td></tr>';
	}
	echo '</table>';

	echo '<hr>';
	echo '<p><label for="ukv_eligibility_note"><strong>Decision note</strong></label><br>';
	echo '<textarea id="ukv_eligibility_note" name="ukv_eligibility_note" rows="3" style="width:100%" placeholder="Why you are clearing / referring (logged to the journey)">' . esc_textarea( $note ) . '</textarea></p>';

	echo '<p><label for="ukv_eligibility_action"><strong>Action</strong></label><br>';
	echo '<select id="ukv_eligibility_action" name="ukv_eligibility_action" style="width:100%">';
	echo '<option value="">— No change —</option>';
	echo '<option value="cleared">Clear (allow through the pipeline)</option>';
	echo '<option value="referred">Refer (escalate / hold)</option>';
	echo '</select></p>';
	echo '<p style="color:#555;font-size:11px">Choosing an action then saving sets the lane and logs an audit note to the Lead Journey.</p>';
}

/**
 * Save the agent Clear / Refer decision. Nonce + capability checked.
 * Sets ukv_eligibility (cleared|referred), stores ukv_eligibility_note, and
 * appends a journey note "Eligibility cleared/referred: {note}".
 */
add_action( 'save_post_ukv_order', function ( $pid ) {
	$pid = (int) $pid;
	if ( ! isset( $_POST['ukv_eligibility_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ukv_eligibility_nonce'] ) ), 'ukv_eligibility_' . $pid )
	) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( wp_is_post_revision( $pid ) ) { return; }
	if ( ! current_user_can( 'edit_post', $pid ) ) { return; }

	$action = isset( $_POST['ukv_eligibility_action'] ) ? sanitize_text_field( wp_unslash( $_POST['ukv_eligibility_action'] ) ) : '';
	$note   = isset( $_POST['ukv_eligibility_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['ukv_eligibility_note'] ) ) : '';

	// Always persist the note field (lets an agent edit the note without re-deciding).
	update_post_meta( $pid, 'ukv_eligibility_note', $note );

	if ( ! in_array( $action, [ 'cleared', 'referred' ], true ) ) {
		return; // no decision made this save
	}

	$existing = (string) get_post_meta( $pid, 'ukv_eligibility', true );
	update_post_meta( $pid, 'ukv_eligibility', $action );

	// Only log when the decision actually changes (avoid duplicate notes on every save).
	if ( $action !== $existing ) {
		$verb      = ( 'cleared' === $action ) ? 'cleared' : 'referred';
		$journey   = (array) get_post_meta( $pid, 'ukv_journey', true );
		$u         = wp_get_current_user();
		$journey[] = [
			'date'    => gmdate( 'Y-m-d H:i' ),
			'agent'   => ( $u && $u->display_name ) ? $u->display_name : 'agent',
			'channel' => 'internal',
			'text'    => sprintf( 'Eligibility %s: %s', $verb, $note ),
		];
		update_post_meta( $pid, 'ukv_journey', $journey );
	}
}, 10 );

/* =========================================================================
 * Unit 3 — Eligibility gate (runs alongside the stage gates)
 * ====================================================================== */

/**
 * Block a non-cleared manual_review order from advancing past 'paid'.
 *
 * If the order is NOT cleared (ukv_order_is_cleared() === false) AND the
 * attempted status is anything other than 'paid', this REVERTS ukv_status to
 * the previous stage (ukv_status_last, falling back to 'paid'), sets an
 * admin-notice transient, and logs a journey note.
 *
 * Standard / cleared orders are never blocked here.
 *
 * @param int    $order_id
 * @param string $attempted_status the value ukv_status was just set to.
 * @return bool TRUE when blocked (and reverted), FALSE otherwise.
 */
function ukv_eligibility_gate_enforce( int $order_id, string $attempted_status ): bool {
	$order_id         = (int) $order_id;
	$attempted_status = (string) $attempted_status;

	// Cleared / standard orders are never blocked by eligibility.
	if ( ukv_order_is_cleared( $order_id ) ) {
		return false;
	}

	// 'paid' is the entry stage — the order may sit there. Anything past it is blocked.
	if ( 'paid' === $attempted_status ) {
		return false;
	}

	// REVERT to the previous valid stage, or 'paid' if none recorded.
	$prev      = (string) get_post_meta( $order_id, 'ukv_status_last', true );
	$revert_to = ( '' !== $prev && $prev !== $attempted_status ) ? $prev : 'paid';
	update_post_meta( $order_id, 'ukv_status', $revert_to );

	// Admin notice (rendered escaped on the next admin page load).
	$uid = get_current_user_id();
	if ( $uid ) {
		set_transient( 'ukv_eligibility_block_' . $uid, 'Eligibility not cleared — order kept at its previous stage.', 60 );
	}

	// Journey audit note.
	$journey   = (array) get_post_meta( $order_id, 'ukv_journey', true );
	$journey[] = [
		'date'    => gmdate( 'Y-m-d H:i' ),
		'agent'   => 'system',
		'channel' => 'internal',
		'text'    => 'Blocked: eligibility not cleared.',
	];
	update_post_meta( $order_id, 'ukv_journey', $journey );

	return true;
}

/**
 * Eligibility gate hook. Priority 10 — alongside the stage gates. Guards
 * autosave / revisions. Only acts on a real transition to a stage past 'paid'.
 */
add_action( 'save_post_ukv_order', function ( $order_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( wp_is_post_revision( $order_id ) ) { return; }

	$new  = (string) get_post_meta( $order_id, 'ukv_status', true );
	$last = (string) get_post_meta( $order_id, 'ukv_status_last', true );

	if ( '' === $new || $new === $last ) { return; } // no real transition
	if ( 'paid' === $new ) { return; }               // entry stage is always allowed

	ukv_eligibility_gate_enforce( (int) $order_id, $new );
}, 10 );

/** Render the eligibility block reason to the agent as a dismissible admin notice. */
add_action( 'admin_notices', function () {
	$uid = get_current_user_id();
	$msg = $uid ? get_transient( 'ukv_eligibility_block_' . $uid ) : false;
	if ( empty( $msg ) ) { return; }
	delete_transient( 'ukv_eligibility_block_' . $uid );
	echo '<div class="notice notice-error is-dismissible"><p><strong>' . esc_html__( 'Stage move blocked.', 'ukv' ) . '</strong> ';
	echo esc_html( (string) $msg ) . '</p></div>';
} );
