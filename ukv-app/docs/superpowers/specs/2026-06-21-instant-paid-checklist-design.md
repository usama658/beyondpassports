# Instant paid document checklist — design

**Date:** 2026-06-21
**Status:** approved (design), pending implementation plan
**Topic:** Gate the document-checklist tool behind payment, delivering the full tailored checklist **instantly on screen** the moment the customer pays — making the word "instant" honest.

---

## 1. Problem & goal

The `/document-checklist` tool currently shows a paywall gate (step 1 free → tiers + free WhatsApp). The paid tiers describe a human-prepared service, so the tool cannot honestly promise an *instant* checklist.

**Goal:** turn the paid path into a **standalone instant digital product**. Customer fills the wizard, pays, and the full personalised checklist reveals **immediately on screen** (plus tier extras). The free WhatsApp path stays.

**Decided (brainstorm):**
- Product model: **standalone instant digital product** (not folded into the visa-service `Order` system).
- Pricing: **keep 3 tiers**, redefined as instant-deliverable value; prices are **"from £X" starting prices**, charged at the chosen destination's real per-destination rate.
- Architecture: **Approach A — lightweight, pay-then-reveal (peek model)**. Generate the real list after step 2, show it blurred/redacted, reveal on payment.

No eligibility screening is required: an instant checklist is *information*, not a government submission.

---

## 2. User flow

```
/document-checklist
  Step 1  Trip       (destination, purpose, travel/return dates, entries)   — free  [exists]
  Step 2  Situation  (residency, employment, accommodation, funding,
                      minor, prior-refusal)                                  — free  [REINSTATE]
        │ submit → ChecklistService::build(); persist UNPAID ChecklistRequest
        ▼
  GET /checklist/{token}  (unpaid)  — PEEK
        · real list redacted server-side (count + categories + 1 teaser item)
        · 3 tier cards with EXACT per-destination price for the chosen destination
        · consent tick (immediate delivery + 14-day waiver)
        · free "Ask on WhatsApp" path
        │ pick tier (consent required) → POST /checklist/{token}/checkout
        │   → snapshot amount_gbp (server-side) → Stripe Checkout (hosted)
        ▼
  Stripe success_url = /checklist/{token}?session_id={CHECKOUT_SESSION_ID}
        · show() verifies the session with Stripe (read-only) → reveals INSTANTLY
        · webhook persists paid_at moments later (sole writer)
        ▼
  GET /checklist/{token}  (paid)  — FULL reveal + tier extras
```

The gate **moves from after step 1 → after step 2**, so the peek is built from the *real* tailored list (stronger than a generic blur). Step 2 (the situation form, previously removed) is reinstated.

`/checklist/{token}` is **paid-aware**: paid → full; unpaid → peek + pay CTA. It never leaks the list to an unpaid visitor.

---

## 3. Tier matrix (prices = starting "from £X"; charged at destination rate)

| Benefit | Standard (from £35) | Express (from £55) *(most popular)* | Premium (from £85) |
|---|---|---|---|
| Full tailored checklist, on screen **instantly** | ✓ | ✓ | ✓ |
| Saved shareable link | ✓ | ✓ | ✓ |
| Mandatory-vs-optional flags + notes | ✓ | ✓ | ✓ |
| Downloadable **PDF pack** | – | ✓ | ✓ |
| **Calendar reminders** (.ics) keyed to travel date | – | ✓ | ✓ |
| Emailed copy | – | ✓ | ✓ |
| Document **templates & samples** (cover/sponsor letter, itinerary) | – | – | ✓ |
| **Family / multi-traveller** checklist | – | – | ✓ |
| Priority **1:1 WhatsApp review** by UK team | – | – | ✓ |

- PDF (`ChecklistPdfService`), .ics (`IcsService`), and the delivery controller **already exist** — Express extras are mostly wiring existing pieces behind the paywall.
- Premium's **1:1 WhatsApp review** is the one human, non-instant item — the upsell hook and a tie-back to the brand's "a real UK person checks it" positioning. The *checklist* still delivers instantly for all tiers; the review is a labelled follow-up.
- **Templates & samples** and **multi-traveller** are new build (Premium only).

