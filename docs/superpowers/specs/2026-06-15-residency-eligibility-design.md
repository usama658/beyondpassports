# Residency / nationality / eligibility — design spec

**Date:** 2026-06-15 · **Parent:** UK Outbound Visa site / production line · **Trigger:** audit found nationality,
country of residence, and residency status are captured nowhere; the site silently assumes UK passport + UK resident.

## Problem
The system captures the passport **number** + destination + payer, but never the **passport nationality**, the
applicant's **country of residence**, or their **residency status**. Visa rules depend on **nationality** (which
rules apply) and **residence/status** (which centre/jurisdiction + whether they may apply from here). Quoting the
UK-citizen data to a non-UK or non-resident applicant = wrong info + refusal. Operator has chosen to **serve all
combos**, so these three fields are now a real gap.

## Model — capture-and-route (do NOT pre-build every nationality)
Capture the three axes at intake, then route each order into a lane. Automate the common lane; handle the rest
manually; automate further only where demand recurs.

### Three axes (capture every order)
- **Passport nationality** (which rules apply).
- **Country of residence** (jurisdiction + some eligibility).
- **Residency status** (citizen / settled-permanent / visa-holder + visa validity) — can they apply from here?

### Eligibility lanes
- **Standard** = UK passport **AND** UK resident → the existing automated flow; money-page data applies as-is.
- **Manual review** = any other combo (UK passport abroad · non-UK passport in UK · any other) → flagged
  `manual_review`; an agent verifies that nationality/residence's actual rules (gov.uk / embassy / IATA — see
  research-sources-reference) and gives a bespoke quote before the order may proceed.

### The gate
A non-standard order **cannot enter the normal production-line flow** (cannot advance past intake) until an agent
records an **eligibility clearance** (rules verified + quote agreed, or referred/declined). This protects against
applying UK rules to a non-UK case.

## Where it runs — PRE-payment first (refinement)
The audit also found no eligibility step in any **flow** (it appears only as a rejection *reason*). Screen at TWO
points, pre-payment primary:
1. **Pre-payment (primary):** the **checker** + **apply funnel** ask nationality + residence. A non-standard combo
   does NOT proceed to the standard Stripe checkout — it shows "we'll confirm your specific requirements + price"
   and routes to a callback/quote. This stops a non-UK/non-resident customer paying for rules that don't apply.
2. **Post-payment (backstop):** the order-level eligibility gate (below) catches anything that slips through —
   blocks the order from advancing until an agent clears it.

The flow docs (delivery-framework Stage 1, detailed-process Phase 0/1, runbook 1.2–1.3, recipes Step 1,
page-copy apply/checker) must each gain an explicit **Eligibility screen** step.

## Data model
New order meta (all `ukv_`-prefixed):
- `ukv_nationality` (ISO country / display name of the passport).
- `ukv_residence_country` (where they live).
- `ukv_residency_status` (`citizen` | `permanent` | `visa_holder` | `other`).
- `ukv_residency_visa_expiry` (date, when status = visa_holder — must outlast the application).
- `ukv_eligibility` (`standard` | `manual_review` | `cleared` | `referred`).
- `ukv_eligibility_note` (agent's verification note + quote basis).

Helpers: `ukv_eligibility_evaluate($nationality,$residence,$status): string` (returns standard|manual_review);
`ukv_order_is_cleared(int): bool` (standard, or manual_review that's been cleared).

## Components
1. **Intake capture** — add the 3 axes to the Apply Forminator form (#300) + read them in the existing
   `forminator_custom_form_after_stripe_charge` enrichment (mirror `ukv-apply-intake.php`); store on the order.
2. **Eligibility router** — on order creation, compute `ukv_eligibility` from the three axes.
3. **Eligibility gate** — extend the stage-gates engine: a non-standard order can't advance past intake until
   `ukv_eligibility` is `cleared`. On-screen reason + journey note when blocked.
4. **Agent surface** — an "Eligibility" meta box: shows the three axes + lane; a "Clear / Refer" control (nonce +
   cap) that records the verification note + sets `cleared`/`referred`.
5. **Checker** — optional nationality + residence inputs on `/do-i-need-a-visa/`; if non-UK, show "we'll confirm
   your specific requirements" instead of the UK-citizen answer.
6. **Compliance** — the standard money-page data + checker answer apply to the **standard lane only**; non-standard
   sees a "we verify your specific rules" message, never an auto-quote.

## Build phases
1. Order meta + `ukv_eligibility_evaluate` + router on creation (free).
2. Eligibility meta box (capture/clear/refer) + the gate in stage-gates (free).
3. Apply-form intake read (free) + the actual Forminator fields (operator, in builder).
4. Checker nationality/residence inputs (free).
5. Later: automate specific recurring nationality/residence combos (per demand).

## Acceptance
- A UK passport + UK resident order → `ukv_eligibility = standard`, flows normally.
- A non-UK or non-resident order → `manual_review`, **blocked** from advancing until an agent clears it; cleared →
  flows; referred → closed.
- The three axes are captured + stored + shown on the order.
- The checker shows the UK answer only for UK input; otherwise the "we'll confirm" message.

## Out of scope
Pre-researched rule sets for every nationality (build per recurring demand, not up front).
