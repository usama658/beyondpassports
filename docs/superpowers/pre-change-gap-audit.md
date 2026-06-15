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

---

## Wave 2 — broader business / legal / ops / growth gaps (tasks #130–141)
Beyond the data/eligibility gaps, these are business-readiness gaps. The software is built; these are where the
*business* is unfinished.

**Legal / regulatory (confirm before launch):**
- #130 OISC/IAA regulation scope — confirm outbound facilitation is outside regulated UK immigration advice.
- #131 Consumer Contracts Regs — statutory 14-day cancellation right + service-start waiver at checkout.
- #132 Sub-processor DPAs — HubSpot/Stripe/host/Anthropic (with the GDPR transfer item #123).

**Financial (margin/cash risk):**
- #133 FX risk on government fees (GBP collected vs USD/local cost) — buffer or quote-at-time.
- #134 Supplier payment mechanics (how govt fees are actually paid).
- #137 Cash-flow / float (collect upfront, pay later; refund/chargeback impact).

**Continuity / ops:**
- #136 Business continuity / single-point-of-failure (solo operator, held passports, deadlines).
- #135 Formal complaints procedure.
- #138 Visa-rule-change monitoring (keep destination data accurate).

**Tech / security:**
- #139 Admin 2FA + brute-force + patching + uptime + off-site backups (you hold passport data).

**Growth (existential):**
- #140 Acquisition / traffic — the funnel is built but has no traffic plan. No traffic = no orders.
- #141 Entry requirements beyond visa (vaccinations/transit/onward-ticket).

## The honest meta-point
The software was the easy, finished part. The remaining risk is now almost entirely **business + legal + ops +
growth** — and #140 (acquisition) is existential: a perfect production line with no customers produces nothing.
Prioritise the launch-blocking legal items (#130/#131/#123/#132) + the financial-correctness items (#124 VAT,
#133 FX) + a traffic plan (#140) over building more features.