Pricing source: per-destination `tier_standard_gbp / tier_express_gbp / tier_premium_gbp` (same columns `/apply` uses). Cards show the **floor across destinations** ("from £X"); the **exact** price for the chosen destination+tier is shown before Stripe (consumer law: clear price before pay).

---

## 4. Data model

New migration adds to `checklist_requests` (all nullable so existing rows and the pre-payment unpaid record stay valid; the only free path now is the WhatsApp option, not a free checklist):

| Column | Type | Purpose |
|---|---|---|
| `tier` | string, nullable | standard \| express \| premium (null until chosen) |
| `amount_gbp` | decimal(8,2), nullable | snapshot of charged price (frozen at checkout, immune to later edits) |
| `currency` | string(3), default `gbp` | |
| `paid_at` | timestamp, nullable | **sole paid flag**, written only by the webhook |
| `stripe_session_id` | string, nullable | success-return cross-check |
| `immediate_delivery_consent` | boolean, default false | Consumer Contracts waiver |
| `consent_at` | timestamp, nullable | when consent was given |

Existing columns (`token`, `destination_id`, `inputs`, `items`, `email`, `phone`, `channels`, `marketing_consent`, `ip`) unchanged. `items` snapshot is what reveals on pay.

`ChecklistRequest` model: add the columns to `$fillable`/`$casts`; add `isPaid(): bool` (`paid_at !== null`) and a `tier` cast (enum or string).

---

## 5. Payment & reveal

### Routes
- `GET /document-checklist` → `tool()` *(exists)*
- `POST /document-checklist` → `result()` *(repurpose)*: build items, persist **unpaid** request, redirect to token
- `GET /checklist/{token}` → `show()`: paid → full; unpaid → peek
- `POST /checklist/{token}/checkout` → **new** `checkout()`: validate `tier` + consent ticked; resolve + snapshot `amount_gbp` server-side from the destination; create Stripe session; redirect to Stripe
- success_url `/checklist/{token}?session_id={CHECKOUT_SESSION_ID}` · cancel_url `/checklist/{token}`

### StripeService additions (mirror the order pattern)
- `createChecklistSession(ChecklistRequest $r, string $tier, float $amount): string` — `mode=payment`, GBP `price_data`, `metadata.type=checklist` + `checklist_id` + `token`, `client_reference_id=token`.
- `handleWebhook()` — branch on `metadata.type`: existing order path unchanged; `checklist` → `markChecklistPaid()`.
- `markChecklistPaid(ChecklistRequest $r, Session $s)` — set `paid_at` (idempotent: no-op if already set), save `email` from `customer_email`, dispatch post-pay delivery jobs.

### Instant reveal without waiting for the webhook
On the success return, `show()` reads `?session_id`, retrieves the session from Stripe, and if `payment_status === 'paid'` **and** the session's `metadata.token` matches → reveals immediately. This is a **read-only** Stripe check (no DB write); the webhook remains the sole writer of `paid_at`. Once `paid_at` is set, future visits reveal with no Stripe call.

### Post-pay delivery (queued from `markChecklistPaid`)
- All tiers → reveal + saved link.
- Express+ → email with **PDF + .ics** (reuse `ChecklistPdfService` / `IcsService` / `ChecklistDeliveryController`).
- Premium → unlock templates + multi-traveller on the page; **notify team** for the 1:1 WhatsApp review; create HubSpot lead.

### Reveal view
Extend the existing `checklist-result` view: full list when paid; tier-gated extras (download buttons Express+, templates/multi-traveller Premium). Unpaid render = peek partial (see §6).

---

## 6. Gate integrity (security)

**The peek must be server-side redaction, not CSS blur.** A CSS blur still ships real document labels in the DOM (view-source leak). The **unpaid** render withholds real item text server-side:
- shows **count + category names + exactly one teaser item**;
- remaining rows render as locked placeholders with **no real labels**.

