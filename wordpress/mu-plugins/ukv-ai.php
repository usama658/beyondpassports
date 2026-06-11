<?php
/**
 * Plugin Name: UKV AI Assist (P15) — optional Claude polish layer
 * Desc: A thin, OPTIONAL Anthropic/Claude layer that polishes copy and proposes next-best-actions.
 *       Advisory only. Every caller falls back to existing rules templates when no key is set.
 *       Never auto-publishes; AI output destined for public content is re-checked by the leak gate.
 *       Public function surface:
 *         ukv_ai(), ukv_ai_polish_guidance(), ukv_ai_polish_content(), ukv_ai_next_best_action().
 * Depends: ukv-stories-content.php (ukv_story_has_leak, ukv_redact_pii), ukv-barriers.php, ukv-orders.php.
 * Spec: docs/superpowers/specs/2026-06-12-ai-assist-design.md (P15)
 */
defined( 'ABSPATH' ) || exit;

/**
 * The one gateway. Sends a system + user prompt to Claude and returns the text, or null.
 *
 * Null-safe by design: returns null with no key, on any WP_Error, any non-2xx response,
 * or any missing field. Never throws.
 *
 * Testability hook: a `ukv_ai_pre_response` filter runs FIRST (before the key check), so tests
 * can inject a mock response with no key and no HTTP call. Returning anything non-null short-circuits.
 *
 * @param string $system System prompt (brand/honesty rules etc).
 * @param string $user   User message (the content to act on).
 * @param array  $opts   Optional: 'model' (default claude-haiku-4-5), 'max_tokens' (default 400).
 * @return string|null   Claude's text, or null on any failure / no key.
 */
function ukv_ai( string $system, string $user, array $opts = [] ): ?string {
	// Mock/override hook — runs before everything so tests need no key and make no HTTP call.
	$pre = apply_filters( 'ukv_ai_pre_response', null, $system, $user, $opts );
	if ( null !== $pre ) {
		return is_string( $pre ) ? $pre : null;
	}

	$key = (string) get_option( 'ukv_anthropic_key', '' );
	if ( '' === $key ) {
		return null;
	}

	$model      = isset( $opts['model'] ) ? (string) $opts['model'] : 'claude-haiku-4-5';
	$max_tokens = isset( $opts['max_tokens'] ) ? (int) $opts['max_tokens'] : 400;

	$body = [
		'model'      => $model,
		'max_tokens' => $max_tokens,
		'system'     => $system,
		'messages'   => [
			[ 'role' => 'user', 'content' => $user ],
		],
	];

	$res = wp_remote_post(
		'https://api.anthropic.com/v1/messages',
		[
			'timeout' => 30,
			'headers' => [
				'x-api-key'         => $key,
				'anthropic-version' => '2023-06-01',
				'content-type'      => 'application/json',
			],
			'body'    => wp_json_encode( $body ),
		]
	);

	if ( is_wp_error( $res ) ) {
		return null;
	}

	$code = (int) wp_remote_retrieve_response_code( $res );
	if ( $code < 200 || $code >= 300 ) {
		return null;
	}

	$data = json_decode( (string) wp_remote_retrieve_body( $res ), true );
	$text = $data['content'][0]['text'] ?? null;

	return ( is_string( $text ) && '' !== $text ) ? $text : null;
}

/**
 * Brand/honesty system prompt shared by the client-facing polishers.
 * Independent service (not a government website); express speeds HANDLING not the government
 * decision; never guarantee approval.
 */
function ukv_ai_brand_rules(): string {
	return 'You are an editor for an independent UK visa support service. '
		. 'Rewrite the supplied text into plain, warm, on-brand client-facing prose. '
		. 'Honesty rules you MUST follow: we are an independent service, NOT a government website and not affiliated with any government; '
		. 'an express or priority option speeds up our handling and the application paperwork, it does NOT make the government decide faster or guarantee a faster decision; '
		. 'NEVER promise, guarantee, or imply a guaranteed approval or a specific outcome. '
		. 'Keep it concise. Do not invent facts, names, dates, prices, or case details that are not in the input. '
		. 'Return only the rewritten text, with no preamble, labels, or quotation marks.';
}

/**
 * Use-case 1: polish a barrier's `guidance` into on-brand client-facing prose.
 * Caller keeps the original template if this returns null.
 *
 * @param int $barrier_id A ukv_barrier post ID.
 * @return string|null    Polished guidance, or null (no key / AI failure / not a barrier).
 */
function ukv_ai_polish_guidance( int $barrier_id ): ?string {
	$post = get_post( $barrier_id );
	if ( ! $post || 'ukv_barrier' !== $post->post_type ) {
		return null;
	}
	$guidance = trim( (string) get_post_meta( $barrier_id, 'guidance', true ) );
	if ( '' === $guidance ) {
		return null;
	}

	return ukv_ai( ukv_ai_brand_rules(), $guidance, [ 'max_tokens' => 400 ] );
}

/**
 * Use-case 2: polish an ALREADY-ANONYMISED story/testimonial draft.
 *
 * SAFETY: input must already be post-redaction. After the AI returns, we re-run the leak GATE on
 * the polished text. If the gate finds ANYTHING (PII or competitor terms), we DISCARD the AI output
 * and return null so the caller keeps its clean rules draft. The AI can never re-introduce a leak.
 *
 * @param string $anonymised_text Redacted draft text.
 * @param string $type            A label for the kind of content (e.g. 'story', 'testimonial') for the prompt.
 * @return string|null            Polished text (re-gated clean), or null (no key / AI failure / leak detected).
 */
