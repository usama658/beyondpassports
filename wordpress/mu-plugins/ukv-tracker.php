<?php
/**
 * Plugin Name: UKV Tracker (public customer status tracker)
 * Desc: Privacy-critical public application tracker. [ukv_tracker] shortcode + /track page.
 *       Renders ONLY client-safe fields; never leaks journey/agent/risk/passport/email/other orders.
 *       Lookups are non-enumerating (a failed lookup is indistinguishable from a missing ref).
 */
defined( 'ABSPATH' ) || exit;

// Client-facing friendly status labels (NOT the internal admin labels).
const UKV_TRACKER_LABELS = [
	'paid'             => 'Payment received',
	'awaiting_docs'    => 'Awaiting your documents',
	'doc_review'       => 'In document review',
	'submitted'        => 'Submitted to authorities',
	'awaiting_decision'=> 'Awaiting a decision',
	'delivered'        => 'Delivered',
	'won'              => 'Approved',
	'rejected'         => 'Decision received — please contact us',
	'refunded'         => 'Refunded',
];

// Ordered progress pipeline (won counts as delivered).
const UKV_TRACKER_PIPELINE = [ 'paid', 'awaiting_docs', 'doc_review', 'submitted', 'awaiting_decision', 'delivered' ];

// Short "next step" line per status.
const UKV_TRACKER_NEXT = [
	'paid'             => 'Next: we will request the documents we need from you shortly.',
	'awaiting_docs'    => 'Next: please send the documents we have requested so we can proceed.',
	'doc_review'       => 'Next: our team is checking your documents — no action needed right now.',
	'submitted'        => 'Next: your application is with the authorities — we are monitoring it for you.',
	'awaiting_decision'=> 'Next: a decision is pending — we will notify you as soon as we hear back.',
	'delivered'        => 'Next: your application is complete. Please contact us if you need anything.',
	'won'              => 'Next: your application was approved. Please contact us if you need anything.',
	'rejected'         => 'Next: please contact us — we will talk you through your options.',
	'refunded'         => 'Next: your refund has been processed. Please contact us with any questions.',
];

/**
 * Look up an order by reference + email.
 * Returns the order ID where ukv_order_ref === trimmed ref AND
 * strtolower(ukv_email) === strtolower(trimmed email). No match -> null.
 */
function ukv_tracker_lookup( $ref, $email ) {
	$ref   = trim( (string) $ref );
	$email = strtolower( trim( (string) $email ) );
	if ( '' === $ref || '' === $email ) { return null; }

	$ids = get_posts( [
		'post_type'   => 'ukv_order',
		'post_status' => 'publish',
		'fields'      => 'ids',
		'numberposts' => -1,
		'meta_query'  => [ [ 'key' => 'ukv_order_ref', 'value' => $ref ] ],
	] );

	foreach ( $ids as $oid ) {
		if ( strtolower( trim( (string) get_post_meta( $oid, 'ukv_email', true ) ) ) === $email ) {
			return (int) $oid;
		}
	}
	return null;
}

/**
 * CLIENT-SAFE render of one order. Returns HTML.
 * Shows ONLY: ref, destination, tier, friendly status, progress bar, next step,
 * open client-facing barriers, compliance footer. NEVER emits internal fields.
 */
