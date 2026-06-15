# Pre-change gap audit — silent assumptions to resolve before building

Found while specifying the residency/eligibility work. These are uncaptured dimensions / unexamined assumptions
that could break eligibility, pricing, or compliance. Resolve the design/legal ones BEFORE building, so the
eligibility + funnel + pricing changes are built once, correctly. Tasks #120–129.

## Eligibility-adjacent (uncaptured data that changes the visa or the price)
- **#120 Trip purpose + visa sub-type/entries** — only destination + tier captured; not purpose
  (tourist/business/transit/study) or visa type (single/multi-entry, 30/90-day). These set the actual visa + fee.
- **#121 Applicant ≠ payer + minors** — one "name"; no applicant-vs-cardholder, no child handling.
- **#122 Previous refusals / immigration history** — must be declared; changes eligibility + success odds.
- **#126 Dual nationality / passport type** — which passport they travel on.
> These belong in the SAME intake/eligibility redesign as residency (#113–119) — decide them together so the
> apply form + order meta are extended once, not five times.

## Legal / compliance (decide before launch)
- **#123 GDPR cross-border transfer** — passport/order data → HubSpot (US) + Anthropic (US); needs lawful basis +
  privacy-policy disclosure + PII minimisation.
- **#124 VAT on the service fee** — inclusive? threshold? invoices — affects the pricing tiers themselves.
- **#126/#128 ToS/liability + accessibility (WCAG)** — review legal pages for this model + a11y pass.

## Commercial / data quality
- **#127 Travel-insurance requirement** — destination-specific; eligibility + upsell.
- **#128 Fraud / chargebacks / identity** — passport + card handling, dispute SOP.
- **#129 Per-destination fee + processing-time accuracy** — verify vs gov.uk before quoting.

## Recommended sequence (build once)
1. **Decide first (no code):** VAT (#124) + GDPR transfer (#123) + which intake dimensions you'll capture
   (#120/#121/#122/#126 alongside residency #113). These shape the data model + pricing — settle them before building.
2. **Then build the unified intake/eligibility/pricing change** (#113–119 + the agreed #120-class fields) in one pass.
3. **Then the rest** (insurance, fraud SOP, fee verification, ToS/a11y) as follow-ups.

## Why this matters
The residency gap showed the cost of a silent assumption found late. Surfacing these now means the eligibility +
funnel + pricing rebuild captures ALL the needed dimensions at once — instead of reopening the apply form and
order schema repeatedly.
