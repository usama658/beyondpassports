# /apply Funnel + Payments — Sub-project Design Spec

**Date:** 2026-06-11 · **Parent:** `2026-06-11-uk-outbound-evisa-site-design.md` (subsystem #4 of 6)
**Depends on:** Foundation (#1), visa JSON (#2). **Fed by:** money pages (#2) + tools (#3). **Hands off to:** CRM (#5).

## Goal
Convert intent into paid orders through a 6-step `/apply` funnel that prices from the shared visa JSON, takes documents before payment, charges via Stripe (guest, full upfront), and fires an order event to the CRM. Structure is locked in parent §13.1 + wireframe v2; this spec defines the data wiring.

## Decisions (locked)
| Item | Choice |
|---|---|
| Steps | **6** (parent §13.1, wireframe v2): 1 Product+destination · 2 Eligibility+tier · 3 Applicant details · 4 Document upload · 5 Review & Pay · 6 Confirmation. |
| Product options (step 1) | 3 explicit: **Visa only · IDP only · Visa+IDP**. |
| Prefill | URL params `?dest=<slug>&product=<visa\|idp\|bundle>` set by money pages/checker/photo tool. |
| Pricing | Built from JSON: `tiers[selected]` (service) + `visa.govt_fee_gbp` (at cost) [+ IDP £19 + £5.50 + postage]. Bundle = sum, **no discount** (parent §15). |
| Checkout | **Stripe Checkout**, guest (no account), full upfront. |
| State | Client-side `sessionStorage` across steps; nothing persisted server-side until payment success. |
| Docs | Uploaded at step 4 (pre-pay), encrypted storage (parent §9). |
| Indexing | `/apply` is `noindex` (set in Foundation). |

## Step detail + data wiring
1. **Product + destination** — 3-option chooser (Visa only / IDP only / Visa+IDP) + destination dropdown. Prefilled from URL. Writes `{product, dest}` to sessionStorage.
2. **Eligibility + tier** — reads `data/visas/<dest>.json`; shows `visa.requirements` + tier cards (Standard `tiers.standard_gbp` / Express `tiers.express_gbp` / Premium `tiers.premium_gbp`) with `visa.processing` times. Passport/eligibility captured **here once** (not re-asked at step 3 — parent fix). IDP-only path skips visa fields, shows IDP options.
3. **Applicant details** — name, contact, passport, travel dates. Pre-populated with anything captured at step 2 (no duplicate asks).
4. **Document upload** — passport scan, photo, etc. Encrypted at rest; stored to a private bucket; only references (URLs/IDs) travel in the order payload. MetForm uploader or custom.
5. **Review & Pay (one screen)** — line-item summary built from JSON:
   - Visa: service `tiers[selected]` + govt `visa.govt_fee_gbp` (at cost).
   - IDP: `£19` service + `£5.50` permit + postage.
   - Bundle: both sets of line items summed, no discount.
   - → Stripe Checkout (full upfront, guest).
6. **Confirmation** — order ref + next steps; fires server-side GA4 `purchase` event (parent §10).

## Order handoff (boundary with #5)
On Stripe payment success → server webhook (or Zapier) builds the order payload and creates a Pipedrive deal. #4 owns firing the event + payload; #5 owns the CRM pipeline/automation.

Payload:
```jsonc
{
  "order_ref": "UKV-2026-000123",
  "product": "bundle",              // visa | idp | bundle
  "dest": "turkey",
  "tier": "express",                // null for idp-only
  "amount_gbp": 84.50,
  "line_items": [
    { "label": "Express visa service", "amount_gbp": 49 },
    { "label": "Turkey eVisa government fee", "amount_gbp": 0 },
    { "label": "IDP service", "amount_gbp": 19 },
    { "label": "1949 permit", "amount_gbp": 5.50 },
    { "label": "Postage", "amount_gbp": 11.00 }
  ],
  "applicant": { "name": "...", "email": "...", "phone": "..." },
  "documents": ["https://private-bucket/.../passport.jpg"],
  "stripe_session_id": "cs_..."
}
```

## Three product paths
- **Visa-only** — digital, delivered by email; steps skip IDP fields.
- **IDP-only** — physical, posted; steps skip visa eligibility; requires postal address + driving-licence scan.
- **Visa+IDP bundle** — combined cart, two line-item sets, single payment, no discount.

## Error handling
- Stripe failure/cancel → return to step 5 with state intact (sessionStorage), no order created.
- Missing JSON for `dest` → block step 2 with "destination not available," route to contact.
- Upload failure → inline retry; cannot advance to pay without required docs.
- Abandoned funnel → no server record (privacy); optional analytics drop-off event only.

## Out of scope
Money pages/tools (#2/#3) · CRM pipeline + fulfilment automation (#5) · IDP catalogue/logic depth (#6 — funnel consumes IDP pricing constants here).

## Acceptance criteria
- All 3 product paths complete end-to-end on Stripe test mode, producing correct line items + total from JSON (bundle = exact sum, no discount).
- Prefill from `?dest=&product=` lands the user on the right step-1 selection.
- Passport/eligibility captured once at step 2, not re-asked at step 3.
- Documents upload pre-pay and are referenced (not embedded) in the order payload; stored encrypted.
- On test payment success: GA4 `purchase` fires server-side and an order payload is emitted with all fields above.
- `/apply` returns `noindex`.

## Open items
- Encrypted document storage choice (private S3/bucket vs WP media with access control).
- Webhook host (small serverless function vs Zapier-only) — coordinate with #5.
- IDP postage pricing table (flat vs weight/zone).
