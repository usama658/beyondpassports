# Production line — recipes (the 9-lens card per step)

Each step answered through **What · Why · How · When · Where · Which · Who · Would · Could**. Grounded in the
locked policies + the built plugins. Compliance always: independent service, not a government website; service
fee separate; express speeds our handling not the decision; no approval guarantee; IDP = guided self-service.

---

## Step 1 — Intake / onboarding (`paid`)
- **What:** Open the order, confirm the trip, set expectations.
- **Why:** First impression + correct scope prevents downstream rework and builds trust (calls are the main channel).
- **How:** Stripe charge auto-creates `ukv_order` + HubSpot deal; owner calls the customer, confirms destination/travel date/traveller count, logs a journey note.
- **When:** Call **within ~1 working hour** of payment.
- **Where:** Production Line board (paid column) → the order screen; channel = phone, backed by WhatsApp + the confirmation email.
- **Which:** ukv-orders · ukv-hubspot · ukv-ownership · ukv-emails (order_paid → how-it-works + track).
- **Who:** The **assigned owner** (auto round-robin; queue fallback).
- **Would:** Call, confirm, set owner, send confirmation, request nothing yet.
- **Could:** WhatsApp-first if preferred; retry 3×/24h then pause+notify; manual order if a non-Stripe sale.

## Step 2 — Requirements determination (`awaiting_docs` entry)
- **What:** Decide exactly what this destination/product needs.
- **Why:** Asking for the right things once avoids back-and-forth and refusals.
- **How:** Pull the destination's `required_docs` + `passport_validity_months` (Pods); pick the route (online / appointment / IDP).
- **When:** Immediately after intake.
- **Where:** Order screen (required-docs + SOP meta boxes).
- **Which:** ukv-required-docs · ukv-passport-validity · ukv-sop.
- **Who:** Owner.
- **Would:** Build the customer's exact checklist from the destination data.
- **Could:** Flag special extras early (insurance, onward ticket, yellow-fever); branch to the appointment route for sticker visas.

## Step 3 — Document collection (`awaiting_docs`)
- **What:** Get the customer's documents in, correctly.
- **Why:** Bad/missing docs are the #1 cause of delay and refusal.
- **How:** Send the required-docs list + "How to send documents" guide; customer uploads via the secure ref+email upload; agent saves to the order; auto-chase at 24h.
- **When:** Chase at 24h → escalating → pause after ~1 week silence; near-travel keep a 2-day cadence.
- **Where:** `/upload-documents/` (ref+email gated) → `ukv_documents`; docs_needed email.
- **Which:** ukv-doc-upload · ukv-emails (docs_needed) · ukv-orders (completeness/auto-chase).
- **Who:** Owner.
- **Would:** Request, receive, verify legibility, confirm receipt.
- **Could:** Collect over WhatsApp; redo a blurred scan; pause + advise renewal if passport validity is short.

## Step 4 — Review & QA (`doc_review` → submit gate)
- **What:** Catch every error before submission.
- **Why:** This is the core value-add ("we catch errors"); it lifts the success rate.
- **How:** AI advisory check (expiry/name/photo/legibility) → human confirms → completeness 100% → record sign-off.
- **When:** As soon as documents are complete; before any submit.
- **Where:** Order screen (AI badge + QA sign-off box); the QA gate enforces it.
- **Which:** ukv-doc-review · ukv-required-docs · ukv-qa-gate · ukv-stage-gates.
- **Who:** Owner (+ a second reviewer for high-risk if desired).
- **Would:** Fix issues with the customer, then sign off.
- **Could:** Re-take a photo; align a name; advise honestly + refund our fee if not viable; conditional-submit near-travel where the portal allows later upload, with consent.

## Step 5 — Government appointment / biometrics (appointment route only)
- **What:** Secure and run the in-person appointment.
- **Why:** Some visas can't be submitted without biometrics/in-person lodgement.
- **How:** **We book** the centre slot, build the appointment pack ("what to bring"), the customer attends, we record the govt reference.
- **When:** As soon as docs are ready; subject to slot availability (the main bottleneck).
- **Where:** VFS/TLS/embassy (supply-chain registry); the appointment meta box; appointment_booked email.
- **Which:** ukv-appointments · ukv-govt-fields · ukv-supply-chain · ukv-premium-slot.
- **Who:** Owner books; the customer attends.
- **Would:** Book the earliest slot, prep the customer thoroughly.
- **Could:** Offer a paid premium/fast-track slot; use an alternate centre/city; reschedule if they can't attend.

