# Rejection Silo — Information Architecture & Content

**Date:** 2026-06-19
**Status:** Approved (design); pending implementation plan
**Owner:** Beyond Passports (InfactAI)
**Builds on:** `2026-06-19-rejection-proposition-design.md` (the offer mechanic — referenced, not
redefined here) and `docs/superpowers/research/competitor-analysis.md` (5-competitor teardown).
**Keyword research:** Ahrefs (GB + global), 2026-06-19 — see §2.

## 1. Purpose

Make **rejection** the lead point of sale as a real, navigable silo: a rejection-centred
hub-and-spoke that funnels **inform → reassure → convert**, grounded in actual UK search demand,
produced honestly by the existing guide engine, and gated against the compliance line. This spec
covers the **information architecture, page model, content, population, compliance and testing**.
The **offer mechanic** (Re-Application Promise, exclusions, ops wiring) lives in the proposition
spec and is referenced, not duplicated.

## 2. Keyword findings that shaped the IA (Ahrefs, GB + global, 2026-06-19)

- **"uk visa refusal"** (100 GB / 400 global) is the **parent topic** of the cluster — "rejection"
  and "refused" terms all resolve to a "…refusal" parent. → root = `/visa-refusals`; "refusal" is
  the canonical word; "rejection" is a synonym (meta + 301, not separate pages).
- **"chances of getting uk visa after refusal"** (300 / 700) — the single biggest term → the
  `/reapply` spoke is the highest-demand page; frame it "your chances after a refusal" (honest, no
  published rate).
- **"uk visa refusal letter / email"** (~450 global combined) → a demand-driven spoke
  `/visa-refusals/refusal-letter` ("understand your refusal letter") — which is also the Atlys
  "Reasons Decoded" conversion move. Search validates it.
