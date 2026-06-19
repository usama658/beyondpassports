# Rejection Proposition — Beyond Passports' point of sale

**Date:** 2026-06-19
**Status:** Approved (design); pending implementation plan
**Owner:** Beyond Passports (InfactAI)
**Supersedes:** `2026-06-19-re-application-promise-design.md` (offer-only) — that content is folded in here.

## 1. Purpose & strategy

Make **rejection** the lead point of sale — honestly. Competitor teardown (getbrazilvisa,
Atlys, Sherpa) shows the winning pattern and where we must diverge for compliance.

**What the leaders do best (adopt):**
- **A named, badged rejection safety net at the buy button** (Atlys "AtlysProtect" auto-included;
  Brazil "Free resubmission if rejected"; Sherpa "Reapply & Rejection Protection"). Risk-reversal
  at the point of purchase is the closer.
- **A "why applications get refused" section with specifics** (Brazil: *"6 common document
  mistakes that cause denials… insufficient income proof (35% of rejections)"*) — loss-aversion +
  specificity. Their single most persuasive block.
- **An honest-looking DIY-vs-us comparison** and a **"what we learned from N applications" data
  block** — numerical authority, reads credible not salesy.
- **A named, credentialed, *verifiable* reviewer who "reads every page"** (Brazil's Camila:
  OAB licence + registry link + "no junior associates, no outsourced prep"). Personal
  accountability is their #1 trust lever.

**Where we MUST diverge (compliance — load-bearing):**
- **No approval-rate %** (their "95%") and **no untermed "money-back guarantee" badge.** We never
  imply a guaranteed outcome; the decision is always the authority's; we are not a government
  service. Our edge = the *honest, termed* version, which is also the more defensible one.

**Proposition:** *"Most visa refusals are avoidable. We make sure yours isn't one of them —
and if it's ever refused for something we should have caught, we re-prepare and re-submit it free."*

## 2. The offer — Re-Application Promise (locked)

- **Name:** **Re-Application Promise** (brandable, like AtlysProtect). Badged at every CTA.
- **Structure:** **single, included on all standard paid orders** (not tiered; Atlys-style
  frictionless). Bespoke / manual-review lane excluded (handled case-by-case).
- **Mechanic:** one **free re-preparation & re-submission** if refused; customer pays any new
  **government fee**. Effort-based wording ("we re-do the work"), never "approval guaranteed."
- **Boldness = lean+:** if our re-prep *also* fails for our error → **service-fee refund**. The
  government fee is never refunded (future "Protect" upgrade could add it, insurance-style — out
  of scope now).
- **Bound:** one free re-prep per order; if we assess a re-submission can't succeed we say so and
  apply the refund/cancellation policy (#72) instead.

**✅ Covered:** anything we prepared/checked/filed (incomplete/incorrect docs, a requirement we
should have flagged, formatting/translation/apostille errors we handled, wrong category/typo we
introduced); **and** a **discretionary** refusal on a complete & correct file where a stronger
re-submission has a real chance. **[REVISITABLE — isolated to the `discretionary_covered`
taxonomy bucket; removable without touching the rest, see §8.]**

**❌ Excluded:** fraud / false or withheld info; undisclosed ineligibility (never qualified);
missed deadline / applicant didn't supply a requested doc; government rule changed after submission.

## 3. Surface area & silo

Dedicated page **+ reused everywhere**, organised as a rejection-centred hub-and-spoke that
funnels **inform → reassure → convert**.

- **Hub `/visa-refusals`** — pillar: "Visa refused — or worried it might be?"
- **Spokes (guides engine #242–245):**
  - `/visa-refusals/why` — why visas get refused (= taxonomy #73)
  - `/visa-refusals/reapply` — can you reapply / reconsideration
  - `/visa-refusals/reasons/{reason}` — one page per refusal reason
  - `/visa/{country}/refused` — per-destination (uses existing `/visa/{dest}/{topic}` route)
- **`/promise`** — the Re-Application Promise explainer (offer + terms).
- **Reusable module** `partials/promise-strip.blade.php` — passport-stamp signature + one-line
  promise + link to `/promise`; placed on home, each `/visa/{country}`, the apply funnel
  (pre-payment), and reviews.
- **Nav:** add **"Refused?"** → hub and **"Our Promise"** → `/promise`.
- **Legal:** termed `/legal#re-application-promise` clause.

## 4. The content that sells (the centrepiece — honest versions of their best sections)

These are the high-conversion blocks, used on `/promise`, the hub, and money pages:

1. **"The real reasons applications get refused — and how we stop each one."** Two-column: each
   common cause (from taxonomy #73) → the specific check that catches it (eligibility screen / AI
   document check #99 / human QA gate #75). Loss-aversion + specificity, *honest*: describe the
   causes; only show a number if it's substantiable from our own data, else omit.
2. **Honest DIY-vs-us comparison** (on `/compare` + a compact version on money pages): "On your
   own, the avoidable mistakes are yours to catch. With us, a UK team checks every one before
   submission." **No approval %, no fabricated metrics.**
3. **"What we see refused" data block** — real, anonymised, consented outcomes ("most common
   issue we fix before submitting is…"). Mirrors Brazil's "50+ visas" credibility section without
   inventing figures.
4. **CTA-adjacent Re-Application Promise badge** — the risk-reversal at the buy button.
5. **Voice rules:** name the anxiety → show control → back it with the Promise. "What we control,
   we get right." Never a guarantee. Specific > clever. No invented stats anywhere.

## 5. About page — build the authority they fake

Their strongest asset is a named, verifiable reviewer. Ours (`/about`, rejection-led):
- **H1:** *"We exist because most visa refusals shouldn't happen."*
- **Named UK case-lead** — real person, role, photo, and any membership/registration we can
  **link for verification** (their OAB-registry-link move). *"[Lead] personally checks every
  application before it's submitted — no outsourced document prep."* **[REQUIRED INPUT: real
  name + role + credential/registration. Cannot be fabricated; until supplied, render as "our
  UK case team" with the personal-accountability copy and no invented credentials.]**
- **How we work** — eligibility → AI document check → human pre-submission QA.
- **The Promise**, restated; real consented outcomes; compliance strip; CTAs (Apply / WhatsApp
  with an explicit response-time, e.g. "a real UK person replies within X hours").

## 6. Operational reality (the promise must be real)

- **Rejection-reason taxonomy (#73):** add a **promise-eligibility classification** per reason —
  `our_error` | `discretionary_covered` | `excluded`. Single source of truth for both the
  customer-facing claim and the ops decision.
- **Filament / OrderService:** order marked **rejected** → reason classification auto-determines
  promise eligibility → trigger the **free re-preparation workflow** (re-open the production line
  at prep, no new service fee) or the **decline → refund** path (#72). Reuses existing rejection
  + refund flows; no new payment logic.
- **Customer comms:** rejection email states the reason, whether the Promise applies, and the next
  step (extends #80/#178).
- **Audit:** log each promise determination on the order (who/when/classification).

## 7. Compliance (load-bearing)

- Never state/imply an approval rate, success %, or guaranteed outcome.
- "Independent service / not a government website / the decision is the authority's" prominent on
  `/promise`, the hub, and the module.
- Promise terms published at `/legal#re-application-promise` with clear exclusions — the honesty
  is both the differentiator and the legal protection. Ideally solicitor-reviewed (ties to #130).
- No fabricated stats; any number must be substantiable or omitted.

## 8. Revisitable clause

Discretionary-refusal coverage (§2) is the highest-exposure clause and most likely to be pulled.
It is isolated to the single `discretionary_covered` taxonomy classification. To remove later:
(a) reclassify that bucket to `excluded`, (b) update the ✅/❌ copy on `/promise` + the legal
clause. Nothing else depends on it.

## 9. Testing

- `/promise`, `/visa-refusals`, and a per-country `/refused` spoke render 200 with the promise +
  exclusions + compliance strip; the module links to `/promise`.
- **Compliance guard test:** these surfaces contain **no** forbidden phrasing — assert absence of
  "approval rate", "guarantee approval", "% approved", and any invented-stat patterns; assert the
  "not a government website" line is present.
- Unit: rejection reason → promise-eligibility classification returns the expected bucket for
  representative reasons.
- Existing suite (146) stays green.

## 10. Out of scope

Government-fee refunding (future "Protect" upgrade); tiered/add-on pricing; the bespoke-lane
manual handling (unchanged); fabricating any named person or credential; rebuilding the
rejection/refund engines (reuse #72/#73/#76/#178). New copy + the listed pages/module are in
scope; unrelated page rewrites are not.

## 11. Required user input before launch

1. **Named UK case-lead** — name, role, photo, verifiable credential/registration (or confirm
   "UK case team" wording for v1).
2. **Legal sign-off** on the published Promise terms (esp. the discretionary clause) — ties to
   #130 regulatory-scope review.
3. Any **real, substantiable** numbers we may cite (else all stats omitted).
