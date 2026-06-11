# /apply Funnel + Payments — Sub-project Design Spec

**Date:** 2026-06-11 · **Parent:** `2026-06-11-uk-outbound-evisa-site-design.md` (subsystem #4 of 6)
**Depends on:** Foundation (#1), visa JSON (#2). **Fed by:** money pages (#2) + tools (#3). **Hands off to:** CRM (#5).

> **Revision 2026-06-11 (IDP pivot):** IDP is now **guided self-service** (#6), not a paid fulfilment product (research: UK IDPs are in-person-only at PayPoint, see memory `idp-paypoint-self-service`). The `/apply` funnel is therefore **visa/eVisa/ETA only**. The old 3-option chooser's IDP-only and bundle paths are removed; IDP is now a **cross-sell link** out to the #6 silo, not a cart line item.

## Goal
Convert intent into paid **visa** orders through a 6-step `/apply` funnel that prices from the shared visa JSON, takes documents before payment, charges via Stripe (guest, full upfront), and fires an order event to the CRM. Structure is locked in parent §13.1 + wireframe v2; this spec defines the data wiring.

## Decisions (locked)
| Item | Choice |
|---|---|
| Steps | **6** (parent §13.1, wireframe v2): 1 Destination + IDP cross-sell · 2 Eligibility+tier · 3 Applicant details · 4 Document upload · 5 Review & Pay · 6 Confirmation. |
| Product | **Visa/eVisa/ETA only.** IDP is a cross-sell link to #6 (self-service), not a paid path. |
| Prefill | URL param `?dest=<slug>` (always `product=visa`) set by money pages/checker. |
| Pricing | Built from JSON: `tiers[selected]` (service) + `visa.govt_fee_gbp` (at cost). No IDP line items. |
| Checkout | **Stripe Checkout**, guest (no account), full upfront. |
| State | Client-side `sessionStorage` across steps; nothing persisted server-side until payment success. |
| Docs | Uploaded at step 4 (pre-pay), encrypted storage (parent §9). |
| Indexing | `/apply` is `noindex` (set in Foundation). |

## Step detail + data wiring
1. **Destination** — destination dropdown (prefilled from `?dest=`). Writes `{dest}` to sessionStorage. If the destination's `idp` block flags IDP relevance, show a **cross-sell card** ("Driving in X? See our IDP guide" → `/driving-in-<dest>/`) — link only, no cart effect.
2. **Eligibility + tier** — reads `data/visas/<dest>.json`; shows `visa.requirements` + tier cards (Standard `tiers.standard_gbp` / Express `tiers.express_gbp` / Premium `tiers.premium_gbp`) with `visa.processing` times. Passport/eligibility captured **here once** (not re-asked at step 3 — parent fix).
3. **Applicant details** — name, contact, passport, travel dates. Pre-populated with anything captured at step 2 (no duplicate asks).
4. **Document upload** — passport scan, photo, etc. Encrypted at rest; stored to a private bucket; only references (URLs/IDs) travel in the order payload. MetForm uploader or custom.
5. **Review & Pay (one screen)** — line-item summary built from JSON:
   - Service `tiers[selected]` + government fee `visa.govt_fee_gbp` (at cost).
   - → Stripe Checkout (full upfront, guest).
6. **Confirmation** — order ref + next steps; fires server-side GA4 `purchase` event (parent §10).

## Order handoff (boundary with #5)
On Stripe payment success → server webhook (or Zapier) builds the order payload and creates a Pipedrive deal. #4 owns firing the event + payload; #5 owns the CRM pipeline/automation.

Payload:
```jsonc
{
  "order_ref": "UKV-2026-000123",
  "product": "visa",                // always visa in v1
  "dest": "turkey",
  "tier": "express",                // standard | express | premium
  "amount_gbp": 49.00,
  "line_items": [
    { "label": "Express visa service", "amount_gbp": 49 },
    { "label": "Turkey eVisa government fee", "amount_gbp": 0 }
  ],
  "applicant": { "name": "...", "email": "...", "phone": "..." },
  "passport_number": "...",         // fulfilment key (ETA issues no document)
  "documents": ["https://private-bucket/.../passport.jpg"],
  "stripe_session_id": "cs_..."
}
```

## Product path (single, v1)
- **Visa / eVisa / ETA** — digital; eVisa delivered as emailed PDF, ETA as approval linked to passport number (no document). IDP appears only as a cross-sell link to `/driving-in-<dest>/` (#6); it never enters the cart.

## Error handling
- Stripe failure/cancel → return to step 5 with state intact (sessionStorage), no order created.
- Missing JSON for `dest` → block step 2 with "destination not available," route to contact.
- Upload failure → inline retry; cannot advance to pay without required docs.
- Abandoned funnel → no server record (privacy); optional analytics drop-off event only.

## Out of scope
Money pages/tools (#2/#3) · CRM pipeline + fulfilment automation (#5) · IDP (#6 — self-service, cross-sell link only, no cart/payment).

## Acceptance criteria
- The visa path completes end-to-end on Stripe test mode, producing correct line items + total from JSON (service tier + govt fee at cost).
- Prefill from `?dest=` lands the user on the right destination at step 1.
- Passport/eligibility captured once at step 2, not re-asked at step 3.
- Documents upload pre-pay and are referenced (not embedded) in the order payload; stored encrypted.
- On test payment success: GA4 `purchase` fires server-side and an order payload (incl. `passport_number`) is emitted with all fields above.
- IDP cross-sell card links out to #6 and never adds a cart line item.
- `/apply` returns `noindex`.

## Open items
- Encrypted document storage choice (private S3/bucket vs WP media with access control).
- Webhook host (small serverless function vs Zapier-only) — coordinate with #5.
