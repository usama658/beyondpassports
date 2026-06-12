<?php
/**
 * Plugin Name: UKV Stage SOP & Troubleshooting (Production Line)
 * Desc: Surfaces the per-stage SOP (do/watch/next) + relevant troubleshooting on each order's
 *       edit screen, so whoever picks up the order knows exactly what to do next and how to
 *       unblock it. Read-only, escaped. Source: docs/superpowers/delivery-runbook.md +
 *       delivery-process-detailed.md (delay/exception register).
 */
defined( 'ABSPATH' ) || exit;

/**
 * Per-stage Standard Operating Procedure, keyed by ukv_status.
 * do = the actions for this stage; watch = what commonly goes wrong; next = what moves it forward.
 */
const UKV_STAGE_SOP = [
	'paid' => [
		'title' => 'Paid — onboard the order',
		'do'    => [
			'Call the customer within ~1 working hour; assign an owner (queue fallback).',
			'Confirm trip: destination, travel date, traveller count, product/tier correct.',
			'Explain the 6 steps + set honest expectations; log a journey note.',
		],
		'watch' => [
			'Duplicate charge or charge-without-order (hook miss) — reconcile against Stripe.',
			'Wrong product/tier bought, or trip too soon for the tier.',
			'No answer — WhatsApp + email, retry up to 3× in 24h, persist near-travel, then pause + notify.',
		],
		'next'  => 'Trip confirmed + owner set → move to Awaiting docs.',
	],
	'awaiting_docs' => [
		'title' => 'Awaiting docs — collect the required documents',
		'do'    => [
			'Send the exact required-docs list + the "How to send your documents" guide.',
			'Save received docs to the order; record travel dates + any extras.',
			'Auto-chase at 24h if incomplete.',
		],
		'watch' => [
			'Blurred/cropped/wrong scans — request a redo with the photo guide.',
			'Passport validity short of destination requirement (#1 rejection cause).',
			'Customer unresponsive — escalating chases; pause + notify after ~1 week (2-day cadence near travel, no pause).',
		],
		'next'  => 'All required docs in → move to Doc review.',
	],
	'doc_review' => [
		'title' => 'Doc review — AI + human QA, completeness + sign-off',
		'do'    => [
			'Run the AI advisory check: expiry, name match, photo spec, legibility.',
			'Human confirms flags and fixes with the customer.',
			'Enforce the completeness gate: must be 100% complete before submit.',
			'Record a human sign-off before the status can move to submitted.',
		],
		'watch' => [
			'Pressure to submit incomplete to hit the travel date — do not submit broken.',
			'False AI flags; photo fails spec; name mismatch (maiden/married/middle names).',
		],
		'next'  => '100% complete + signed off → move to Submitted (conditional submit only where the portal allows later upload, near-travel, with consent).',
	],
	'submitted' => [
		'title' => 'Submitted — application lodged',
		'do'    => [
			'Submit on the official portal (eVisa/ETA) or confirm in-person submission.',
			'Pay the government fee from the collected total; record govt reference + fee-paid.',
			'Send the submitted email (cautious timeframe); monitor for portal queries.',
		],
		'watch' => [
			'Portal down at submit time — retry when up + fan-out update.',
			'Payment to government fails; reference not captured.',
		],
		'next'  => 'Lodged + reference recorded → move to Awaiting decision.',
	],
	'awaiting_decision' => [
		'title' => 'Awaiting decision — manage the wait',
		'do'    => [
			'Monitor the application; respond to any authority queries fast.',
			'Update the customer only on real news.',
			'Log each delay as a barrier + a proactive client update (fan out destination-wide ones).',
		],
		'watch' => [
			'Portal outage, government backlog/seasonal surge, administrative processing.',
			'Request for Evidence — get docs from the customer fast and resubmit.',
			'Near travel date, no decision — escalate (contact authority / paid expedite where offered) + honest contingency advice.',
		],
		'next'  => 'Decision received → Delivered/Won (approved) or Rejected (refused).',
	],
	'delivered' => [
		'title' => 'Delivered — hand over the grant',
		'do'    => [
			'Email the e-visa PDF / ETA confirmation, or return the passport by tracked + insured courier (we pay).',
			'Send the delivered email + the "Using your visa on arrival" guide.',
			'Archive to the Drive folder per order_ref.',
		],
		'watch' => [
			'Customer cannot open/print the PDF — resend / re-host.',
			'Passport lost in return post — tracked + insured; wrong details on the grant — contact authority to correct.',
		],
		'next'  => 'Receipt confirmed → close (Won) + aftercare.',
	],
	'won' => [
		'title' => 'Won — aftercare & close',
		'do'    => [
			'Confirm receipt; send the review-request email (consented testimonials).',
			'Offer the next-order discount; flag returning customers for lighter intake.',
			'GDPR purge of stored scans after retention (default 90 days post-delivery).',
		],
		'watch' => [
			'Data kept too long (GDPR risk) — rely on the auto-purge cron.',
			'No review captured — incentivise with the next-order discount.',
		],
		'next'  => 'Order closed; outcome feeds success stats + feedback loop.',
	],
	'rejected' => [
		'title' => 'Rejected — capture reason + advise options',
		'do'    => [
			'Capture the structured refusal reason (doc quality / eligibility / validity / portal / withdrawn / other).',
			'Communicate clearly and kindly; explain reapply vs appeal.',
			'Apply the refund policy: refund our service fee; the government fee is non-refundable.',
		],
		'watch' => [
			'Customer upset / refund expectation — be clear on what is refundable.',
			'Reapply vs appeal unclear — advise the viable route.',
		],
		'next'  => 'Reapply with corrected docs / appeal where the route allows / alternative visa or destination.',
	],
	'refunded' => [
		'title' => 'Refunded — service fee returned',
		'do'    => [
			'Confirm the service-fee refund was processed (government fee non-refundable once paid).',
			'Record the reason; close the order cleanly.',
			'Offer an alternative route or destination where still viable.',
		],
		'watch' => [
			'Govt fee already paid to the authority — cannot be refunded.',
			'Dispute risk — keep records (retention extendable to closed + 6 months).',
		],
		'next'  => 'Order closed; outcome feeds the feedback loop.',
	],
];

