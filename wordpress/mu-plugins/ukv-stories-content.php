<?php
/**
 * Plugin Name: UKV Smart Stories — Anonymised Content Engine (P13)
 * Desc: Turns resolved barriers into public problem->solution guides, with TWO mandatory
 *       redaction layers (PII + competitor) and a hard leak GATE that aborts on any finding.
 *       NEVER publishes — drafts only. Public function surface:
 *         ukv_redact_pii(), ukv_redact_competitor(), ukv_story_has_leak(), ukv_generate_story_draft().
 * Spec: docs/superpowers/specs/2026-06-12-smart-stories-design.md (P13)
 */
defined( 'ABSPATH' ) || exit;

/** Forbidden business-confidential terms (case-insensitive). Multi-word entries are matched as phrases. */
const UKV_COMPETITOR_TERMS = [
	'margin', 'markup', 'cost price', 'supplier', 'vendor', 'processing route',
	'route via', 'volume', 'playbook', 'commission', 'wholesale',
];

/**
 * Layer 1: strip ALL personal data from $text.
 * $known may contain: name, email, ref, passport, phone — those exact values are stripped too.
 * Returns the redacted text.
 */
function ukv_redact_pii( string $text, array $known = [] ): string {
	// Exact known values first (case-insensitive), longest-first so substrings don't unmask.
	$vals = array_filter( array_map( 'strval', array_values( $known ) ), static fn( $v ) => '' !== trim( $v ) );
	usort( $vals, static fn( $a, $b ) => strlen( $b ) <=> strlen( $a ) );
	foreach ( $vals as $v ) {
		$text = preg_replace( '/' . preg_quote( $v, '/' ) . '/i', '[removed]', $text );
	}

	// Email addresses.
	$text = preg_replace( '/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', '[removed]', $text );

	// Dates BEFORE numeric runs so their digit groups are not mistaken for phones/passports.
	//   12 June 2026 / 12 Jun 2026
	$text = preg_replace(
		'/\b\d{1,2}\s+(?:Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)\s+\d{4}\b/i',
		'a recent date',
		$text
	);
	//   YYYY-MM-DD
	$text = preg_replace( '/\b\d{4}-\d{2}-\d{2}\b/', 'a recent date', $text );
	//   DD/MM/YYYY
	$text = preg_replace( '#\b\d{1,2}/\d{1,2}/\d{4}\b#', 'a recent date', $text );

	// Passport-like tokens: 8-9 char alphanumerics that contain at least one digit (and at least one letter,
	// to avoid clobbering pure-number runs handled as phones). Mixed alnum is the passport signature.
	$text = preg_replace_callback(
		'/\b(?=[A-Z0-9]*[0-9])(?=[A-Z0-9]*[A-Z])[A-Z0-9]{8,9}\b/i',
		static fn() => '[removed]',
		$text
	);

	// Phone-like runs: optional leading +, then 7+ digits possibly separated by spaces/dashes/parens.
	$text = preg_replace_callback(
		'/\+?\d[\d\s\-()]{5,}\d/',
		static function ( $m ) {
			return preg_match_all( '/\d/', $m[0] ) >= 7 ? '[removed]' : $m[0];
		},
		$text
	);

	return $text;
}

/**
 * Layer 2: neutralise business-confidential content. For each forbidden term:
 *   - remove the whole sentence (split on ".") that contains it, OR
 *   - if no sentence boundary applies, replace the term + any adjacent number/percentage with "[redacted]".
 * A reader learns the PROBLEM and the FIX, never how the business operates.
 */
function ukv_redact_competitor( string $text ): string {
	$pattern = ukv_competitor_pattern();

	// Sentence-level pass: drop any sentence containing a forbidden term.
	$sentences = preg_split( '/(?<=\.)/', $text );
	$kept      = [];
	foreach ( $sentences as $s ) {
		if ( '' === trim( $s ) ) { continue; }
		if ( preg_match( $pattern, $s ) ) { continue; } // drop entire sentence
		$kept[] = $s;
	}
	$text = trim( implode( ' ', $kept ) );

	// Fallback: any residual term with no sentence boundary -> redact term + adjacent number/percentage.
	$text = preg_replace(
		'/\d+\s*%?\s*(?:' . ukv_competitor_alt() . ')|(?:' . ukv_competitor_alt() . ')\s*\d*\s*%?/i',
		'[redacted]',
		$text
	);

	return trim( $text );
}

/**
 * The GATE. Returns human-readable findings for anything that still looks like PII or any
 * forbidden competitor term still present. Empty array = clean (safe to publish/draft).
 */