function ukv_tracker_view( $order_id ) {
	$order_id = (int) $order_id;
	$ref    = (string) get_post_meta( $order_id, 'ukv_order_ref', true );
	$dest   = (string) get_post_meta( $order_id, 'ukv_destination', true );
	$tier   = (string) get_post_meta( $order_id, 'ukv_tier', true );
	$status = (string) get_post_meta( $order_id, 'ukv_status', true );
	if ( '' === $status ) { $status = 'paid'; }

	$label = UKV_TRACKER_LABELS[ $status ] ?? 'In progress';
	$next  = UKV_TRACKER_NEXT[ $status ] ?? '';

	// Progress: stage index over the pipeline. won -> delivered. rejected/refunded -> full bar.
	$effective = ( 'won' === $status ) ? 'delivered' : $status;
	$idx = array_search( $effective, UKV_TRACKER_PIPELINE, true );
	if ( in_array( $status, [ 'rejected', 'refunded' ], true ) || false === $idx ) {
		$pct = 100;
	} else {
		$total = count( UKV_TRACKER_PIPELINE ) - 1; // last stage index
		$pct   = $total > 0 ? (int) round( ( $idx / $total ) * 100 ) : 0;
	}

	$o = '<div class="ukv-tracker-result" style="max-width:640px;margin:1em 0;padding:1.25em;border:1px solid #e2e2e2;border-radius:8px">';
	$o .= '<h3 style="margin-top:0">Application ' . esc_html( $ref ) . '</h3>';
	$o .= '<p style="margin:.25em 0">';
	if ( '' !== $dest ) { $o .= '<strong>Destination:</strong> ' . esc_html( $dest ) . '<br>'; }
	if ( '' !== $tier ) { $o .= '<strong>Service:</strong> ' . esc_html( ucfirst( $tier ) ) . '<br>'; }
	$o .= '<strong>Status:</strong> ' . esc_html( $label );
	$o .= '</p>';

	// Progress bar.
	$o .= '<div style="background:#eef0f2;border-radius:6px;height:14px;overflow:hidden;margin:.75em 0">';
	$o .= '<div style="background:#0f7b3f;height:14px;width:' . (int) $pct . '%"></div>';
	$o .= '</div>';

	if ( '' !== $next ) {
		$o .= '<p style="margin:.5em 0;color:#333">' . esc_html( $next ) . '</p>';
	}

	// Open barriers (client-facing guidance only).
	if ( function_exists( 'ukv_barriers_for_order' ) ) {
		$bids = ukv_barriers_for_order( $order_id );
		if ( $bids ) {
			$o .= '<div style="margin-top:1em">';
			foreach ( $bids as $bid ) {
				$guidance = (string) get_post_meta( $bid, 'guidance', true );
				if ( '' === $guidance ) { continue; }
				// Guidance is staff free-text shown to the public — strip any PII a staff member typed in.
				if ( function_exists( 'ukv_redact_pii' ) ) { $guidance = ukv_redact_pii( $guidance ); }
				$nat   = (string) get_post_meta( $bid, 'nature', true );
				$natl  = defined( 'UKV_BARRIER_NATURE' ) ? ( UKV_BARRIER_NATURE[ $nat ] ?? '' ) : '';
				$o .= '<div style="background:#fff8e1;border:1px solid #f0e0a0;border-radius:6px;padding:.75em;margin:.5em 0">';
				$o .= esc_html( $guidance );
				if ( '' !== $natl ) { $o .= ' <em>(' . esc_html( $natl ) . ')</em>'; }
				$o .= '</div>';
			}
			$o .= '</div>';
		}
	}

	$o .= '<p style="margin-top:1.25em;font-size:.85em;color:#777;border-top:1px solid #eee;padding-top:.75em">Independent service — not a government website.</p>';
	$o .= '</div>';
	return $o;
}

/**
 * [ukv_tracker] — form (ref + email) posting to itself with a nonce.
 * On valid POST runs the lookup and renders the client-safe result below the form.
 */
add_shortcode( 'ukv_tracker', function () {
	$ref_val   = '';
	$email_val = '';
	$result    = '';

	if ( isset( $_POST['ukv_track_submit'] ) ) {
		if ( isset( $_POST['ukv_track_nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['ukv_track_nonce'] ), 'ukv_track' ) ) {
			$ref_val   = isset( $_POST['ukv_track_ref'] ) ? sanitize_text_field( wp_unslash( $_POST['ukv_track_ref'] ) ) : '';
			$email_val = isset( $_POST['ukv_track_email'] ) ? sanitize_email( wp_unslash( $_POST['ukv_track_email'] ) ) : '';
			$oid = ukv_tracker_lookup( $ref_val, $email_val );
			if ( $oid ) {
				$result = ukv_tracker_view( $oid );
			} else {
				// Non-enumerating: identical generic message whether or not the ref exists.
				$result = '<div class="ukv-tracker-result" style="max-width:640px;margin:1em 0;padding:1em;border:1px solid #e2c2c2;border-radius:8px;background:#fdf3f3">'
					. esc_html( "We couldn't find an application matching those details. Please check your reference and email, or contact us." )
					. '</div>';
			}
		}
	}

	$action = esc_url( remove_query_arg( 'x' ) );
	$o  = '<form class="ukv-tracker-form" method="post" action="' . $action . '" style="max-width:640px">';
	$o .= wp_nonce_field( 'ukv_track', 'ukv_track_nonce', true, false );
	$o .= '<p style="margin:.5em 0"><label for="ukv_track_ref" style="display:block;font-weight:600">Order reference</label>';
	$o .= '<input type="text" id="ukv_track_ref" name="ukv_track_ref" value="' . esc_attr( $ref_val ) . '" required style="width:100%;padding:.5em"></p>';
	$o .= '<p style="margin:.5em 0"><label for="ukv_track_email" style="display:block;font-weight:600">Email</label>';
	$o .= '<input type="email" id="ukv_track_email" name="ukv_track_email" value="' . esc_attr( $email_val ) . '" required style="width:100%;padding:.5em"></p>';
	$o .= '<p style="margin:.75em 0"><button type="submit" name="ukv_track_submit" value="1" style="padding:.6em 1.4em;background:#0f7b3f;color:#fff;border:0;border-radius:6px;cursor:pointer">Track your application</button></p>';
	$o .= '</form>';

	return $o . $result;
} );

// Upsert the published /track page containing the shortcode.
add_action( 'init', function () {
	$existing = get_page_by_path( 'track', OBJECT, 'page' );
	if ( $existing ) {
		if ( false === strpos( (string) $existing->post_content, '[ukv_tracker]' ) ) {
			wp_update_post( [ 'ID' => $existing->ID, 'post_content' => '[ukv_tracker]' ] );
		}
		return;
	}
	wp_insert_post( [
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => 'Track your application',
		'post_name'    => 'track',
		'post_content' => '[ukv_tracker]',
	] );
} );