Full labels emit **only** when `isPaid()` is true (or the read-only success-return Stripe verification passes for this exact token). This is the core invariant and is covered by a dedicated guard test.

---

## 7. Compliance & edge cases

- **14-day waiver (#131):** consent tick states *both* "I consent to immediate delivery" and "I understand I lose my 14-day cancellation right." No checkout without it. Stored as `immediate_delivery_consent` + `consent_at`. Page states **no refund once unlocked**.
- **Refunds:** revealed = delivered = non-refundable. Error path (paid but reveal failed) → manual support/refund, not automatic.
- **Price integrity:** tier + `amount_gbp` resolved server-side from the destination; client amounts never trusted.
- **Receipt/email:** Stripe Checkout collects email + sends its receipt; webhook `customer_email` saved to the request (powers Express+ email).
- **Disclaimer:** reuse the existing compliance strip — guidance only, not a government site, confirm before submitting, no approval guaranteed, service fee separate from any government fee.
- **noindex:** `/checklist/{token}` stays noindex (per-user / thin).
- **Idempotency / double-charge guard:** webhook idempotent (`paid_at` guard); re-hitting checkout when already paid → redirect to reveal, no second session (mirrors `CheckoutController` order guard).
- **GDPR retention (#71):** add `checklist_requests` to the purge sweep (holds personal inputs).
- **VAT (#125, open):** does not block build; `amount_gbp` treated as gross; receipt/display wording finalised when #125 lands.

---

## 8. Testing (Pest)

**Redaction guard (critical):**
- unpaid `/checklist/{token}` HTML contains **no** real document labels (assert a known mandatory label is absent);
- paid HTML contains them.

**Feature:**
- step-2 POST → unpaid `ChecklistRequest` created + redirect to token;
- checkout **without consent** → rejected (422/redirect-back), no session;
- checkout **with consent** → `createChecklistSession` called (mocked), `amount` = destination tier price, `metadata.type=checklist`;
- webhook `checkout.session.completed` (type=checklist) → `paid_at` set; **replay = idempotent** (no duplicate side effects);
- paid reveal → extras gated by tier (Express PDF/.ics link present; Premium templates + multi-traveller present; both absent on Standard);
- success-return with valid mocked session → reveals before webhook (no `paid_at` write on that path);
- already-paid → checkout redirects to reveal, no new session.

**Unit:**
- price resolution (destination + tier → amount; floor "from" computation for cards);
- redaction helper (paid vs unpaid item projection).

---

## 9. Out of scope

- Folding checklist purchases into the `Order`/orders-hub system (explicitly rejected — Approach B).
- Pay-then-fill ordering (Approach C).
- Flipping the global `show_prices` flag (separate decision; gated per-page).
- Building the actual templates/sample-letter content library beyond wiring the Premium unlock (content authored separately).
- Real Stripe live keys / go-live env (existing launch tasks #98, #207).

---

## 10. Affected files (indicative)

- `database/migrations/<new>_add_payment_to_checklist_requests.php`
- `app/Models/ChecklistRequest.php` (fillable/casts/`isPaid()`)
- `app/Http/Controllers/ChecklistController.php` (`result()` repurpose, `show()` paid-aware + redaction, new `checkout()`)
- `app/Services/StripeService.php` (`createChecklistSession`, webhook branch, `markChecklistPaid`)
- `app/Http/Controllers/StripeWebhookController.php` (route checklist events if not already type-agnostic)
- `app/Services/PricingService.php` or a small helper (tier price + floor)
- `resources/views/public/document-checklist.blade.php` (reinstate step 2; move gate to post-step-2)
- `resources/views/public/checklist-result.blade.php` (peek partial + paid reveal + tier extras)
- post-pay job(s) for Express+/Premium delivery
- retention command (include `checklist_requests`)
- `tests/Feature/…`, `tests/Unit/…`
