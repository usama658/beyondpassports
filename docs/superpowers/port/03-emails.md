# Email Lifecycle — Port to Laravel Queued Mailables

Source of truth: `wp-content/mu-plugins/ukv-emails.php` (engine + all 9 templates).
Triggers spread across `ukv-emails.php` (status-change hook) and `ukv-refunds.php`.

This is the **secondary** comms channel — phone / WhatsApp are primary. Every send is:
- **Idempotent per (order, event)** — `ukv_email_sent` meta array; an event never resends.
- **Always logged** regardless of delivery outcome — appends to `ukv_email_log` (audit) and `ukv_journey` (timeline) meta.
- **Pluggable transport** — option `ukv_email_transport` ∈ {`wp_mail` (default), `hubspot`}. On XAMPP it logs but does not deliver.

When porting: each event becomes one queued Mailable. Replicate the idempotency + audit-log + journey-note side effects (these are NOT in the Mailable — keep them in a dispatcher/listener so a Mailable can never double-fire and every attempt is recorded).

---

## 1. Lifecycle event table

There are **9 templates**. Of these, **5 are wired to live triggers today** (`submitted`, `decision`, `delivered`, `review_request`, `refunded`). The other **4 are defined but dormant** (`order_paid`, `docs_needed`, `appointment_booked`, `checker_abandon`) — templates exist and must be ported, but their trigger code is a stub / not yet hooked. Notes column flags this.

All subjects/bodies use merge fields read from order post meta:
`{name}` = `ukv_name` (fallback `there`), `{dest}` = `ukv_destination` (fallback `your destination`), `{ref}` = `ukv_order_ref` (fallback `—`), `{base}` = `home_url('/')` (fallback for the live status/help links).

| Event | Trigger condition | Recipient | Subject | Body (verbatim copy, merge fields in `{}`) |
|---|---|---|---|---|
| **order_paid** | **DORMANT** — template only. Intended: order reaches `paid` stage (the entry stage; SOP references "emails (order_paid)" but no hook fires it yet). | `ukv_email` | `Your {dest} visa order is confirmed ({ref})` | `Hi {name},`<br><br>`Thanks — we've received your order for your {dest} visa. Your order reference is {ref}.`<br><br>`Here's exactly what happens next: {base}how-it-works/`<br>`You can check your progress any time here: {base}track/`<br><br>`We'll be in touch shortly (usually by phone or WhatsApp) and let you know which documents we need. Nothing for you to do right now.` |
| **docs_needed** | **DORMANT** — template only. Intended: order enters `awaiting_docs` stage (SOP: "/upload-documents/ (gated) → ukv_documents; docs_needed email"). No hook in `ukv_email_on_status_change` for `awaiting_docs` yet. | `ukv_email` | `Action needed: documents for your {dest} visa ({ref})` | `Hi {name},`<br><br>`To move your {dest} visa application forward (order {ref}), we need a couple of documents — usually your passport bio page and a passport-style photo.`<br><br>`How to send them (it's quick): {base}how-to-send-documents/`<br>`Or just reply here / to our WhatsApp message and we'll guide you.` |
| **submitted** | **LIVE** — `save_post_ukv_order` hook detects `ukv_status` transition to `submitted` → `ukv_email_on_status_change('submitted')`. | `ukv_email` | `Your {dest} visa application has been submitted ({ref})` | `Hi {name},`<br><br>`Good news — your {dest} visa application (order {ref}) has now been submitted to the official system. Timeframes vary by destination and we'll let you know as soon as there's a decision. You can follow progress here: {base}track/` |
| **decision** | **LIVE** — `ukv_status` transitions to `awaiting_decision`. | `ukv_email` | `Update on your {dest} visa decision ({ref})` | `Hi {name},`<br><br>`There's an update on your {dest} visa application (order {ref}). A member of our team will be in touch by phone or WhatsApp with the details and any next steps.` |
| **delivered** | **LIVE** — `ukv_status` transitions to `delivered` **OR** `won` (both treated as successful completion). Fires `delivered` **and** `review_request` together. | `ukv_email` | `Your {dest} visa is ready ({ref})` | `Hi {name},`<br><br>`Your {dest} visa is ready (order {ref}). Please check the details carefully and keep a copy with your travel documents.`<br><br>`How to use it at the border: {base}using-your-visa-on-arrival/`<br><br>`If anything doesn't look right, contact us straight away and we'll help.` |
| **review_request** | **LIVE** — fired immediately after `delivered` on the same `delivered`/`won` transition. | `ukv_email` | `How did we do? Your {dest} visa ({ref})` | `Hi {name},`<br><br>`We hope your {dest} visa (order {ref}) made your trip planning easier. If you have a moment, we'd really appreciate a short review of how we did — it helps other travellers know what to expect.` |
| **refunded** | **LIVE** — `ukv_process_refund()` in `ukv-refunds.php` sets `ukv_status = refunded`, writes refund meta, then calls `ukv_email_fire('refunded')`. | `ukv_email` | `Your refund for the {dest} application ({ref})` | `Hi {name},`<br><br>`We've processed a refund of our service fee for your {dest} application (order {ref}). Please note the government fee is non-refundable as it was already paid to the authority on your behalf.`<br><br>`The refund will appear on your original payment method shortly. If you have any questions, just reply or message us on WhatsApp.` |
| **appointment_booked** | **DORMANT** — template only. Intended: `ukv_set_appointment()` in `ukv-appointments.php` books a biometrics/visa appointment. That function logs a journey note but does **not** call `ukv_email_fire('appointment_booked')` yet — wire it on port. | `ukv_email` | `Your appointment is booked — {dest} visa ({ref})` | `Hi {name},`<br><br>`Good news — we've booked your appointment for your {dest} visa (order {ref}). We'll send you the full details (centre, date and what to bring) separately so you're fully prepared.`<br><br>`Please arrive a little early and bring your passport and the documents we list. Any questions, just reply or message us.` |
| **checker_abandon** | **DORMANT** — daily WP-cron `ukv_email_checker_abandon` exists but its lead source is an empty placeholder (`$leads = []`). Intended: visa-checker submission captured an email but no Apply/order followed within 24h → fire once per lead. No order ref/name available for these (lead-only). | captured lead email | `Finish your {dest} visa check — we can help` | `Hi {name},`<br><br>`It looks like you started a visa check for {dest} but didn't finish. If you'd like a hand, we can guide you through it — just reply and we'll pick up where you left off.` |

