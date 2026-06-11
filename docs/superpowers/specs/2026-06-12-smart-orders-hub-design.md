# Smart Orders & Ops Hub — Design Spec

**Date:** 2026-06-12 · **Parent:** UK Outbound Visa Site · **Depends on:** funnel (#4/T12), CRM (#5/T13), Pods data (#2)

## Goal
A back-office system where the team manages every paid order + its documents in one place, made **smart** by AI document review, completeness auto-chase, SLA/priority flagging, and ops insights. Layered combo: WordPress = operational hub + doc store (canonical), HubSpot = CRM/sales, Google Drive = final-doc archive, Google Sheet = master log. Tied together by one `order_ref`.

## Architecture
- **WordPress Order record (canonical):** a `ukv_order` CPT created on successful Stripe charge (extend the existing `forminator_custom_form_after_stripe_charge` hook in `ukv-hubspot.php`). Fields: order_ref, customer name/email, destination, tier, service_fee, govt_fee, total, passport_number, document attachment IDs/URLs, status, AI-review flags, created/updated.
- **Admin "Orders" dashboard:** custom wp-admin page listing orders (ref, customer, destination, tier, total, status dropdown, doc downloads, SLA flag, AI-flag badge, notes). Filter by status; per-order detail view.
- **order_ref** is shared with the HubSpot deal (already created) + used for the Drive folder name + Sheet row.

## Smart features

### 1. AI document review (headline)
- **Trigger:** documents uploaded (Forminator) / order created.
- **Engine:** Anthropic Claude API (vision) — model **claude-haiku-4-5** for cost (~pennies/order), escalate to a larger model only on ambiguity. Requires `UKV_ANTHROPIC_KEY` (DB option, not git).
- **Checks per uploaded passport/photo:**
  - Passport **expiry** ≥ destination's required validity (e.g. 6 months) from travel date → flag if short.
  - **Name** on passport matches the application name → flag mismatch.
  - **Photo** meets the destination's spec (background, framing) → flag.
  - **Legibility / document type** correct (is it actually a passport bio page?) → flag.
- **Output:** a structured verdict (pass/flag + reasons) stored on the order; shown as a badge in the dashboard. Never auto-rejects — it **assists** the human reviewer (the core "we catch errors" value-add). Compliance: AI output is advisory; a human confirms before submission.

### 2. Completeness + auto-chase
- Each destination's **required documents** come from Pods (`requirements` + a new `required_docs` field). The engine compares uploaded vs required → marks order **complete/incomplete**.
- **Auto-chase:** if incomplete after N hours, auto-send the "docs needed" email (+ WhatsApp link) listing exactly what's missing. Rules-based, free.

### 3. SLA + priority engine
- Each tier has a processing-time target (Pods `processing`). Compute an **SLA due** timestamp per order; flag **red** when breached, **amber** near. **Express/Premium** sorted to the top of the dashboard. Daily check (wp-cron). Free.

### 4. Ops insights dashboard
- Widgets: revenue (this month), orders by status, **bottleneck stage** (where orders sit longest), avg processing time, conversion (checker→apply→paid via GA4). Reads WP orders + GA4. Free.

## Integrations (the combo)
- **HubSpot:** deal already created on charge (shares order_ref); team uses for comms/follow-up. Optionally push AI-flag + status to the deal.
- **Google Drive:** one folder per order_ref for final issued documents — manual now; **Zapier (free)** auto-create folder + copy docs later.
- **Google Sheet:** append a row per order (ref, date, customer, dest, tier, total, status) — via **Zapier (free)** for at-a-glance log/reporting.

## Dependencies / open items
- **Anthropic API key** (for AI review) — user provides; store in DB option `UKV_ANTHROPIC_KEY`.
- Email delivery (SMTP/transactional) for auto-chase — needed at production (local XAMPP won't send).
- WhatsApp number (chase links) — already set (sample).
- New Pods field `required_docs` per destination (list of required document types).
- Zapier free account for Drive/Sheet auto-feed (optional, later).

## Build phases
1. **Foundation (free):** Order CPT + create-on-charge + admin Orders dashboard (list + detail + status). 
2. **Completeness + SLA (free, rules-based):** required-docs check + auto-chase + SLA flags/priority sort.
3. **Ops insights (free):** dashboard widgets.
4. **AI document review (needs Anthropic key):** Claude-vision checks + flags on the order.
5. **Drive/Sheet auto-feed (free, Zapier):** archive + log.

## Acceptance criteria
- A successful payment creates a `ukv_order` with all fields + linked docs; visible in the admin Orders dashboard with a status dropdown.
- Incomplete orders are flagged + the correct "missing docs" auto-chase fires.
- SLA-breached orders flag red; Express/Premium sort first.
- AI review (with key) returns a structured pass/flag verdict per document, shown as a badge; never auto-rejects.
- order_ref matches the HubSpot deal; ops dashboard shows revenue + bottleneck.

## Lead Journey + Case Intelligence (extension)

### Journey log (per order + per callback lead)
- **Critical header** (scannable, agent-edited): stage · blocker (none/docs-missing/payment-pending/eligibility/customer-deciding) · next-action + due date · priority · travel date · passport expiry · AI flag · **risk flag** (rejection-likely) · **order value / upsell note**.
- **Timeline:** append-only notes, each = `date · agent · channel (call/WhatsApp/email) · 1-line summary · outcome`. Stored as order meta (`ukv_journey` = array). Reverse-chronological "story so far".
- **Input (combo):** (1) in-order meta box (full edit), (2) quick-add note from the Orders list table, (3) push each note to the **HubSpot deal timeline** (engagement/note via API) so sales sees it.

### Case Intelligence (improve success rate)
- **Rules-based (free):** aggregate all `ukv_order` outcomes → rejection/refund rate **by destination, blocker, tier**; avg time-in-stage; per-case auto-flag when it matches a high-rejection pattern (high-rejection destination + open blocker + near travel date).
- **AI-assisted (needs `UKV_ANTHROPIC_KEY`):** Claude reads the case's journey + a digest of similar past cases (same destination/tier + outcome) → a plain-language **next-best-action recommendation** to lift success.
- **Success-rate dashboard:** overall + per-destination success rate, trend over time, **top rejection causes**. Closes the improvement loop.

### Build phases (extension)
7. Journey log (header fields + timeline + in-order meta box) — free.
8. Quick-add note from Orders list + HubSpot timeline sync — free + API.
9. Rules-based case pattern stats + per-case risk flag + success-rate dashboard — free.
10. AI next-best-action recommendation (needs Anthropic key).

## Out of scope
WhatsApp Business API automation (separate paid sub-project); building the customer-facing funnel (done); HubSpot pipeline setup (done).
