<?php
/**
 * Plugin Name: UKV Feedback Loop (Outcome → requirements suggestions — Gap #76)
 * Desc: Closes the loop between rejection outcomes and the requirements/checklist.
 *       Reads aggregate rejection analytics (ukv_rejection_stats) and, for every
 *       destination + reason that recurs at or above a threshold, surfaces a concrete
 *       preventive suggestion in a dashboard widget. Read-only / advisory — a human
 *       reviews and acts on the suggestions.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Build improvement suggestions from recurring rejection patterns.
 *
 * Reads ukv_rejection_stats() and, for each (destination, reason) pair whose count
 * meets the threshold, maps the reason to a concrete preventive action.
 *
 * @param int $threshold Minimum recurrence count for a pattern to be surfaced (default 2).
 * @return array<int,array{destination:string,reason:string,reason_label:string,count:int,suggestion:string}>
 *         Sorted by count descending.
 */
function ukv_feedback_suggestions( int $threshold = 2 ): array {
	if ( ! function_exists( 'ukv_rejection_stats' ) ) {
		return [];
	}
	if ( $threshold < 1 ) {
		$threshold = 1;
	}

	$stats = ukv_rejection_stats();
	$by_dest = isset( $stats['by_destination'] ) && is_array( $stats['by_destination'] )
		? $stats['by_destination']
		: [];

	$suggestions = [];

	foreach ( $by_dest as $dest => $reasons ) {
		if ( ! is_array( $reasons ) ) {
			continue;
		}
		foreach ( $reasons as $reason_key => $count ) {
			$count = (int) $count;
			if ( $count < $threshold ) {
				continue;
			}

			$label = ( defined( 'UKV_REJECTION_REASONS' ) && isset( UKV_REJECTION_REASONS[ $reason_key ] ) )
				? UKV_REJECTION_REASONS[ $reason_key ]
				: (string) $reason_key;

			$suggestions[] = [
				'destination'  => (string) $dest,
				'reason'       => (string) $reason_key,
				'reason_label' => $label,
				'count'        => $count,
				'suggestion'   => ukv_feedback_suggestion_text( (string) $reason_key, (string) $dest ),
			];
		}
	}

	usort( $suggestions, static function ( $a, $b ) {
		return $b['count'] <=> $a['count'];
	} );

	return $suggestions;
}

/**
 * Map a rejection reason key to a concrete preventive action for a destination.
 *
 * @param string $reason_key One of UKV_REJECTION_REASONS' keys (falls back to a generic prompt).
 * @param string $dest_slug  Destination slug (rendered as a human label inside the text).
 */
function ukv_feedback_suggestion_text( string $reason_key, string $dest_slug ): string {
	$dest = ucwords( str_replace( '-', ' ', $dest_slug ) );
	if ( '' === trim( $dest ) ) {
		$dest = 'this destination';
	}

	switch ( $reason_key ) {
		case 'passport_validity':
			return sprintf( 'Tighten the %s checklist: verify passport validity up front and flag short validity before submission.', $dest );
		case 'doc_quality':
			return sprintf( 'Add a clearer document-photo example to the %s requirements; pre-check scans on intake.', $dest );
		case 'eligibility':
			return sprintf( 'Add an eligibility pre-screen question for %s before payment.', $dest );
		case 'portal_error':
			return sprintf( 'Note the %s portal issue; add a resubmit/retry step.', $dest );
		case 'customer_withdrew':
			return sprintf( 'Review %s pricing/expectations messaging to reduce withdrawals.', $dest );
		case 'other':
			return sprintf( "Review recent %s 'other' rejections for a new pattern.", $dest );
		default:
			return sprintf( 'Review recurring %s rejections (%s) for a preventive fix.', $dest, $reason_key );
	}
}

/* -------------------------------------------------------------------------
 * Dashboard widget: improvement suggestions from rejection outcomes.
 * ---------------------------------------------------------------------- */
add_action( 'wp_dashboard_setup', function () {
	wp_add_dashboard_widget( 'ukv_feedback_loop', 'UKV — Improvement suggestions', 'ukv_feedback_widget' );
} );

function ukv_feedback_widget() {
	$suggestions = ukv_feedback_suggestions();

	if ( empty( $suggestions ) ) {
		echo '<p style="color:#555">No recurring rejection patterns yet.</p>';
		return;
	}

	echo '<p style="color:#555;font-size:11px;margin-top:0">Recurring rejection causes (advisory — a human reviews and acts).</p>';
	echo '<ul style="margin:0 0 0 1em">';
	foreach ( $suggestions as $s ) {
		$dest = ucwords( str_replace( '-', ' ', (string) $s['destination'] ) );
		echo '<li style="margin-bottom:6px">'
			. '<strong>' . esc_html( $dest ) . '</strong> &middot; '
			. esc_html( (string) $s['reason_label'] ) . ' &middot; '
			. '<strong>' . (int) $s['count'] . '</strong><br>'
			. '<span style="color:#333">' . esc_html( (string) $s['suggestion'] ) . '</span>'
			. '</li>';
	}
	echo '</ul>';
}
