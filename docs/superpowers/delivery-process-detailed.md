# Delivery process — granular end-to-end (every step, appointments, delays, exceptions)

Expands `delivery-framework.md` into every micro-step: trigger → who → action → customer comm → system state
(`ukv_status` + meta) → SLA → possible delays/exceptions → fallback. Compliance throughout: independent service,
not a government website; service fee separate; express speeds our handling not the decision; no approval
guarantee; IDP = guided self-service (collected in person at PayPoint).

`[NEEDS YOUR INPUT]` marks where only you know the real-world detail — fill these in.

---

## PHASE 0 — Pre-sale (lead → intent)
0.1 Visitor lands (organic/guide/ad) → reads money page or guide.
0.2 Uses **visa checker** (`/do-i-need-a-visa/`) → sees if a visa is needed + which product.
0.3 Calls / WhatsApps / requests callback (primary channels) OR goes straight to **Apply**.
0.4 Apply funnel: picks destination + tier → Stripe payment.
- Exceptions: payment fails (retry/declined), abandons checker (→ `checker_abandon` nudge), asks a question first (→ callback/WhatsApp).

## PHASE 1 — Onboarding (payment → order opened)
1.1 **Stripe charge succeeds** → hook creates `ukv_order` (`paid`), HubSpot contact+deal, order_ref, journey opened.
1.2 **Order confirmation email** (order_paid) → links how-it-works + track. SLA: instant.
1.3 First human touch `[NEEDS YOUR INPUT: do you call every new order? within how long?]` → log journey note, set owner/agent.
1.4 Confirm trip: destination, **travel date**, traveller count, product correct.
- Exceptions: wrong product bought (→ adjust/refund-diff), trip date too soon for tier (→ advise express / set expectation), multiple travellers (→ one order each? `[NEEDS YOUR INPUT]`).

## PHASE 2 — Requirements determination
2.1 System/agent determines required documents for the destination (Pods `required_docs`) + **passport-validity months**.
2.2 Determine route per product:
- **eVisa / ETA** → online only, no appointment.
- **Full/sticker visa** → may need **appointment + biometrics + in-person/courier submission** `[NEEDS YOUR INPUT: which destinations? which use VFS/TLS/embassy?]`.
- **IDP** → no submission by us; guided self-service at PayPoint.
2.3 Set status → `awaiting_docs`.

## PHASE 3 — Document collection + intake
3.1 Send the **exact required-docs list** + "How to send your documents" guide (docs_needed email).
3.2 Customer uploads/sends: passport bio page, photo, travel dates, + extras (onward ticket, insurance, accommodation) `[GAP #68: no upload-tied-to-order yet — currently manual reply/WhatsApp]`.
3.3 Agent saves docs to the order (`ukv_documents`).
3.4 **Auto-chase** at 24h if incomplete.
- Delays: customer unresponsive (chase cadence `[NEEDS YOUR INPUT: how many chases, then pause?]`), wrong/blurred docs (request redo), passport validity short (→ barrier, advise **renewal first** — can stall days/weeks).

## PHASE 4 — Review & QA (pre-submission)
4.1 **AI doc review** (advisory) — expiry vs required validity, name match, photo spec, legibility `[GAP: image vision needs Anthropic key; currently text-only]`.
4.2 Human reviews flags, fixes with customer.
4.3 **Completeness gate** — must be 100% before proceeding `[GAP #75: not enforced yet]`.
4.4 **Sign-off** — human confirms ready `[GAP #75]`.
- Exceptions: eligibility doubt (→ advise honestly, possible refund if not viable), photo re-take, name/passport mismatch.

## PHASE 5 — Government appointment / biometrics (ONLY full-visa route)
`[NEEDS YOUR INPUT: confirm which products reach this phase; skip for eVisa/ETA/IDP]`
5.1 Identify centre (VFS/TLScontact/embassy) + available slots.
5.2 **Book appointment** `[NEEDS YOUR INPUT: do you book for the customer or guide them?]`.
5.3 Prepare appointment pack (forms, checklist of what to bring).
5.4 Customer attends → biometrics/fingerprints/photo taken; documents submitted in person or by courier.
5.5 Collect appointment confirmation + receipt; record govt reference `[GAP #69]`.
- Delays: **no slots available** (common bottleneck — wait days/weeks), customer can't attend (reschedule), centre rejects a document on the day, courier delay.

