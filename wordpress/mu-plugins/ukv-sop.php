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
 * Per-stage RECIPE — the 9-lens decision card, keyed by ukv_status.
 * what/why/how/when/where/which/who/would/could. Source: docs/superpowers/production-line-recipes.md.
 */
const UKV_STAGE_RECIPE = [
	'paid' => [
		'what' => 'Open the order, confirm the trip, set expectations.',
		'why'  => 'Right scope + a strong first impression prevent rework and build trust.',
		'how'  => 'Order/deal auto-created on charge; owner calls, confirms details, logs a note.',
		'when' => 'Call within ~1 working hour of payment.',
		'where'=> 'Production Line (paid) → order screen; phone, backed by WhatsApp + email.',
		'which'=> 'orders · hubspot · ownership · emails (order_paid).',
		'who'  => 'The assigned owner (round-robin; queue fallback).',
		'would'=> 'Call, confirm trip, set owner, send confirmation; request nothing yet.',
		'could'=> 'WhatsApp-first if preferred; retry 3×/24h then pause+notify; manual order if non-Stripe.',
	],
	'awaiting_docs' => [
		'what' => 'Get the right documents in, correctly.',
		'why'  => 'Bad/missing docs are the #1 cause of delay and refusal.',
		'how'  => 'Send the required-docs list + guide; customer uploads via ref+email; save + auto-chase.',
		'when' => 'Chase at 24h → escalating → pause ~1 week; near-travel keep a 2-day cadence.',
		'where'=> '/upload-documents/ (gated) → ukv_documents; docs_needed email.',
		'which'=> 'doc-upload · required-docs · emails · orders (auto-chase).',
		'who'  => 'The owner.',
		'would'=> 'Request, receive, verify legibility, confirm receipt.',
		'could'=> 'Collect over WhatsApp; redo a blurred scan; pause + advise renewal if validity short.',
	],
	'doc_review' => [
		'what' => 'Catch every error before submission.',
		'why'  => 'Core value-add — this is what lifts the success rate.',
		'how'  => 'AI advisory check → human confirm → 100% complete → record sign-off.',
		'when' => 'As soon as docs are complete; always before submit.',
		'where'=> 'Order screen (AI badge + QA sign-off); the QA gate enforces it.',
		'which'=> 'doc-review · required-docs · qa-gate · stage-gates.',
		'who'  => 'Owner (+ a 2nd reviewer for high-risk).',
		'would'=> 'Fix issues with the customer, then sign off.',
		'could'=> 'Re-take a photo; refund-our-fee if not viable; conditional-submit near-travel w/ consent.',
	],
	'submitted' => [
		'what' => 'Lodge the application + pay the government fee.',
		'why'  => 'Point of no return; accuracy is everything.',
		'how'  => 'Submit on the official portal; pay the govt fee; record govt-ref + fee-paid.',
		'when' => 'Only after the QA gate passes.',
		'where'=> 'Official portal; order screen (govt fields); submitted email + tracker.',
		'which'=> 'govt-fields · qa-gate · emails (submitted) · tracker.',
		'who'  => 'The owner.',
		'would'=> 'Submit, record the reference, set a cautious timeframe (no guarantee).',
		'could'=> 'Retry on portal outage (barrier + fan-out); alternate channel where available.',
	],
	'awaiting_decision' => [
		'what' => 'Manage the wait and any queries.',
		'why'  => 'Proactive updates keep clients calm and catch problems early.',
		'how'  => 'Monitor; answer queries fast; each delay = barrier + proactive update.',
		'when' => 'Throughout the wait; same day on any authority query.',
		'where'=> 'Barrier register + proactive updates; tracker for the customer.',
		'which'=> 'barriers · client-updates · emails (decision).',
		'who'  => 'Owner; near-travel/high-risk escalate to a lead.',
		'would'=> 'Update only on real news; reset expectations on backlogs.',
		'could'=> 'Paid official expedite where offered; contact authority + honest contingency near-travel.',
	],
	'delivered' => [
		'what' => 'Get the visa to the customer + show how to use it.',
		'why'  => 'A smooth handover = a happy, repeat customer.',
		'how'  => 'Email the e-visa / return passport by tracked+insured courier (we pay); delivered email + guide; archive.',
		'when' => 'Same day as the grant.',
		'where'=> 'Email/courier; order screen (return tracking); Zapier archive.',
		'which'=> 'emails (delivered) · passport-return · zapier.',
		'who'  => 'Owner; dispatch by ops.',
		'would'=> 'Deliver, confirm receipt, give arrival tips (border officer decides).',
		'could'=> 'Re-host a PDF; correct a grant error with the authority; local collection where offered.',
	],
	'won' => [
		'what' => 'Wrap up, learn, retain.',
		'why'  => 'Reviews + repeat business + GDPR compliance.',
		'how'  => 'Confirm receipt; review request + next-order discount; purge scans at 90 days.',
		'when' => 'On delivery confirmation; purge at 90 days.',
		'where'=> 'Email; retention cron; success dashboard.',
		'which'=> 'discounts · emails (review_request) · retention · feedback-loop.',
		'who'  => 'Owner; system runs the purge.',
		'would'=> 'Ask for a review, retain the relationship, delete data on time.',
		'could'=> 'Returning-customer fast-track; consented story → anonymised content.',
	],
	'rejected' => [
		'what' => 'Record the refusal + advise options.',
		'why'  => 'Outcomes drive the customer\'s next step and our learning loop.',
		'how'  => 'Capture the structured reason; advise reapply/appeal; refund our service fee.',
		'when' => 'As soon as the authority decides.',
		'where'=> 'Order screen (rejection-reason); success dashboard + feedback loop.',
		'which'=> 'rejection · refunds · feedback-loop · insights.',
		'who'  => 'Owner; refunds approved by a lead.',
		'would'=> 'Communicate clearly + kindly; log the reason.',
		'could'=> 'Reapply with corrected docs; appeal where allowed; alternative visa/route.',
	],
	'refunded' => [
		'what' => 'Return the service fee; close cleanly.',
		'why'  => 'Fair outcome + clean records.',
		'how'  => 'Confirm the service-fee refund (govt fee non-refundable); record reason; close.',
		'when' => 'After the refusal/cancellation decision.',
		'where'=> 'Order screen; refund flow.',
		'which'=> 'refunds · emails (refunded).',
		'who'  => 'Owner; lead approves.',
		'would'=> 'Refund our fee, record reason, offer an alternative where viable.',
		'could'=> 'Keep records (retention extendable to closed + 6 months) for disputes.',
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

/** Return the 9-lens recipe for a status. Empty-safe. */
function ukv_stage_recipe( $status ) {
	$status = (string) $status;
	return UKV_STAGE_RECIPE[ $status ] ?? [];
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

	// Full 9-lens recipe for this stage (collapsible).
	$recipe = ukv_stage_recipe( $status );
	if ( ! empty( $recipe ) ) {
		$labels = [
			'what' => 'What', 'why' => 'Why', 'how' => 'How', 'when' => 'When', 'where' => 'Where',
			'which' => 'Which (tools)', 'who' => 'Who', 'would' => 'Would (standard)', 'could' => 'Could (alternatives)',
		];
		echo '<details style="margin:4px 0 8px"><summary style="cursor:pointer;font-weight:600">' . esc_html__( 'Full recipe (9 lenses)', 'ukv' ) . '</summary>';
		echo '<ul style="margin:6px 0 0;padding-left:0;list-style:none">';
		foreach ( $labels as $k => $label ) {
			if ( empty( $recipe[ $k ] ) ) { continue; }
			echo '<li style="margin:0 0 4px"><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( $recipe[ $k ] ) . '</li>';
		}
		echo '</ul></details>';
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
