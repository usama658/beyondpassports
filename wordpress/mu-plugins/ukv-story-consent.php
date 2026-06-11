<?php
/**
 * Plugin Name: UKV Smart Stories — Consented Testimonials (P14)
 * Desc: The CONSENTED tier of Smart Stories. A richer first-person testimonial may only be drafted
 *       when the client has explicitly consented (hard gate). Consent is recorded by staff (meta box)
 *       or captured defensively at checkout. Reuses the P13 redaction + leak gate — NEVER reimplements
 *       redaction, and NEVER publishes (drafts only). Public function surface:
 *         ukv_set_story_consent(), ukv_has_story_consent(), ukv_generate_testimonial_draft().
 * Spec: docs/superpowers/specs/2026-06-12-smart-stories-design.md (P14)
 */
defined( 'ABSPATH' ) || exit;

/* ----------------------------------------------------------------- consent storage */

/** Record (or clear) story consent on an order. '1' = consented, '' = not. */
function ukv_set_story_consent( int $order_id, bool $consent ): void {
	update_post_meta( $order_id, 'ukv_story_consent', $consent ? '1' : '' );
}

/** True when the order's client has consented to an anonymised testimonial. */
function ukv_has_story_consent( int $order_id ): bool {
	return '1' === (string) get_post_meta( $order_id, 'ukv_story_consent', true );
}

/* ----------------------------------------------------------------- admin meta box */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ukv_story_consent', 'Story consent', 'ukv_story_consent_metabox', 'ukv_order', 'side', 'default' );
} );

function ukv_story_consent_metabox( $post ): void {
	wp_nonce_field( 'ukv_story_consent', 'ukv_story_consent_nonce' );
	$on = ukv_has_story_consent( (int) $post->ID );
	echo '<p><label style="display:inline;font-weight:600"><input type="checkbox" name="ukv_story_consent" value="1" '
		. checked( $on, true, false )
		. '> Client consented to an anonymised testimonial</label></p>';
	echo '<p style="color:#666;margin:4px 0 0">Tick once the client has agreed on a call/chat. No consent = no testimonial.</p>';
}

add_action( 'save_post_ukv_order', function ( $pid ) {
	if ( ! isset( $_POST['ukv_story_consent_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['ukv_story_consent_nonce'] ), 'ukv_story_consent' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	ukv_set_story_consent( (int) $pid, isset( $_POST['ukv_story_consent'] ) );
} );

/* ----------------------------------------------------------------- checkout consent hook */

/**
 * Defensive checkout capture: if the Forminator submission carried a truthy consent checkbox,
 * mark the matching order (by email) as consented. Must never fatal if fields are absent.
 * NOTE: live form-field IDs are finalised at production — we check the likely keys and guard everything.
 */
add_action( 'forminator_custom_form_after_stripe_charge', function ( $module, $field, $stripe_entry_data, $prepared_data, $field_data_array ) {
	$pd = is_array( $prepared_data ) ? $prepared_data : [];

	$truthy = static function ( $v ) {
		if ( is_array( $v ) ) { return ! empty( array_filter( $v ) ); }
		$v = is_string( $v ) ? strtolower( trim( $v ) ) : $v;
		return in_array( $v, [ '1', 'on', 'true', 'yes', true, 1 ], true ) || ( is_numeric( $v ) && (int) $v > 0 );
	};

	$consented = false;
	foreach ( [ 'checkbox-1', 'consent-1' ] as $k ) {
		if ( array_key_exists( $k, $pd ) && $truthy( $pd[ $k ] ) ) { $consented = true; break; }
	}
	if ( ! $consented ) { return; }

	$email = isset( $pd['email-1'] ) ? sanitize_email( (string) $pd['email-1'] ) : '';
	if ( '' === $email ) { return; }

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
	if ( ! empty( $matches ) ) {
		ukv_set_story_consent( (int) $matches[0], true );
	}
}, 30, 5 );

/* ----------------------------------------------------------------- consented testimonial draft */

/**
 * Build a richer, first-person anonymised testimonial from a CONSENTED order and insert it as a DRAFT.
 * Hard gates: no consent -> 0; any leak after both redaction layers -> 0 (creates nothing).
 */
function ukv_generate_testimonial_draft( int $order_id ): int {
	// HARD GATE 1: no consent, no testimonial.
	if ( ! ukv_has_story_consent( $order_id ) ) { return 0; }

	$order = get_post( $order_id );
	if ( ! $order || 'ukv_order' !== $order->post_type ) { return 0; }

	$g = static fn( $k ) => (string) get_post_meta( $order_id, $k, true );

	$dest_raw = $g( 'ukv_destination' );
	$dest     = $dest_raw ? ucwords( strtolower( $dest_raw ) ) : 'my destination';
	$tier     = $g( 'ukv_tier' );
	$tier_txt = $tier ? ucwords( strtolower( $tier ) ) : '';
	$status   = $g( 'ukv_status' );

	$outcome = ( 'won' === $status )
		? "my visa came through and the trip went ahead"
		: ( ( 'delivered' === $status )
			? "my visa was delivered in good time"
			: "everything was sorted out properly" );

	// Generalised paraphrase of the journey — keep only the shape, never the detail.
	$journey = (array) get_post_meta( $order_id, 'ukv_journey', true );
	$steps   = count( array_filter( $journey ) );
	$paraphrase = $steps > 0
		? "There were a few steps along the way and I was kept in the loop at each one"
		: "It was handled smoothly from start to finish";

	$known = [
		'name'     => $g( 'ukv_name' ),
		'email'    => $g( 'ukv_email' ),
		'ref'      => $g( 'ukv_order_ref' ),
		'passport' => $g( 'ukv_passport_number' ),
	];

	$tier_clause = $tier_txt ? " I went with the {$tier_txt} service" : " I used their service";

	$raw_title = "How I, a UK traveller, got my {$dest} visa sorted";

	$body  = "I am a UK traveller and I wanted to share my experience applying for {$dest}.";
	$body .= "{$tier_clause}, and {$outcome}. {$paraphrase}, so I always knew where things stood.";
	$body .= " I would happily recommend this to anyone applying for {$dest} — it took the stress out of the process.";

	$content = "<p>" . $body . "</p>";

	// TWO mandatory redaction layers over BOTH title and body, then the GATE.
	$content = ukv_redact_competitor( ukv_redact_pii( $content, $known ) );
	$title   = ukv_redact_competitor( ukv_redact_pii( $raw_title, $known ) );

	$leaks = ukv_story_has_leak( $content, $known );
	if ( ! empty( $leaks ) ) { return 0; } // ABORT — create nothing.

	$pid = wp_insert_post( [
		'post_type'    => 'post',
		'post_status'  => 'draft', // NEVER publish.
		'post_title'   => $title,
		'post_content' => $content,
	] );
	if ( ! $pid || is_wp_error( $pid ) ) { return 0; }

	update_post_meta( $pid, 'rank_math_title', $title );
	update_post_meta( $pid, 'rank_math_description', wp_trim_words( wp_strip_all_tags( $content ), 28, '…' ) );
	update_post_meta( $pid, 'rank_math_focus_keyword', strtolower( $dest ) . ' visa testimonial' );

	return (int) $pid;
}
