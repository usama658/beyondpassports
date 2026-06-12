# Delivery runbook — every step: process · problems · solutions · alternates

Operational companion to `delivery-process-detailed.md`. For each step: **Process** (what we do), **Problems**
(what goes wrong), **Solutions** (how we fix), **Alternates** (other valid paths). Scope = all routes (eVisa/ETA
online, appointment+biometrics, full sticker visa, IDP). Decisions locked: **call every new order within ~1
working hour**; **each order gets an assigned owner**, with a shared queue as fallback. Compliance always:
independent service, not a government website; fee separate; express speeds our handling not the decision; no
approval guarantee; IDP = guided self-service at PayPoint. `[INPUT]` = your real policy still needed.

---

## PHASE 1 — Onboarding

### 1.1 Order created on payment
- **Process:** Stripe charge → `ukv_order` (`paid`), HubSpot deal, order_ref, journey opened, **owner auto-assigned** (rota/destination) with queue fallback.
- **Problems:** duplicate charge; charge succeeds but hook fails (no order); wrong product/tier bought; multiple travellers on one payment.
- **Solutions:** idempotent order creation by order_ref; daily reconcile Stripe↔orders to catch missed hooks; product-mismatch → adjust + charge/refund difference.
- **Alternates:** manual order creation for phone/bank-transfer sales `[INPUT: do you take non-Stripe payments?]`.

### 1.2 First contact — call within ~1 working hour
- **Process:** owner calls to confirm trip (destination, **travel date**, traveller count), explain the 6 steps, set expectations; logs a journey note.
- **Problems:** no answer; wrong number; customer in different timezone; outside hours.
- **Solutions:** call → if no answer, WhatsApp + email immediately; retry cadence `[INPUT: e.g. 3 attempts over 24h?]`; out-of-hours → next-morning call + instant auto-email so they're never in silence.
- **Alternates:** WhatsApp-first for customers who prefer chat; callback booking link.

### 1.3 Confirm scope + route
- **Process:** lock product route — online (eVisa/ETA), appointment visa, or IDP — and the required-docs set.
- **Problems:** customer unsure of trip dates; trip too soon for the tier; needs visa for a transit too; group/family.
- **Solutions:** advise express if tight; flag if timeline isn't achievable honestly; one order per traveller `[INPUT: confirm]`.
- **Alternates:** hold order as "planning" if dates unconfirmed.

---

## PHASE 2-3 — Requirements & document intake

### 2.1 Determine requirements
- **Process:** pull destination required docs + **passport-validity months** (Pods); build the customer's checklist.
- **Problems:** requirements changed recently; destination has special rules (insurance for Zanzibar, onward ticket, yellow-fever cert).
- **Solutions:** keep Pods data current vs gov.uk; per-destination "extras" list.
- **Alternates:** —

