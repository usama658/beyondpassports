# Gap remediation plan — catering for the audited gaps in the website

A phased plan to resolve the gap-audit items (#113–141) **in the website**, separating **decisions** (you settle,
no code) from **build** (concrete website changes) so we build once, in the right order. Principle: decisions
first → one unified intake/eligibility/pricing rebuild → legal surfaces → ops/security → growth.

---

## Phase 0 — Decisions that shape the build (no code; do first)
These change the data model, pricing, or copy — settle them before building so we don't rework.
- **#124 VAT** — is the service fee VAT-inclusive? registered? → fixes the tier numbers + receipts. (Accountant.)
- **#133 FX rule** — govt fee buffer % OR quote-at-time → fixes how the govt-fee figure is set per order.
- **#130 OISC/IAA scope** — confirm outbound facilitation is outside regulated advice → fixes copy boundaries.
- **#131 Cancellation right** — 14-day statutory right + service-start waiver wording → fixes checkout + refund/ToS.
- **#123/#132 GDPR transfer + DPAs** — lawful basis + which sub-processors → fixes privacy policy + PII minimisation.
- **Intake dimensions to capture** (with residency): trip purpose, visa type/entries, applicant-vs-payer, minors,
  refusal history, dual nationality, insurance-needed. → fixes the one apply-form + order-schema change.
**Output:** a one-page "decisions locked" sheet → feeds Phase 1.

## Phase 1 — Unified intake + eligibility + pricing rebuild (BUILD ONCE)
The big coordinated change. Touches the apply funnel, checker, order schema, router, gate, pricing. Covers
#113–122, #125, #126.

**1a. Order schema (mu-plugin, one pass)** — add meta: nationality, residence_country, residency_status,
residency_visa_expiry, trip_purpose, visa_type/entries, applicant_name (vs payer), is_minor + guardian,
refusal_history, dual_nationality, insurance_required, eligibility (+ note). One migration, all fields.

**1b. Eligibility router + helper** — `ukv_eligibility_evaluate()` from nationality+residence+status (+ purpose
edge cases). Standard (UK+UK+online+tourist) vs manual_review (anything else). Computed on order create. (#113)

**1c. Apply funnel rebuild (Forminator #299 + glue)** — capture the new fields EARLY, then **branch before
payment**: standard → fixed tiers + Stripe; non-standard → hide tiers, "request a personalised quote" → callback
#324. (#115/#118)

**1d. Checker rebuild** — optional nationality + residence + purpose inputs; UK-citizen answer only for UK input,
else "we'll confirm your specific requirements." (#116)

**1e. Eligibility gate + agent surface** — meta box (shows axes + lane, Clear/Refer with note); stage-gate blocks
a manual_review order from advancing until cleared. (#114)

**1f. Bespoke-quote pricing** — manual_review = no fixed tier; agent sets a custom price → Stripe Payment Link →
order created/cleared on payment. Keep fixed tiers for the standard lane. (#119)

**1g. Flows + copy** — add the "Eligibility screen" step to framework/process/runbook/recipes + apply/checker copy;
add insurance-required as a destination flag feeding required-docs. (#117/#125)

**Build method:** one spec (extend 2026-06-15) → coordinated build (order schema first, then router, then
funnel/checker/gate/quote) → e2e test the standard AND manual-review paths.

## Phase 2 — Legal / compliance surfaces (website)
- **Privacy policy** — rewrite: what data (incl. passport = sensitive), purposes, **sub-processors + US transfer +
  safeguards** (#123/#132), retention (90d), rights. Link everywhere.
- **Terms of service** — liability limits, no-approval-guarantee, refund policy (service-fee-only), **14-day
  cancellation right + waiver** (#131).
- **Checkout consent** — explicit "start now + waive the 14-day right" tick + privacy/ToS acceptance.
- **Complaints page + procedure** (#135).
- **Accessibility (WCAG) pass** on the public pages (#126).

## Phase 3 — Operations / security / data accuracy (website + process)
- **Admin security** (#139) — 2FA plugin, login lockout, auto-updates/patch cadence, uptime monitor, off-site
  encrypted backups. Document in pre-launch.
- **Business continuity** (#136) — a cover plan + account-access vault + "what if the operator is away" runbook.
- **Visa-rule-change monitoring** (#138) — gov.uk country-page alerts + a quarterly data-review cadence; reflect in
  Pods + the rule-change updates barriers.
- **Fee + processing-time verification** (#129) — verify each destination's figures vs gov.uk before quoting.
- **Insurance handling** (#127) — destination insurance-required flag → required-docs + optional upsell/affiliate.
- **Fraud / chargebacks** (#128) — identity-confidence checks, Stripe Radar review, a dispute-evidence SOP.
- **Supplier payment + FX + cash-float** (#133/#134/#137) — document the govt-fee payment method + an FX buffer in
  pricing + a float model.

## Phase 4 — Growth (website + external) — the existential one
- **Acquisition** (#140) — SEO (built, keep publishing) + **paid search** (Google Ads on "X visa for UK") +
  comparison/partnership + referral. Without this, nothing else earns. Plan + budget + tracking.
- **Conversion** — trust signals, published testimonials, exit-intent (built) — measure + iterate.
- **Reviews/reputation** — route the review-request to Trustpilot/Google; monitor.
- **Entry-requirements content** (#141) — vaccinations/transit/onward-ticket guides (SEO + accuracy).

---

## Sequencing summary
1. **Phase 0 decisions** (days; mostly your calls + an accountant) →
2. **Phase 1 unified build** (the one big coordinated dev change) →
3. **Phase 2 legal surfaces** (before real customer data) →
4. **Phase 3 ops/security** (before/at launch) →
5. **Phase 4 growth** (at + after launch, ongoing).

## What I can build vs what's yours
- **I build:** Phase 1 (all code), Phase 2 page content (privacy/ToS/complaints drafts + checkout consent),
  Phase 3 code bits (insurance flag, rule-change barrier, fraud hooks), Phase 4 content/SEO + tracking wiring.
- **Yours:** the Phase 0 decisions, accounts/keys, the Forminator field additions, legal sign-off on the drafts,
  ad budget, and the real per-destination data.

## My recommendation
Do **Phase 0 decisions next** (they're cheap + they unblock everything), then I build **Phase 1** in one coordinated
pass. Don't build Phase 1 piecemeal before the decisions — that's the rework trap the residency gap warned about.
And start a **traffic plan (#140) in parallel** — it has the longest lead time and is existential.