## Step 6 — Submission (`submitted`)
- **What:** Lodge the application + pay the government fee.
- **Why:** The point of no return; accuracy here is everything.
- **How:** Submit on the official portal (or confirm in-person lodgement); pay the govt fee from the collected total; record govt-ref + fee-paid; status → submitted.
- **When:** Only after the QA gate passes.
- **Where:** Official portal; order screen (govt fields); submitted email + tracker.
- **Which:** ukv-govt-fields · ukv-qa-gate · ukv-emails (submitted) · ukv-tracker.
- **Who:** Owner.
- **Would:** Submit, record the reference, set expectations on timeframe (cautious, no guarantee).
- **Could:** Retry on portal outage (barrier + fan-out); use an alternate submission channel where available.

## Step 7 — Processing & delays (`awaiting_decision`)
- **What:** Manage the wait and any queries.
- **Why:** Proactive updates keep clients calm and catch problems early.
- **How:** Monitor; respond to any Request-for-Evidence fast; each delay = a barrier + a proactive client update; destination-wide issues fan out to all affected.
- **When:** Throughout the wait; act the same day on any authority query.
- **Where:** Barrier register + proactive updates; the tracker for the customer.
- **Which:** ukv-barriers · ukv-client-updates · ukv-emails (decision).
- **Who:** Owner; high-risk/near-travel escalate to a lead.
- **Would:** Update only on real news; reset expectations on backlogs.
- **Could:** Use paid official expedite where offered; contact the authority + advise contingency when near travel with no decision.

## Step 8 — Decision (`won`/`rejected`)
- **What:** Record the outcome and act on it.
- **Why:** Outcomes drive both the customer's next step and our learning loop.
- **How:** Approved → deliver. Refused → capture the structured reason, advise options (reapply/appeal), refund **our service fee** (govt fee non-refundable).
- **When:** As soon as the authority decides.
- **Where:** Order screen (rejection-reason box); success dashboard + feedback loop.
- **Which:** ukv-rejection · ukv-refunds · ukv-feedback-loop · ukv-insights.
- **Who:** Owner; refunds approved by a lead.
- **Would:** Communicate clearly and kindly; log the reason.
- **Could:** Reapply with corrected docs; appeal where allowed; suggest an alternative visa type/route.

## Step 9 — Delivery (`delivered`)
- **What:** Get the visa to the customer + show them how to use it.
- **Why:** A smooth handover + arrival guidance = a happy, repeat customer.
- **How:** Email the e-visa to print / confirm the ETA / return the passport by **tracked + insured courier (we pay)**; delivered email + "Using your visa on arrival" guide; archive to Drive.
- **When:** Same day as the grant.
- **Where:** Email/courier; order screen (passport-return tracking); Zapier archive.
- **Which:** ukv-emails (delivered) · ukv-passport-return · ukv-zapier.
- **Who:** Owner; dispatch handled by ops.
- **Would:** Deliver, confirm receipt, give arrival tips (border officer decides).
- **Could:** Re-host a PDF a customer can't open; correct a grant error with the authority; local collection where offered.

## Step 10 — Aftercare & close
- **What:** Wrap up, learn, retain.
- **Why:** Reviews + repeat business + GDPR compliance.
- **How:** Confirm receipt; send the review request **with a next-order discount**; purge stored scans after 90 days; close the order; feed the outcome into stats + the requirements feedback loop.
- **When:** On delivery confirmation; purge at 90 days.
- **Where:** Email; retention cron; success dashboard.
- **Which:** ukv-discounts · ukv-emails (review_request) · ukv-retention · ukv-feedback-loop.
- **Who:** Owner; system runs the purge.
- **Would:** Ask for a review, retain the relationship, delete data on time.
- **Could:** Offer returning-customer fast-track (lighter intake + loyalty discount); turn a consented story into anonymised content.

---

## Cross-cutting (apply to every step)
- **Who-when nudges:** owner daily digest surfaces next-actions due, SLA breaches, high-risk; SLA breach auto-escalates.
- **Which-tools:** the Production Line board is the live view; the SOP/troubleshooting meta box gives the right action in context.
- **Could-always:** every delay/exception has a barrier + a client-safe update; reconciliation catches any missed charge.
