<?php
/**
 * Plugin Name: UKV AI Document Review (Smart Orders Hub — Phase 5)
 * Desc: Claude vision-style ADVISORY review of an order's uploaded passport/photo.
 *       Key-gated via the shared ukv_ai() gateway. Returns a structured pass/flag verdict
 *       stored on the order and shown as a badge in the admin Orders list + an edit-screen
 *       "Run AI doc review" button.
 *
 * COMPLIANCE — read before changing:
 *   This review is ADVISORY ONLY. It NEVER auto-rejects and NEVER changes ukv_status.
 *   A human reviewer must confirm every verdict before an application is submitted.
 *   The verdict assists the team ("we catch errors") — it does not decide.
 *
 * PRIVACY: the passport NUMBER (ukv_passport_number) is deliberately NOT sent to the AI —
 *   only the destination, travel date, the application name, and a document description.
 *
 * Depends: ukv-ai.php (ukv_ai + ukv_ai_pre_response mock filter), ukv-orders.php (ukv_order CPT).
 * Spec: docs/superpowers/specs/2026-06-12-smart-orders-hub-design.md (section "AI document review").
 */
defined( 'ABSPATH' ) || exit;

/**
 * The engine. Runs an advisory AI document review for one order and stores the verdict.
 *
 * @param int $order_id A ukv_order post ID.
 * @return array|null   Verdict array { pass, flags, ... } — or null when AI is unavailable
 *                       (no key AND no mock), or the id is not an order. Never throws.
 */
function ukv_doc_review_verdict( int $order_id ): ?array {
	$post = get_post( $order_id );
	if ( ! $post || 'ukv_order' !== $post->post_type ) {
		return null;
	}

	// No-op when AI is unavailable: no key AND no mock filter present.
	// The pre-response filter runs FIRST inside ukv_ai(), so a mock works with no key.
	$has_mock = ( null !== apply_filters( 'ukv_ai_pre_response', null, '', '', [] ) );
	if ( '' === (string) get_option( 'ukv_anthropic_key', '' ) && ! $has_mock ) {
		return null;
	}

	$dest        = (string) get_post_meta( $order_id, 'ukv_destination', true );
	$travel_date = (string) get_post_meta( $order_id, 'ukv_travel_date', true );
	$name        = (string) get_post_meta( $order_id, 'ukv_name', true );
	$dest_label  = '' !== $dest ? ucwords( str_replace( '-', ' ', $dest ) ) : 'the destination';

	// Required passport validity for this destination (if the Pods glue exposes it).
	// Guarded: the field may not be wired yet — default to the common 6-month rule.
	$validity_months = 6;
	if ( function_exists( 'ukv_dest_value' ) ) {
		// ukv_dest_value resolves a destination by SLUG; $dest is the display name ("Egypt") -> slugify.
		$dest_slug = function_exists( 'ukv_dest_slug' ) ? ukv_dest_slug( $dest ) : sanitize_title( $dest );
		$v = ukv_dest_value( $dest_slug, 'passport_validity_months' );
		if ( is_numeric( $v ) && (int) $v > 0 ) {
			$validity_months = (int) $v;
		}
	}

	// Document context. NOTE: real image bytes are out of scope for this text gateway —
	// we pass a TEXTUAL description placeholder. Wiring the actual passport/photo image
	// bytes into a vision request is a launch step (see spec: vision = claude-haiku-4-5).
	$docs     = array_filter( (array) get_post_meta( $order_id, 'ukv_documents', true ) );
	$doc_desc = $docs
		? count( $docs ) . ' uploaded document file(s) (passport bio page and/or photo). '
			. '[Image bytes not attached in this text-gateway build — describe checks structurally.]'
		: 'No documents are attached to this order yet.';

	$system = 'You are a meticulous document-review assistant for an independent UK visa support service. '
		. 'You ADVISE a human reviewer who makes the final decision — you never approve or reject anything. '
		. 'Check an applicant\'s passport bio page and photo against the destination\'s requirements and flag issues. '
		. 'You MUST reply with ONLY strict minified JSON, no preamble, in exactly this shape: '
		. '{"pass":true|false,"flags":[{"check":"expiry|name|photo|legibility","severity":"info|warn|fail","note":"short reason"}]}. '
		. 'Use "pass":true only when nothing needs human attention; otherwise "pass":false with one flag per issue.';

	$user = "Review this application's documents and return the JSON verdict.\n"
		. 'Applicant name (must match the passport): ' . ( '' !== $name ? $name : 'not provided' ) . "\n"
		. 'Destination: ' . $dest_label . "\n"
		. 'Travel date: ' . ( '' !== $travel_date ? $travel_date : 'not provided' ) . "\n"
		. 'Required passport validity from travel date: ' . $validity_months . " months.\n"
		. 'Documents: ' . $doc_desc . "\n"
		. "Checks to perform: (1) expiry — passport valid for the required months beyond the travel date; "
		. "(2) name — passport name matches the applicant name; "
		. "(3) photo — meets the destination photo spec (plain background, framing); "
		. "(4) legibility — the document is a clear, correct passport bio page.";

	$text = ukv_ai( $system, $user, [ 'max_tokens' => 500 ] );

	if ( null === $text ) {
		return null; // AI dropped out mid-call (e.g. HTTP error) — treat as "not run".
	}

	$parsed = json_decode( $text, true );
	if ( is_array( $parsed ) && array_key_exists( 'pass', $parsed ) ) {
		$verdict = [
			'pass'  => is_bool( $parsed['pass'] ) ? $parsed['pass'] : null,
			'flags' => isset( $parsed['flags'] ) && is_array( $parsed['flags'] ) ? array_values( $parsed['flags'] ) : [],
		];
	} else {
		// Invalid / unexpected JSON — never throw; keep the raw text for the human.
		$verdict = [ 'pass' => null, 'flags' => [], 'raw' => $text ];
	}

	$verdict['reviewed_at'] = time();

	// Store the advisory verdict. NEVER touch ukv_status — a human decides.
	update_post_meta( $order_id, 'ukv_doc_review', $verdict );

	return $verdict;
}