## PHASE 6 — Submission
6.1 Submit on the official portal (eVisa/ETA) OR confirm in-person submission done (full visa).
6.2 Pay the **government fee** from the collected total; record govt-fee-paid `[GAP #69]`.
6.3 Record the **government reference number** `[GAP #69]`.
6.4 Status → `submitted`; submitted email (timeframe, cautious).

## PHASE 7 — Processing & delays (the wait)
7.1 Monitor the application; status → `awaiting_decision`.
7.2 Possible **delay/exception events** (each = a barrier + a proactive client update):
- **Portal outage / system down** (temporary, often destination-wide → fan-out update to all affected).
- **Government backlog / seasonal surge** (temporary; reset expectations).
- **Request for Evidence / additional info** from the authority `[NEEDS YOUR INPUT: how relayed?]` → ask customer fast, resubmit.
- **Administrative processing / extra checks** (some nationalities/destinations) — open-ended wait.
- **Near travel date approaching** with no decision → escalate, advise contingency `[NEEDS YOUR INPUT: do you offer expedite/contact embassy?]`.
7.3 Keep the customer updated only on real news.

## PHASE 8 — Decision
8.1 **Approved** → status `delivered`/`won`, proceed to Phase 9.
8.2 **Rejected/refused** → capture **reason** (taxonomy) `[GAP #73]`; advise options (reapply, appeal where possible, refund per policy) `[NEEDS YOUR INPUT: refund policy on govt refusal?]`.
8.3 **Partial / more info needed** → loop back to 7.2.

## PHASE 9 — Delivery
9.1 Receive the grant (e-visa PDF / sticker passport returned / ETA confirmation).
9.2 Deliver to customer: email the e-visa to print, or return passport `[NEEDS YOUR INPUT: how is a sticker-visa passport returned — courier? tracked?]`.
9.3 Delivered email + "Using your visa on arrival" guide (how-to-use, border officer decides).
9.4 **Archive** to Drive folder per order_ref (Zapier) + Sheet row.
9.5 Status confirmed `delivered`.

## PHASE 10 — Aftercare & close
10.1 Confirm receipt with customer.
10.2 **Review request** email (consented testimonials feed content).
10.3 **GDPR retention** — purge stored passport scans/photos after the retention period `[GAP #71; NEEDS YOUR INPUT: retention period — 30/90 days?]`.
10.4 Mark order closed/won; outcome feeds success stats + (if rejected) the requirements feedback loop `[GAP #76]`.

---

## Cross-cutting: delay & exception register (each → barrier + comms)
| Event | Nature | Scope | Customer action | Our action |
|---|---|---|---|---|
| Payment failed | temporary | case | retry card | resend payment link |
| Docs missing/blurred | temporary | case | resend | chase + guide |
| Passport validity short | permanent-ish | case | renew passport | pause, advise |
| No appointment slots | temporary | dest | wait / flexible dates | keep checking, book ASAP |
| Portal outage | temporary | dest/all | none | resubmit when up, fan-out update |
| Govt backlog | temporary | dest | patience | reset expectations |
| Request for evidence | temporary | case | provide fast | relay + resubmit |
| Near travel, no decision | urgent | case | contingency | escalate/expedite |
| Refusal | permanent | case | reapply/appeal | reason + options + refund per policy |

## What this exposes (already in the gap backlog #68–76)
Document intake (#68), govt-ref/fee fields (#69), ownership+escalation (#70), retention (#71), refund flow (#72),
rejection-reason (#73), passport-validity activation (#74), QA gate (#75), feedback loop (#76). Plus the
`[NEEDS YOUR INPUT]` items above — operator knowledge to lock the process.