/**
 * Common problems/blockers → solution, keyed by blocker/barrier nature.
 * Sourced from the delay & exception register.
 */
const UKV_TROUBLESHOOTING = [
	'payment_failed'    => 'Resend the payment link; ask the customer to retry the card. Reconcile Stripe ↔ orders to catch missed hooks.',
	'docs_missing'      => 'Chase the customer + resend the "How to send documents" guide and photo guide. Escalating chases; pause + notify after ~1 week (2-day cadence near travel, no pause).',
	'docs_blurred'      => 'Request a redo with the photo guide — legible bio page + spec-compliant photo. Do not submit unreadable scans.',
	'wrong_document'    => 'Tell the customer exactly which document is needed and why; resend the required-docs list.',
	'passport_validity' => 'Pause. Advise renewing the passport before submission — short validity is the #1 rejection cause. Fast-track renewal in parallel, or discuss refund/options if the trip cannot wait.',
	'name_mismatch'     => 'Align the name to the passport exactly (maiden/married/middle names). Human reviewer confirms before submit.',
	'no_slots'          => 'Keep checking for slot releases; offer flexible dates and book the earliest. Offer the paid premium/fast-track slot, or an alternative centre/city.',
	'portal_outage'     => 'Resubmit when the portal is back up. Raise a destination-wide barrier and send the fan-out update to all affected customers.',
	'govt_backlog'      => 'Reset expectations honestly (temporary seasonal/backlog delay). Update only on real news.',
	'request_evidence'  => 'Relay the Request for Evidence to the customer fast, collect the extra docs, and resubmit promptly.',
	'admin_processing'  => 'Open-ended extra checks — set expectations; cannot be rushed. Keep monitoring and update on real news only.',
	'near_travel'       => 'Escalate: contact the authority where possible and offer paid official expedite where the destination provides it. Advise honest contingency (rebook travel) — no false promises.',
	'refusal'           => 'Capture the structured refusal reason; advise reapply/appeal. Refund the service fee (government fee non-refundable).',
];

/** Return the SOP for a status. Empty-safe (returns [] for unknown statuses). */
function ukv_stage_sop( $status ) {
	$status = (string) $status;
	return UKV_STAGE_SOP[ $status ] ?? [];
}

/**
 * Relevant troubleshooting entries for one order, derived from:
 *  - ukv_blocker meta (matched to a UKV_TROUBLESHOOTING key), and
 *  - any open barriers on the order (nature + guidance), via ukv_barriers_for_order if available.
 * Returns a list of [ 'key' => .., 'label' => .., 'solution' => .. ].
 */