function ukv_ai_polish_content( string $anonymised_text, string $type ): ?string {
	$anonymised_text = trim( $anonymised_text );
	if ( '' === $anonymised_text ) {
		return null;
	}

	$type   = sanitize_text_field( $type );
	$system = ukv_ai_brand_rules()
		. ' This is an anonymised ' . ( '' !== $type ? $type : 'piece of content' ) . '. '
		. 'It has already had all personal details removed. You MUST NOT add back any names, emails, phone numbers, '
		. 'passport numbers, case references, prices, supplier names, or any internal business detail. '
		. 'If a detail was removed, leave it removed — do not guess or reinstate it.';

	$out = ukv_ai( $system, $anonymised_text, [ 'max_tokens' => 500 ] );
	if ( null === $out ) {
		return null;
	}

	// Re-gate: any finding means the polished text is unsafe — discard it.
	if ( ! empty( ukv_story_has_leak( $out ) ) ) {
		return null;
	}

	return $out;
}

/**
 * Use-case 3: propose ONE short next-best-action for an order.
 *
 * Builds a NON-PII summary: destination, tier, status, and the journey-note texts each passed
 * through ukv_redact_pii() before sending. Advisory only — a human acts on it.
 *
 * @param int $order_id A ukv_order post ID.
 * @return string|null  A short recommendation, or null (no key / AI failure / not an order).
 */
function ukv_ai_next_best_action( int $order_id ): ?string {
	$post = get_post( $order_id );
	if ( ! $post || 'ukv_order' !== $post->post_type ) {
		return null;
	}

	$dest   = (string) get_post_meta( $order_id, 'ukv_destination', true );
	$tier   = (string) get_post_meta( $order_id, 'ukv_tier', true );
	$status = (string) get_post_meta( $order_id, 'ukv_status', true );

	$dest_label = '' !== $dest ? ucwords( str_replace( '-', ' ', $dest ) ) : 'unknown destination';

	$lines = [
		'Destination: ' . $dest_label,
		'Tier: ' . ( '' !== $tier ? $tier : 'standard' ),
		'Status: ' . ( '' !== $status ? $status : 'unknown' ),
	];

	$journey = (array) get_post_meta( $order_id, 'ukv_journey', true );
	$notes   = [];
	foreach ( $journey as $n ) {
		$txt = isset( $n['text'] ) ? trim( (string) $n['text'] ) : '';
		if ( '' === $txt ) {
			continue;
		}
		// Defence-in-depth: strip any PII a staff member may have put in a note.
		$notes[] = '- ' . ukv_redact_pii( $txt );
	}
	if ( $notes ) {
		$lines[] = 'Journey notes:';
		$lines   = array_merge( $lines, $notes );
	}

	$summary = implode( "\n", $lines );

	$system = 'You are an operations adviser for an independent UK visa support service. '
		. 'Given a non-personal summary of one case, suggest ONE short, concrete next-best-action the team '
		. 'could take to improve the chance of a successful outcome and a happy client. '
		. 'Be honest: never imply we can speed up or influence the government decision, and never guarantee approval. '
		. 'Return a single short sentence, no preamble or labels.';

	return ukv_ai( $system, $summary, [ 'max_tokens' => 150 ] );
}

/* -------------------------------------------------------------------------
 * Admin settings: save the Anthropic key to the ukv_anthropic_key option.
 * Simple options section under Settings. AI Assist is OFF until a key is set.
 * ---------------------------------------------------------------------- */

add_action( 'admin_init', static function () {
	register_setting( 'ukv_ai_settings', 'ukv_anthropic_key', [
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => '',
	] );

	add_settings_section(
		'ukv_ai_section',
		'AI Assist (optional)',
		static function () {
			echo '<p>Paste an Anthropic API key to enable the optional Claude "AI Assist" polish layer. '
				. 'Leave blank to keep AI off — every feature falls back to the built-in rules templates. '
				. 'AI output is advisory and is never auto-published; public content is re-scanned by the leak gate.</p>';
			echo '<p><strong>Functions:</strong> <code>ukv_ai_polish_guidance($barrier_id)</code>, '
				. '<code>ukv_ai_polish_content($text, $type)</code>, <code>ukv_ai_next_best_action($order_id)</code>. '
				. 'Each returns the original/template when AI is unavailable.</p>';
		},
		'ukv_ai_settings'
	);

	add_settings_field(
		'ukv_anthropic_key',
		'Anthropic API key',
		static function () {
			$val  = (string) get_option( 'ukv_anthropic_key', '' );
			$mask = '' !== $val ? str_repeat( '•', 8 ) . substr( $val, -4 ) : '';
			echo '<input type="password" name="ukv_anthropic_key" value="' . esc_attr( $val ) . '" class="regular-text" autocomplete="off" placeholder="sk-ant-...">';
			if ( '' !== $mask ) {
				echo ' <span class="description">Current: ' . esc_html( $mask ) . '</span>';
			}
		},
		'ukv_ai_settings',
		'ukv_ai_section'
	);
} );

add_action( 'admin_menu', static function () {
	add_options_page(
		'UKV AI Assist',
		'UKV AI Assist',
		'manage_options',
		'ukv-ai-settings',
		static function () {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			echo '<div class="wrap"><h1>UKV AI Assist</h1><form method="post" action="options.php">';
			settings_fields( 'ukv_ai_settings' );
			do_settings_sections( 'ukv_ai_settings' );
			submit_button( 'Save key' );
			echo '</form></div>';
		}
	);
} );