### 3.1 Request + receive documents
- **Process:** send required-docs list + "How to send documents" guide; customer uploads; agent saves to order; auto-chase at 24h.
- **Problems:** **no structured upload yet** (#68 — manual reply/WhatsApp); blurred/cropped scans; wrong document; missing extras; customer unresponsive.
- **Solutions:** build the post-pay upload (#68); on bad scan → request redo with the photo guide; chase cadence then pause + notify `[INPUT: how many chases before pausing?]`.
- **Alternates:** collect docs over WhatsApp; agent takes details on the call.

### 3.2 Passport validity
- **Process:** check expiry vs destination requirement (+ travel date).
- **Problems:** passport expires too soon — **#1 rejection cause**; auto-detect currently inert (#74).
- **Solutions:** capture expiry at Apply + activate the validity barrier (#74); if short → **pause, advise renewal first** (can add days/weeks).
- **Alternates:** customer fast-tracks passport renewal in parallel; if trip can't wait → discuss refund/options.

---

## PHASE 4 — Review & QA (pre-submission)

### 4.1 AI + human document review
- **Process:** AI advisory check (expiry, name match, photo spec, legibility) → human confirms.
- **Problems:** AI image vision needs the key (text-only now); false flags; photo fails spec; name mismatch (maiden/married, middle names).
- **Solutions:** wire vision at launch (key); human is the decision-maker; re-take photo; align name to passport exactly.
- **Alternates:** manual checklist review if AI unavailable.

### 4.2 Completeness + sign-off gate (#75)
- **Process:** must be 100% complete + a human sign-off recorded BEFORE status can move to submitted.
- **Problems:** pressure to submit incomplete to hit travel date; gate not enforced yet.
- **Solutions:** build the hard gate (#75); if incomplete near travel → escalate, don't submit broken.
- **Alternates:** conditional submit only where the portal allows later document upload `[INPUT]`.

---

## PHASE 5 — Government appointment / biometrics (appointment-visa route)

### 5.1 Identify centre + slots
- **Process:** find VFS/TLS/embassy for the destination; check slot availability.
- **Problems:** **no slots** (major bottleneck — days/weeks); centre far from customer; premium-slot upsell by centre.
- **Solutions:** monitor for releases; offer flexible dates; book earliest; advise premium lounge/slot if customer wants `[INPUT: do you book premium?]`.
- **Alternates:** alternative centre/city; courier-submission route where offered.

### 5.2 Book appointment
- **Process:** **we book the appointment for the customer** (secure the VFS/TLS/embassy slot) + create the appointment pack (forms + "what to bring").
- **Problems:** customer can't attend the slot; documents incomplete for the appointment; ID requirements at the centre.
- **Solutions:** reschedule; pre-appointment checklist call; confirm exactly what to bring.
- **Alternates:** courier/drop-box submission where the route allows (no in-person).

### 5.3 Attend + submit in person
- **Process:** customer attends → biometrics (fingerprints/photo) + documents submitted; collect receipt + **govt reference** (#69).
- **Problems:** centre rejects a document on the day; biometrics fail; customer forgets an item.
- **Solutions:** thorough prep pack reduces this; if rejected → fix + rebook; record everything.
- **Alternates:** —

---

## PHASE 6 — Submission

### 6.1 Submit + pay government fee
- **Process:** submit online (eVisa/ETA) or confirm in-person submission; pay govt fee from the collected total; record **govt reference + fee-paid** (#69); status → `submitted`; submitted email.
- **Problems:** portal down at submit time; payment to gov fails; reference not captured.
- **Solutions:** retry when portal up (barrier + fan-out); structured govt-ref/fee fields (#69).
- **Alternates:** submit via alternate portal/agent channel where available.

---

## PHASE 7 — Processing & delays

### 7.1 Monitor + manage the wait
- **Process:** status `awaiting_decision`; watch for queries; update customer on real news only.
- **Problems (delay register):** portal outage; **govt backlog/seasonal surge**; **Request for Evidence** (more docs); administrative processing/extra checks (open-ended); **near travel date, no decision**.
- **Solutions:** each delay = a **barrier + proactive client update**; destination-wide ones fan out to all affected; RFE → get docs from customer fast + resubmit; near-travel → escalate `[INPUT: do you have an expedite/embassy-contact route?]`.
- **Alternates:** premium/priority processing if the destination offers it (paid); contingency advice (change travel) when truly stuck.

---

## PHASE 8 — Decision

### 8.1 Approved
- **Process:** → Phase 9 delivery.

### 8.2 Refused / rejected
- **Process:** capture **structured reason** (#73: doc quality / eligibility / validity / portal / withdrawn / other); advise options.
- **Problems:** customer upset; refund expectation; reapply vs appeal unclear.
- **Solutions:** clear, kind comms; **refund policy: we refund our service fee; the government fee is non-refundable (already paid to the authority)**; reapply where viable; appeal where the route allows.
- **Alternates:** reapply with corrected docs; alternative visa type; alternative destination route.

### 8.3 More info requested
- **Process:** loop to 7.1 with the RFE.

---

## PHASE 9 — Delivery

### 9.1 Deliver the grant
- **Process:** e-visa PDF emailed to print / ETA confirmation / **sticker-visa passport returned by tracked + insured courier (we absorb the cost)**; delivered email + "Using your visa on arrival" guide; archive to Drive (Zapier).
- **Problems:** customer can't open/print PDF; passport lost in return post; wrong details on the grant.
- **Solutions:** resend/re-host PDF; **tracked, insured return** for passports; if grant has an error → contact authority to correct.
- **Alternates:** in-person/collection for local customers `[INPUT]`.

---

## PHASE 10 — Aftercare & close

### 10.1 Confirm + review + retain
- **Process:** confirm receipt; review-request email (consented testimonials); **GDPR purge** of stored scans after retention — **configurable, default 90 days after delivery (extendable to closed + 6 months for disputes)** (#71); close order; outcome feeds success stats + feedback loop (#73/#76).
- **Problems:** data kept too long (GDPR risk); no review captured.
- **Solutions:** auto-purge cron (#71); review ask + incentive `[INPUT: incentive?]`.
- **Alternates:** repeat-customer fast-track for returning travellers `[INPUT: loyalty?]`.

---

## Locked policies
Refund on refusal = **refund our service fee; govt fee non-refundable**. Appointments = **we book for the
customer**. Passport return = **tracked + insured courier, we pay**. GDPR retention = **configurable, default 90
days post-delivery**. First touch = **call within ~1 working hour**. Ownership = **assigned owner + queue fallback**.

## Still-open `[INPUT]` items
Non-Stripe payments? · call retry cadence · chases before pausing · one-order-per-traveller? · conditional-submit
allowed? · premium appointment slots? · expedite/embassy-contact route? · review incentive · loyalty/repeat fast-track.
