# Phase 1 — eligibility / intake / pricing rebuild — implementation plan

> Build the unified intake + eligibility + pricing change ONCE, against locked decisions D1–D6
> (phase0-decision-brief.md) + spec 2026-06-15-residency-eligibility-design.md. Test-first per the
> ukv-parallel-build skill. Order matters: schema → router → surfaces → pricing → tests.

**Goal:** capture nationality/residence/status + trip purpose + visa type + applicant/minor + refusal history +
dual nationality + insurance-needed at intake; route Standard (UK+UK+online+tourist) vs Manual-review; gate
non-standard orders; branch the funnel before payment; price manual-review by bespoke quote.

**Tech:** WordPress mu-plugins (live `C:\xampp\htdocs\ukvisa\wp-content\mu-plugins\`, mirror to repo); Forminator
apply form #299; tests via `wp eval-file` (`-d memory_limit=512M`).

---

## Unit 1 — Order schema + eligibility helpers  (file: `ukv-eligibility.php`)
**Files:** create `wp-content/mu-plugins/ukv-eligibility.php`; test `automation/test-eligibility.php`.
**Order meta (all `ukv_`):** `nationality`, `residence_country`, `residency_status` (citizen|permanent|visa_holder|other),
`residency_visa_expiry`, `trip_purpose` (tourist|business|transit|study|other), `visa_entries` (single|multiple),
`applicant_name`, `is_minor` (1/''), `guardian_name`, `prior_refusal` (1/''), `dual_nationality`,
`insurance_required` (1/''), `eligibility` (standard|manual_review|cleared|referred), `eligibility_note`.
**Helpers:**
- `ukv_eligibility_evaluate(array $a): string` — return `standard` iff nationality=UK AND residence=UK AND
  status=citizen AND trip_purpose∈[tourist] AND no prior_refusal; else `manual_review`. (Decision D6 — route on the
  key axes; purpose/refusal can downgrade to manual_review.)
- `ukv_order_is_cleared(int $id): bool` — true if `eligibility` is `standard` or `cleared`.
- `ukv_eligibility_apply(int $id, array $data): void` — sanitise + store the fields, then set `eligibility` =
  evaluate(...).
**TDD:** assert UK+UK+citizen+tourist+no-refusal → standard; any deviation → manual_review; cleared() logic.
**Commit:** `feat(elig): order schema + router`.

## Unit 2 — Eligibility meta box + clear/refer  (extend `ukv-eligibility.php`)
**Adds:** meta box on `ukv_order` showing the captured axes + the lane; a nonce+`current_user_can` "Clear" /
"Refer" control writing `eligibility`=cleared|referred + `eligibility_note`; journey note on each.
**TDD:** clearing a manual_review order → `ukv_order_is_cleared` true + note logged.
**Commit:** `feat(elig): agent clear/refer meta box`.

## Unit 3 — Stage gate: block non-cleared from advancing  (extend `ukv-stage-gates.php`)
**Change:** in the gate, a `manual_review` (not yet `cleared`) order cannot advance past `paid` → on attempted
status change, revert + admin notice + journey note "Eligibility not cleared." (Runs alongside the existing QA +
stage gates; priority 10.)
**TDD:** manual_review order blocked from awaiting_docs until cleared; standard order flows.
**Commit:** `feat(elig): gate non-cleared orders`.

## Unit 4 — Apply-form intake read  (extend `ukv-apply-intake.php`)
**Change:** in the `forminator_custom_form_after_stripe_charge` enrichment, read the new fields from
`$prepared_data` (guarded) → `ukv_eligibility_apply()`. Mirror the existing pattern (passport-expiry etc.).
**TDD:** simulated prepared_data with nationality/residence/etc. → order meta set + eligibility computed.
**Commit:** `feat(elig): apply-form intake read`.
**Operator (manual):** add the matching fields to Forminator #299 (nationality, residence, status, purpose,
visa-entries, applicant name, minor, prior-refusal, dual-nat) — task #115/#97.

## Unit 5 — Checker eligibility inputs  (file: `ukv-checker-eligibility.php` or extend glue)
**Change:** the checker (`/do-i-need-a-visa/`) gains optional nationality + residence + purpose inputs; show the
UK-citizen answer only for UK+UK; otherwise the message "We'll confirm your specific requirements + price." No
auto-quote for non-UK.
**TDD:** UK input → UK answer path; non-UK input → "we'll confirm" path (test the decision function).
**Commit:** `feat(elig): checker nationality/residence inputs`.

## Unit 6 — Apply-funnel branch before payment  (Forminator #299 + glue)
**Change:** capture nationality/residence EARLY in the form; **branch before the Stripe step**: standard → show
tiers + payment; non-standard → hide tiers/payment, show "request a personalised quote" → route to callback #324.
(Forminator conditional logic = operator; glue/JS where needed = code.)
**TDD (code side):** a helper `ukv_funnel_is_standard($nat,$res,$status,$purpose):bool` used by the branch; unit-test it.
**Commit:** `feat(elig): funnel branch before payment`.
**Operator (manual):** wire the Forminator conditional show/hide + the quote-route.

## Unit 7 — Bespoke-quote pricing  (file: `ukv-quote.php`)
**Adds:** on a manual_review order — a "Quote" field + a "Send payment link" action (nonce+cap) that records the
custom price (`ukv_quote_amount`) + generates a Stripe Payment Link (live key at launch; build the record + a
placeholder link now), journey note. Standard lane keeps fixed tiers untouched.
**TDD:** setting a quote stores the amount; the action is gated; standard orders unaffected.
**Commit:** `feat(elig): bespoke-quote pricing for manual-review`.

## Unit 8 — Flows + copy
**Change:** add the "Eligibility screen" step to delivery-framework Stage 1, detailed-process Phase 0/1, runbook
1.2–1.3, recipes Step 1, page-copy apply/checker; add `insurance_required` as a destination flag feeding
required-docs. Update SOP box to surface eligibility lane.
**Commit:** `docs(elig): eligibility step in flows + insurance flag`.

## Unit 9 — End-to-end test  (file: `automation/test-eligibility-e2e.php`)
Drive both paths on a synthetic destination: (a) UK+UK+tourist → standard → flows + fixed tier; (b) non-UK →
manual_review → blocked → agent clears + sets quote → flows. Assert gate, router, quote, no fixed-tier-charge on
non-standard. Isolation-hardened; cleanup.
**Commit:** `test(elig): standard + manual-review e2e`.

---

## Build order + parallelism
Sequential where dependent: **Unit 1 → 2 → 3** (schema first, then box, then gate). Then **4, 5, 7** can parallelise
(independent files); **6** needs 1's helper; **8** docs anytime; **9** last (needs all).
Via the ukv-parallel-build skill: build Unit 1 solo (foundation, verify), then fan out 4/5/7, then 6, then 9.

## Acceptance (from the spec)
- UK+UK+tourist+no-refusal → `standard`, flows normally, fixed tier.
- Any other → `manual_review`, **blocked** until cleared; cleared → flows; referred → closed.
- Funnel never charges a fixed tier to a non-standard case; manual-review is quote-priced.
- All new axes captured + shown on the order; checker shows UK answer only for UK input.
- e2e green for both paths.

## What's mine vs operator
- **I build:** Units 1–5, 7, 8, 9 (code + docs) + the code side of 6.
- **Operator:** the Forminator field additions + conditional branch (Units 4/6), legal sign-off on D-items, live
  Stripe key for real payment links.