function ukv_story_has_leak( string $text, array $known = [] ): array {
	$findings = [];

	if ( preg_match( '/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $text ) ) {
		$findings[] = 'email address present';
	}
	if ( preg_match_all( '/\+?\d[\d\s\-()]{5,}\d/', $text, $m ) ) {
		foreach ( $m[0] as $run ) {
			if ( preg_match_all( '/\d/', $run ) >= 7 ) { $findings[] = 'phone-like number present'; break; }
		}
	}
	if ( preg_match( '/\b(?=[A-Z0-9]*[0-9])(?=[A-Z0-9]*[A-Z])[A-Z0-9]{8,9}\b/i', $text ) ) {
		$findings[] = 'passport-like token present';
	}
	// Explicit known values must never survive.
	foreach ( $known as $v ) {
		$v = trim( (string) $v );
		if ( '' !== $v && preg_match( '/' . preg_quote( $v, '/' ) . '/i', $text ) ) {
			$findings[] = 'known personal value present: ' . $v;
		}
	}
	// Forbidden competitor terms.
	if ( preg_match_all( ukv_competitor_pattern(), $text, $cm ) ) {
		foreach ( array_unique( array_map( 'strtolower', $cm[0] ) ) as $term ) {
			$findings[] = 'competitor term present: ' . $term;
		}
	}

	return $findings;
}

/**
 * Build an anonymised problem->solution guide from a RESOLVED (or any) barrier and insert it as a DRAFT.
 * Aborts (returns 0, creates nothing) if the leak gate finds anything after both redaction layers.
 */
function ukv_generate_story_draft( int $barrier_id ): int {
	$barrier = get_post( $barrier_id );
	if ( ! $barrier || 'ukv_barrier' !== $barrier->post_type ) { return 0; }

	$nature   = (string) get_post_meta( $barrier_id, 'nature', true );
	$dest_slug = (string) get_post_meta( $barrier_id, 'destination', true );
	$guidance = (string) get_post_meta( $barrier_id, 'guidance', true );
	$dest     = $dest_slug ? ucwords( str_replace( '-', ' ', $dest_slug ) ) : 'their destination';

	$subject = "a UK traveller applying for {$dest}";
	$type_line = ( 'permanent' === $nature )
		? "This was a lasting requirement rather than a one-off — the kind of thing worth planning around from the start."
		: "This was a temporary hold-up: a short-lived issue that clears once normal service resumes.";

	$intro = "Here is a real situation faced by {$subject}, shared anonymously so others can prepare. "
		. "Names, contact details and case references have been removed — this is about the problem and the fix.";

	$what = "When applying for {$dest}, the traveller hit an unexpected obstacle. {$type_line}";

	$how = $guidance
		? "How it was handled: " . rtrim( $guidance, '.' ) . ". "
			. "If you are applying for {$dest} and see something similar, stay patient, keep your details to hand, and follow the official channel — these situations are usually recoverable."
		: "It was resolved by following the official channel and keeping the application details to hand. "
			. "If you are applying for {$dest} and see something similar, stay patient and follow the official guidance.";

	$raw_title = "What {$subject} learned about handling an unexpected barrier";

	$content = "<p>" . $intro . "</p>\n"
		. "<h2>What happened</h2>\n<p>" . $what . "</p>\n"
		. "<h2>How to handle it</h2>\n<p>" . $how . "</p>";

	// TWO mandatory redaction layers, then the GATE.
	$content = ukv_redact_competitor( ukv_redact_pii( $content ) );
	$title   = ukv_redact_competitor( ukv_redact_pii( $raw_title ) );

	$leaks = ukv_story_has_leak( $content );
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
	update_post_meta( $pid, 'rank_math_focus_keyword', strtolower( $dest ) . ' visa barrier' );

	return (int) $pid;
}

/* ----------------------------------------------------------------------------- helpers */

/** A regex (with delimiters + /i) matching any forbidden competitor term as a whole word/phrase. */
function ukv_competitor_pattern(): string {
	return '/\b(?:' . ukv_competitor_alt() . ')\b/i';
}

/** The inner alternation of forbidden terms (no delimiters), spaces tolerant of extra whitespace. */
function ukv_competitor_alt(): string {
	$parts = array_map(
		static fn( $t ) => str_replace( ' ', '\s+', preg_quote( $t, '/' ) ),
		UKV_COMPETITOR_TERMS
	);
	return implode( '|', $parts );
}