function ukv_order_troubleshooting( $order_id ) {
	$order_id = (int) $order_id;
	$out      = [];
	$seen     = [];

	$blocker = (string) get_post_meta( $order_id, 'ukv_blocker', true );
	if ( '' !== $blocker && 'none' !== $blocker && isset( UKV_TROUBLESHOOTING[ $blocker ] ) ) {
		$out[]          = [ 'key' => $blocker, 'label' => ukv_sop_humanise( $blocker ), 'solution' => UKV_TROUBLESHOOTING[ $blocker ] ];
		$seen[ $blocker ] = true;
	}

	// Open barriers attached to this order (bare meta: nature, guidance).
	if ( function_exists( 'ukv_barriers_for_order' ) ) {
		foreach ( (array) ukv_barriers_for_order( $order_id ) as $bid ) {
			$nature   = (string) get_post_meta( $bid, 'nature', true );
			$guidance = (string) get_post_meta( $bid, 'guidance', true );
			if ( '' === $guidance ) { continue; }
			$k = 'barrier_' . (int) $bid;
			if ( isset( $seen[ $k ] ) ) { continue; }
			$out[]      = [
				'key'      => $k,
				'label'    => 'Barrier' . ( $nature ? ' (' . ukv_sop_humanise( $nature ) . ')' : '' ),
				'solution' => $guidance,
			];
			$seen[ $k ] = true;
		}
	}

	return $out;
}

/** Internal: turn a snake_case key into a readable label. */
function ukv_sop_humanise( $key ) {
	return ucfirst( str_replace( '_', ' ', (string) $key ) );
}

/** Meta box: current stage SOP + troubleshooting for this order. Read-only. */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ukv_stage_sop', 'Stage SOP & troubleshooting', 'ukv_sop_metabox', 'ukv_order', 'side', 'default' );
} );

function ukv_sop_metabox( $post ) {
	$status = (string) get_post_meta( $post->ID, 'ukv_status', true );
	$sop    = ukv_stage_sop( $status );

	if ( empty( $sop ) ) {
		echo '<p><em>' . esc_html__( 'No SOP for this stage yet.', 'ukv' ) . '</em></p>';
	} else {
		echo '<p style="margin:0 0 8px"><strong>' . esc_html( $sop['title'] ) . '</strong></p>';

		if ( ! empty( $sop['do'] ) ) {
			echo '<p style="margin:6px 0 2px"><strong>' . esc_html__( 'Do', 'ukv' ) . '</strong></p><ul style="margin:0 0 8px;padding-left:18px">';
			foreach ( (array) $sop['do'] as $line ) { echo '<li>' . esc_html( $line ) . '</li>'; }
			echo '</ul>';
		}
		if ( ! empty( $sop['watch'] ) ) {
			echo '<p style="margin:6px 0 2px"><strong>' . esc_html__( 'Watch for', 'ukv' ) . '</strong></p><ul style="margin:0 0 8px;padding-left:18px">';
			foreach ( (array) $sop['watch'] as $line ) { echo '<li>' . esc_html( $line ) . '</li>'; }
			echo '</ul>';
		}
		if ( ! empty( $sop['next'] ) ) {
			echo '<p style="margin:6px 0 8px"><strong>' . esc_html__( 'Moves forward when', 'ukv' ) . ':</strong> ' . esc_html( $sop['next'] ) . '</p>';
		}
	}

	$ts = ukv_order_troubleshooting( $post->ID );
	echo '<hr style="margin:10px 0">';
	echo '<p style="margin:0 0 4px"><strong>' . esc_html__( 'Troubleshooting (this order)', 'ukv' ) . '</strong></p>';
	if ( empty( $ts ) ) {
		echo '<p style="margin:0"><em>' . esc_html__( 'No open blockers or barriers.', 'ukv' ) . '</em></p>';
	} else {
		echo '<ul style="margin:0;padding-left:0;list-style:none">';
		foreach ( $ts as $row ) {
			echo '<li style="border-bottom:1px solid #eee;padding:4px 0"><strong>' . esc_html( $row['label'] ) . '</strong><br>' . esc_html( $row['solution'] ) . '</li>';
		}
		echo '</ul>';
	}
}