**Fallback (unknown event):** subject `Update on your {dest} visa ({ref})`, body `Hi {name},\n\nThere's an update on your {dest} visa (order {ref}).` — keep as a default Mailable / guard.

---

## 2. Mandatory footer (every email)

Appended verbatim to **every** body (`UKV_EMAIL_FOOTER`), separated by a blank line:

> `Independent service — not a government website. Our service fee is separate from any government fee. Express tiers speed up our handling, not the government's decision.`

This is a hard compliance requirement — it carries all three core honesty constraints in one line:
1. **Not a government website** (independent service).
2. **Service fee is separate** from any government fee.
3. **No faster approval** — express speeds *our handling*, not the government's *decision*.

Port as a shared Mailable layout/partial so it cannot be omitted.

### Other compliance copy inside bodies
- **refunded**: "the government fee is non-refundable as it was already paid to the authority on your behalf" — must stay; mirrors the refund-amount logic (only the service fee is refundable).
- **submitted**: "Timeframes vary by destination" — no delivery-time promise.
- **decision**: deliberately content-light — the decision itself is delivered by a human on phone/WhatsApp, never asserted in the email (avoids implying we make/guarantee the decision).

---

## 3. Links that must become Netlify / Laravel URLs

All in-body links are built from `{base}` = `home_url('/')` so they "become the live domain at launch." On port, swap `{base}` for the Netlify/Laravel app URL (config/env, not hard-coded). The specific paths used:

| Path | Used in | Becomes |
|---|---|---|
| `{base}track/` | order_paid, submitted | **Status tracker** — the customer self-service progress page (Laravel/Netlify route). The key dynamic link; the tracker reads order status by ref+email (see `ukv-tracker.php`). |
| `{base}how-it-works/` | order_paid | Marketing/help page. |
| `{base}how-to-send-documents/` | docs_needed | Document-upload help page (gated upload lives at `/upload-documents/`). |
| `{base}using-your-visa-on-arrival/` | delivered | Help page. |

Note: the tracker link is **not** order-specific in the copy (it's the generic `/track/` landing where the customer enters ref + email). If the Laravel rebuild wants a deep magic link, that's a new capability, not a port of existing behaviour.

---

## 4. Merge variables per email (Mailable constructor signatures)

Every email is built from a single `$order_id` and reads 4 meta fields + the base URL. Suggested Mailable inputs:

| Email | Required merge vars |
|---|---|
| order_paid | name, dest, ref, baseUrl |
| docs_needed | name, dest, ref, baseUrl |
| submitted | name, dest, ref, baseUrl |
| decision | name, dest, ref |
| delivered | name, dest, ref, baseUrl |
| review_request | name, dest, ref |
| refunded | name, dest, ref |
| appointment_booked | name, dest, ref |
| checker_abandon | name, dest (no ref/order — lead-only; name may be absent → fallback `there`) |

Common type: `OrderMailable(Order $order, string $baseUrl)` reading `$order->name`, `$order->destination`, `$order->order_ref`. Apply the same fallbacks as the source: name → `there`, dest → `your destination`, ref → `—`.

### Side effects to preserve outside the Mailable (in the dispatcher/listener)
From `ukv_email_send()`:
1. **Idempotency guard** — skip if event already in the order's sent-events set (port `ukv_email_sent`).
2. **Audit log** — append `{event, to, subject, time}` (port `ukv_email_log`).
3. **Journey note** — append `{date, agent:'system', channel:'email', text:"Email sent: {event}"}` (port `ukv_journey`).
4. **Guard empty recipient** — `ukv_email_fire` returns false if `ukv_email` is blank.

### Optional AI polish (port decision)
`ukv_email_template()` optionally runs the body through `ukv_ai_polish_content($body, 'transactional email')` if that helper exists; it returns null when its PII leak-gate trips, in which case the static template is kept. Treat the **static template as the canonical content**; AI polish is an optional, fail-safe enhancement, not part of the contract.