/**
 * Build a small HTML badge from the stored verdict.
 *
 * @param int $order_id A ukv_order post ID.
 * @return string       esc_html'd badge markup. Grey "Not reviewed" when nothing stored.
 */
function ukv_doc_review_badge( int $order_id ): string {
	$v = get_post_meta( $order_id, 'ukv_doc_review', true );
	if ( ! is_array( $v ) || ! array_key_exists( 'pass', $v ) ) {
		return '<span style="display:inline-block;padding:1px 8px;border-radius:9px;background:#e2e4e7;color:#50575e;font-size:11px">'
			. esc_html__( 'Not reviewed', 'ukv' ) . '</span>';
	}

	if ( true === $v['pass'] ) {
		return '<span style="display:inline-block;padding:1px 8px;border-radius:9px;background:#d6f0df;color:#0f7b3f;font-size:11px">'
			. esc_html__( 'Docs OK', 'ukv' ) . '</span>';
	}

	$n = is_array( $v['flags'] ?? null ) ? count( $v['flags'] ) : 0;
	$n = max( 1, $n ); // pass!==true always means at least a review-needed state.
	/* translators: %d: number of advisory flags raised by the AI doc review. */
	$label = sprintf( _n( 'Review: %d flag', 'Review: %d flags', $n, 'ukv' ), $n );

	return '<span style="display:inline-block;padding:1px 8px;border-radius:9px;background:#fcf0d6;color:#996800;font-size:11px">'
		. esc_html( $label ) . '</span>';
}

/* -------------------------------------------------------------------------
 * Admin: badge column on the Orders list (new id ukv_docai — no clash).
 * ---------------------------------------------------------------------- */
add_filter( 'manage_ukv_order_posts_columns', static function ( $cols ) {
	$new = [];
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'ukv_status' === $k ) {
			$new['ukv_docai'] = 'AI Docs';
		}
	}
	if ( ! isset( $new['ukv_docai'] ) ) {
		$new['ukv_docai'] = 'AI Docs'; // fallback if the status column is renamed/removed.
	}
	return $new;
} );

add_action( 'manage_ukv_order_posts_custom_column', static function ( $col, $pid ) {
	if ( 'ukv_docai' === $col ) {
		echo wp_kses_post( ukv_doc_review_badge( (int) $pid ) );
	}
}, 10, 2 );

/* -------------------------------------------------------------------------
 * Admin: "Run AI doc review" meta box on the order edit screen.
 * Posts to admin-post.php (nonce-protected) and runs the advisory verdict.
 * ---------------------------------------------------------------------- */