- **"uk visa appeal"** (70 / ~650, traffic potential 500) → an informational `/appeal` spoke
  (appeal vs reapply; we inform, we do not provide regulated appeal advice — OISC scope, #130).
- **Visa-type refusal** terms (visitor / student / spouse) are each parent topics → a visa-type
  spoke axis (`/visa-refusals/{type}`).
- **"uk visa rejection rate"** (300 global) — people search the rate; we answer the question
  honestly **without publishing any rate** (pivot to "what you control").
- GB volumes are small because most UK-visa-refusal search is **overseas** (India/Nigeria/Pakistan);
  the per-country `/visa/{country}/refused` pages capture that demand, the evergreen hub captures
  the GB + global head terms.

## 3. The silo (URL map)

Two roots on purpose: an evergreen cluster under `/visa-refusals`, and per-country pages embedded
in the destination silo (next to the money page, where the buyer already is).

```
─ THE OFFER (bespoke Blade) ─────────────────────────────────
/promise                         H1 "The Re-Application Promise" · ✅/❌ on page · turnaround · no-pivot line
/legal#re-application-promise    formal termed clause + disclaimer + liability cap

─ REJECTION CLUSTER (root: /visa-refusals) ──────────────────
/visa-refusals                   HUB (bespoke) — "UK visa refused — or worried it might be?"
│
├ EVERGREEN SPOKES (guide engine)
│  /visa-refusals/reapply          "Your chances after a UK visa refusal" (highest demand)
│  /visa-refusals/refusal-letter   "Understand your refusal letter" (decode move)
│  /visa-refusals/appeal           "Appeal vs reapply" (informational; not regulated advice)
│
├ REASON SPOKES (guide engine · taxonomy #73 · all reasons)
│  /visa-refusals/reasons/{reason} insufficient-funds, false-documents, ties-to-home,
│                                  credibility-interview, sponsorship, immigration-history, …
│
├ VISA-TYPE SPOKES (guide engine · full set)
│  /visa-refusals/visitor-visa · /student-visa · /spouse-visa · (work/family as data grows)
│
─ PER-COUNTRY (destination silo) ────────────────────────────
/visa/{country}/refused          guide engine (route exists) · all 8 destinations
                                  breadcrumb ↑ /visa-refusals · links its money page
```

"rejection" terms: 301 → the "refusal" canonical; used in meta, never as duplicate pages.

## 4. Page types → engine mapping (hybrid)

Bespoke landing pages where we **sell**; guide engine for the ~30+ spokes that **rank**.

| Page | Produced by | Route → handler | Template |
|---|---|---|---|
| `/promise` | Bespoke Blade | `PromiseController@show` | `public/promise.blade.php` |
| `/visa-refusals` hub | Bespoke Blade (queries engine for spoke list) | `RejectionController@hub` | `public/visa-refusals/hub.blade.php` |
| `/visa-refusals/reasons/{reason}` | Guide engine | `GuideController@showRefusalReason` | guide `show` (reason skeleton) |
| `/visa-refusals/{slug}` (reapply, refusal-letter, appeal, visitor-visa, student-visa, spouse-visa) | Guide engine | `GuideController@showRefusal` | guide `show` (refusal skeleton) |
| `/visa/{country}/refused` | Guide engine (exists) | `GuideController@showCountry` topic=`refused` | country guide + refused section |

**Routing guard:** register `/visa-refusals/reasons/{reason}` before `/visa-refusals/{slug}`, and
constrain `{slug}` with a `where` regex that excludes `reasons` (mirrors the existing `{topic}`
regex) — no collision.

**Data model (extends built pieces — no new engine):**
- `guides` table (#242): add `cluster` (value `refusal`) so the hub can query the whole cluster, and
  a nullable `reason_code` linking a reason-guide to its taxonomy #73 row.
- Taxonomy #73 (`RejectionReason`) stays the single source (see §6).
- `GuideType` enum: add the refusal sub-types (`RefusalReason`, `RefusalTopic`, `RefusalType`), so
  the compliance gate, factuality validator and freshness already apply.

## 5. Nav, module, internal linking, SEO

**Primary nav (two top-level):** `Refused?` → `/visa-refusals`, `Our Promise` → `/promise`; also in
the Tools/Help mega-menu and the footer (rejection column); mobile nav mirrors both.

**Reusable module `partials/promise-strip.blade.php`:** passport-stamp + one-line Re-Application
Promise + link → `/promise`. Placed on: home · every `/visa/{country}` · apply funnel (pre-payment)
· reviews · every rejection-cluster page. The **Turnaround Promise** is a separate small badge on
money pages + `/promise` (not in the strip).

**Internal-link graph:**
- Hub = pillar: links every spoke + every `/visa/{c}/refused`; receives a link from nav + each
  spoke's breadcrumb → concentrates authority on `/visa-refusals`.
- Every spoke → breadcrumb ↑ to hub + contextual link to `/promise`.
- Money page → names its top denial reason → links the matching `/reasons/{reason}` and its
  `/visa/{country}/refused`.
- `/reasons/{reason}` → document-checklist tool (#235) + `/promise` close.

**SEO / schema:**
- Canonical: every page self-canonical; "rejection" URLs 301 → "refusal".
- Schema: `BreadcrumbList` on spokes; `FAQPage` only for Qs whose answers are visibly on the page;
  `Article` with `datePublished`/`dateModified` from the "Reviewed {date}" freshness field (#245).
- Sitemap: auto via the GuideController registry (#206); hub + `/promise` added explicitly.
- GEO/AI-citability: "Reasons Decoded" + "how we stop it" written as self-contained citable blocks.
- Staging `noindex` stays until go-live (#300).

## 6. Content model + population

**Single source of truth = taxonomy #73 (`RejectionReason`).** Each row: `reason_code`, `slug`,
`decode` (plain-English "what the officer meant"), `recoverable|blocking`, `classification`
(`our_error | discretionary_covered | excluded`), `which_check` (eligibility screen / document check
/ human QA gate #75). The reason-guide body is generated from this row + the skeleton, so the
customer copy and the Promise-eligibility claim never diverge.

**Per-page content skeletons (locked from the teardown):**

Reason + per-country refused spoke:
```
H1 "Refused for {reason}? Here's what that actually means."
DECODE → RECOVERABLE/BLOCKING → HOW WE STOP IT (#73 check · "a real UK person checks") →
REAPPLY GUIDANCE (disclose prior refusal · reapply only when something changed) →
TOOL ENTRY (document-checklist) → PROMISE CLOSE (/promise) →
FRESHNESS "Reviewed {date} · we monitor UK visa rule changes" → COMPLIANCE STRIP
```

Evergreen spokes: `/reapply` = "your chances after refusal" (honest, no rate); `/refusal-letter` =
decode-the-letter walkthrough; `/appeal` = appeal-vs-reapply (informational). Type spokes =
type-specific refusal causes → link to the relevant reasons. Hub + `/promise` = bespoke conversion
copy (hub diagnose→reassure→convert; promise = ✅/❌ on page + named turnaround + no-pivot line).

**Voice rules (locked):** diagnose don't sell; lead with the decoded reason, not the offer; numbers
substantiable or omitted; "what we control" never "guaranteed"; "a real UK person checks every
application"; the line we own — *"We don't publish approval rates, because no honest service can
promise the authority's decision. We publish what we control — and stand behind it."*

**Population pipeline (built engine #242–245):**
```
taxonomy #73 + gov.uk-verified data (#129)
  → 1 AI draft (#244)
  → 2 factuality validator
  → 3 compliance publish GATE  ◄ blocks "approval rate" / "% approved" / "guarantee" / invented stat
  → 4 "Reviewed {date}" freshness + AI change-detection (#138/#245)
```
**Order:** fill taxonomy #73 → build the skeleton templates once → generate reason spokes → type
spokes → per-country `/refused` (country data × skeleton) → hand-seed the three evergreen spokes →
gate on every publish.

## 7. Compliance (load-bearing)

- The publish gate (#244) checks **rendered** output (body, titles, meta, schema) for forbidden
  phrasing: approval-rate / "% approved" / "guarantee approval" / "rejection-proof" (except as "we
  remove avoidable causes") / any invented stat.
- "Independent service · not a government website · the decision is the authority's" on every
  rejection page, `/promise`, and the module.
- `/promise` terms + `/legal#re-application-promise` disclaimer (we do NOT guarantee approval or any
  outcome · final decisions are the authority's · liability capped at the service fee paid) —
  solicitor sign-off (#130).
- Reason `classification` is the single source for both the customer claim and the ops
  promise-eligibility decision — they cannot diverge.

## 8. Testing

- **Compliance guard test (critical):** every rejection URL + `/promise` + the module render 200,
  contain the "not a government website" line, and contain **none** of the forbidden phrases
  (source-level + rendered assertions).
- Route tests: hub, `/promise`, a reason spoke, a type spoke, `/visa/{country}/refused`; breadcrumb
  + canonical present; `/visa-refusals/reasons/{x}` does not collide with `/visa-refusals/{slug}`.
- "rejection" → "refusal" 301 works.
- Unit: `RejectionReason` → promise-eligibility classification returns the expected bucket.
- Module links to `/promise`; sitemap includes the new pages.
- Existing suite stays green.

## 9. Build scope & phases (full sweep)

Sequenced so nothing input-blocked stalls the rest.

| Phase | Ships | Blocked? |
|---|---|---|
| F1 Foundations | routes + `RejectionController` + `PromiseController` + `guides` migration (`cluster`, `reason_code`) + `GuideType` values + nav + module + `/promise` + hub shells | no |
| F2 Data | fill taxonomy #73 — all reasons: decode / classification / which-check | no (our own data) |
| F3 Reason spokes | generate all `/reasons/{reason}` via engine + gate | no |
| F4 Type + evergreen | visitor/student/spouse + reapply/refusal-letter/appeal | no |
| F5 Per-country | `/visa/{country}/refused` × 8 destinations | needs gov.uk-verified per-country refusal data (#129/#299) |
| F6 Polish + tests | schema, freshness, compliance guard suite, internal-link audit, full regression | no |

## 10. Required user inputs (block go-live copy, not structure)

1. **Named UK case-lead + verifiable credential** — else `/about` and pages render "our UK case
   team" with the personal-accountability copy and no fabricated credential.
2. **Legal sign-off** on `/promise` terms + the disclaimer (#130).
3. **Real, substantiable numbers** — else omitted (default). No approval rate, ever.

## 11. Out of scope

Paid add-on / Denial-Protection refund model (we include the Promise, not as a paid add-on);
subscription model; rebuilding the guide/refund/taxonomy engines (reuse #72/#73/#242–245); the offer
*mechanic* itself (defined in `2026-06-19-rejection-proposition-design.md`). New IA, pages, module,
nav, schema, content templates and the taxonomy-driven population are in scope; unrelated page
rewrites are not.
