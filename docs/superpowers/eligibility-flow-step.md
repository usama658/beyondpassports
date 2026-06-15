# Eligibility screen — the flow step (slots into every flow)

Adds the previously-missing **Eligibility screen** to the flows. It runs PRE-payment (primary) + as a post-payment
gate (backstop). Insert references at: delivery-framework Stage 1, delivery-process-detailed Phase 0/1,
delivery-runbook 1.2–1.3, production-line-recipes Step 1, page-copy apply/checker.

## The step (where it goes in each flow)
**Pre-sale / checker (Phase 0):** the checker asks nationality + residence. UK+UK → the standard answer; otherwise
"we'll confirm your specific requirements + price" → callback. (`[ukv_eligibility_checker]`.)

**Apply / intake (before payment):** the apply funnel captures nationality · residence · status · trip purpose
(+ applicant/minor/prior-refusal). It **branches before Stripe**: standard (UK+UK+citizen+tourist, no prior
refusal) → fixed tiers + checkout; non-standard → "request a personalised quote" → callback #324 (no fixed-tier
charge). The order's `ukv_eligibility` is computed on creation.

**Onboarding gate (Stage 1, post-payment backstop):** a `manual_review` order **cannot advance past `paid`** until
an agent records a clearance (verifies the specific nationality/residence rules + agrees a bespoke quote) on the
Eligibility meta box → `cleared`; or `referred` (closed). Standard orders flow normally.

## The recipe lens (Step 1, add to recipes)
- **What:** screen passport-nationality + residence + status + purpose; route standard vs manual-review.
- **Why:** the visa rules + price depend on nationality + residence, not just destination — quoting UK data to a
  non-UK case = wrong info + refusal + underpricing.
- **How:** captured at checker + apply; `ukv_eligibility_evaluate()` routes; the gate holds non-cleared orders.
- **When:** before payment (primary) + at onboarding (backstop).
- **Who:** automated route + an agent clears manual-review (verifies rules, sets the bespoke quote).
- **Would:** standard → self-serve fixed tier. **Could:** manual-review → agent verifies + quotes via Payment Link.

## Pricing tie-in
- **Standard lane** → fixed Standard/Express/Premium tiers.
- **Manual-review lane** → **bespoke quote** (no fixed tier): agent sets the price + sends a Stripe Payment Link.

## Status
Built (Phase 1 Units 1–7 + e2e): order schema + router, agent clear/refer, gate, apply-intake read, checker
inputs, bespoke-quote pricing. **Operator remaining:** add the eligibility fields to Forminator #299 + wire the
pre-payment branch (Unit 6) + the live Stripe Payment Link at launch.