add_action( 'add_meta_boxes', static function () {
	add_meta_box( 'ukv_doc_review', 'AI Document Review (advisory)', 'ukv_doc_review_metabox', 'ukv_order', 'side', 'default' );
} );

function ukv_doc_review_metabox( $post ) {
	$pid     = (int) $post->ID;
	$verdict = get_post_meta( $pid, 'ukv_doc_review', true );
	$key_set = '' !== (string) get_option( 'ukv_anthropic_key', '' );

	echo '<p>' . ukv_doc_review_badge( $pid ) . '</p>'; // phpcs:ignore — badge is pre-escaped.

	if ( is_array( $verdict ) ) {
		if ( ! empty( $verdict['reviewed_at'] ) ) {
			echo '<p class="description">' . esc_html__( 'Last reviewed:', 'ukv' ) . ' '
				. esc_html( gmdate( 'Y-m-d H:i', (int) $verdict['reviewed_at'] ) ) . ' UTC</p>';
		}
		if ( ! empty( $verdict['flags'] ) && is_array( $verdict['flags'] ) ) {
			echo '<ul style="margin-left:1em;list-style:disc">';
			foreach ( $verdict['flags'] as $f ) {
				$sev   = isset( $f['severity'] ) ? (string) $f['severity'] : 'info';
				$check = isset( $f['check'] ) ? (string) $f['check'] : '';
				$note  = isset( $f['note'] ) ? (string) $f['note'] : '';
				$color = 'fail' === $sev ? '#c00' : ( 'warn' === $sev ? '#996800' : '#50575e' );
				echo '<li style="color:' . esc_attr( $color ) . '"><strong>' . esc_html( $check ) . ':</strong> ' . esc_html( $note ) . '</li>';
			}
			echo '</ul>';
		}
		if ( array_key_exists( 'pass', $verdict ) && null === $verdict['pass'] ) {
			echo '<p class="description">' . esc_html__( 'AI reply could not be parsed — review the documents manually.', 'ukv' ) . '</p>';
		}
	}

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="ukv_run_doc_review">';
	echo '<input type="hidden" name="order_id" value="' . esc_attr( (string) $pid ) . '">';
	wp_nonce_field( 'ukv_run_doc_review_' . $pid, 'ukv_doc_review_nonce' );
	submit_button( __( 'Run AI doc review', 'ukv' ), 'secondary', 'submit', false );
	echo '</form>';

	if ( ! $key_set ) {
		echo '<p class="description">' . esc_html__( 'Add an Anthropic key under Settings → UKV AI Assist to enable this.', 'ukv' ) . '</p>';
	}
	echo '<p class="description">' . esc_html__( 'Advisory only — a human confirms before submission. This never changes the order status.', 'ukv' ) . '</p>';
}

add_action( 'admin_post_ukv_run_doc_review', static function () {
	$pid = isset( $_POST['order_id'] ) ? (int) $_POST['order_id'] : 0;
	if ( ! $pid || ! current_user_can( 'edit_post', $pid ) ) {
		wp_die( esc_html__( 'Not allowed.', 'ukv' ) );
	}
	check_admin_referer( 'ukv_run_doc_review_' . $pid, 'ukv_doc_review_nonce' );

	$verdict = ukv_doc_review_verdict( $pid );
	$flag    = ( null === $verdict ) ? 'notrun' : 'done';

	wp_safe_redirect( add_query_arg(
		[ 'ukv_docreview' => $flag ],
		get_edit_post_link( $pid, 'redirect' )
	) );
	exit;
} );

add_action( 'admin_notices', static function () {
	if ( empty( $_GET['ukv_docreview'] ) ) {
		return;
	}
	$flag = sanitize_key( wp_unslash( $_GET['ukv_docreview'] ) );
	if ( 'done' === $flag ) {
		echo '<div class="notice notice-success is-dismissible"><p>'
			. esc_html__( 'AI document review complete (advisory). The status was not changed.', 'ukv' ) . '</p></div>';
	} elseif ( 'notrun' === $flag ) {
		echo '<div class="notice notice-warning is-dismissible"><p>'
			. esc_html__( 'AI document review did not run — add an Anthropic key under Settings → UKV AI Assist.', 'ukv' ) . '</p></div>';
	}
} );
