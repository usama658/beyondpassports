# Re-Application Promise — Rejection point-of-sale

**Date:** 2026-06-19
**Status:** Approved (design); pending implementation plan
**Owner:** Beyond Passports (InfactAI)

## 1. Purpose

Make **rejection** the lead point-of-sale — honestly. Competitors (e.g. getbrazilvisa.com)
sell on an unsourced "95% approval rate" + an untermed "money-back guarantee." We cannot and
will not do that: our compliance line is **no approval guarantee, decisions are the
authority's, not a government service.** Instead we sell **confidence + a termed safety net**:

> **The Re-Application Promise** — *"If your visa is refused for something we could have got
> right, we re-prepare and re-submit it — free."*

The trust comes from (a) transparency about why visas actually get refused, (b) the real
checking process that removes the avoidable causes, and (c) a safety net with **published
terms** — the opposite of the competitor's vague badge.

## 2. The promise (locked policy)

**Mechanic:** one **free re-preparation & re-submission** if refused. The customer still pays
any **new government fee**. Effort-based ("we re-do the work"), never outcome ("approval
guaranteed").

**✅ Covered — one free re-prep & re-submission, if refused for:**
- Anything we prepared, checked, or filed (incomplete/incorrect paperwork we handled; a
  requirement we should have flagged; a formatting/translation/apostille error we handled;
  wrong category/typo we introduced).
- A **discretionary** refusal on a file that was complete & correct, where a stronger
  re-submission has a real chance (e.g. officer wanted more evidence of funds/ties we can
  bolster). **[REVISITABLE CLAUSE — see §7; written so it can be removed without touching the
  rest of the promise.]**

**❌ Excluded (the honest, risk-capping line):**
- Fraud / false or withheld information.
- **Undisclosed ineligibility** — applicant never qualified (wrong status, prior refusal hidden).
- Missed deadline / applicant didn't supply a requested document.
- Government rule changed after submission.

**The bound:** **one** free re-preparation per order. If we assess a re-submission genuinely
cannot succeed, we say so and apply the existing refund/cancellation policy (#72) instead of
stringing the customer along.

**Gating:** included on **all standard paid orders** (not gated to a premium tier — it is the
lead lever). **Bespoke / manual-review lane orders are excluded** from the automatic promise
and handled case-by-case (their pricing is already bespoke).

## 3. Surface area

Dedicated page **+ reused everywhere**:
- **`/promise`** — canonical explainer page (route + controller/Blade view, in the public silo,
  wired into nav + sitemap).
- **Reusable module** — a compact "Re-Application Promise" strip/badge partial
  (`partials/promise-strip.blade.php`) using the travel-document signature (passport-stamp +
  one-liner + link to `/promise`). Included on: homepage, each money/destination page
  (`destinations/show`), the apply funnel (pre-payment reassurance), and the reviews page.
- **Legal** — a termed clause at `/legal#re-application-promise`, linked from `/promise`.

## 4. `/promise` page structure (sections)

1. **Hero** — the promise headline + honest sub ("The decision is always the authority's. What
   we control, we get right — and stand behind."). Petrol/teal Institutional system; Outfit;
   passport-stamp signature.
2. **Why visas get refused** — the real, common causes (incomplete documents, wrong visa
   category, weak supporting evidence, formatting/translation/apostille errors). **No invented
   percentages or success rates** — descriptive only.
3. **How we rejection-proof your application** — the real process, tied to existing features:
   eligibility screening → AI document check (#99) → human pre-submission QA gate (#75) →
   final review before anything is submitted. This is the substance competitors fake.
4. **The promise in plain English** — the ✅ covered / ❌ excluded table from §2; link to full
   terms.
5. **If it still happens** — the handling journey: clear refusal reason, one free re-prep, or an
   honest refund if unwinnable. Reassuring, not salesy.
6. **Compliance strip + CTA** — "Independent service · not a government website · no approval
   guarantee" + "Start your application" / "Check what I need."

## 5. Operational reality (the promise must be real, not just copy)

- **Rejection-reason taxonomy (#73):** add a **promise-eligibility classification** per reason
  — one of `our_error` (covered), `discretionary_covered` (covered, revisitable), or
  `excluded`. Single source of truth for "does the Promise apply."
- **Filament / OrderService:** when an order is marked **rejected**, the captured reason's
  classification auto-determines promise eligibility and surfaces the next action — trigger the
  **free re-preparation workflow** (re-open the production line at the prep stage, no new
  service fee) or the **decline → refund** path (#72). Reuses the existing rejection + refund
  flows; no new payment logic.
- **Customer comms:** the rejection notification email states the refusal reason, whether the
  Re-Application Promise applies, and the next step. Extends existing lifecycle email (#80/#178).
- **Audit:** every promise determination logged on the order (who/when/which classification) for
  dispute defence.

## 6. Compliance (load-bearing)

- Never state or imply an approval rate, success %, or guaranteed outcome. Language is always
  "we re-prepare / re-submit," "what we control," "stand behind our work."
- Keep "not a government website / no approval guarantee" prominent on `/promise` and the module.
- The promise terms are real and published (`/legal`), with clear exclusions — the honesty is
  the differentiator and the legal protection.
- No fabricated stats anywhere; any numbers must be substantiable (or omitted).

## 7. Revisitable clause

The **discretionary-refusal coverage** (§2 covered list, 2nd bullet) is the highest-exposure
part and most likely to be pulled back. It is isolated: it maps to the single
`discretionary_covered` taxonomy classification. Removing it later = (a) reclassify that bucket
to `excluded`, (b) update the ✅/❌ table copy on `/promise` + the legal clause. No other part of
the system depends on it.

## 8. Testing

- Feature test: `/promise` renders 200, contains the promise + the exclusions + the compliance
  strip, and the module links to it.
- Guard test: `/promise` and the module contain **no** approval-rate / "guarantee approval" /
  fabricated-% language (assert absence of forbidden phrases).
- Unit: rejection reason → promise-eligibility classification mapping (covered/excluded) returns
  the expected bucket for representative reasons.
- Existing suite (146) stays green.

## 9. Out of scope

Pricing/tier changes; the bespoke-lane manual handling (case-by-case, unchanged); any
approval-rate analytics; redesign of the rejection/refund engines themselves (we reuse #72/#73/
#76/#178). New copy for `/promise` is in scope; rewriting other pages is not.
