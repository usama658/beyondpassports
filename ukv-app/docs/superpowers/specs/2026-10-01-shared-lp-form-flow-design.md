# Shared LP Form Flow — Design Spec

**Date:** 2026-10-01
**Status:** Draft for review
**Related:** [[lp-v2-keyword-variants]], [[flows-serve-not-inform]], [[refusal-logic]], [[ukvisa-validity-step]], [[schengen-service-pricing]], [[prod-deploy-shallow-clone]]

## 1. Context & intent

The 9 "gold" landing pages all run the **same** CTA modal qualifier ("Flow B", `#mov`) —
a multi-intent, multi-step form that qualifies a lead and hands off to WhatsApp. Today that
flow is **copy-pasted into 5 physical files** and is drifting:

- **9 URLs, 5 files:**
  - `lp-v2-preview.html` → served at 5 variant URLs by `LpVariantController`
    (`/schengen-visa-services-uk`, `-assistance`, `-application-help`, `-agents-uk`, `-consultancy`)
  - `lp-france.html`, `lp-spain.html`, `lp-germany.html`, `lp-netherlands.html`
    → served at `/schengen-visa/{country}` by the route closure in `routes/web.php`
- Flow logic (intents, step sequences, REFLOGIC verdict engine) is **byte-identical** across
  all 5. Only country identity, copy, and **prices** differ.
- Drift already present: prices diverged (lp-v2 vs country), and the country clones carry
  find/replace bugs (wrong dial codes, duplicate `ISOM` keys — **already hotfixed 2026-10-01**,
  see §7).

**Goal:** extract the modal flow into **one shared source**, injected into all 5 files, so a
change lands on all 9 pages at once and drift stops. UX unchanged except the agreed pricing
correction and bug fixes. **Scope: modal (Flow B) only.** The hero inline flow (Flow A) is
left untouched — it legitimately differs per page (country picker on lp-v2 vs France centre
picker on country pages).

## 2. Approach (approved: Approach 1)

**Route-injected Blade partial**, mirroring the existing `analytics-head` / `utm-capture`
injection the serve paths already use.

1. New `resources/views/partials/lp-flow.blade.php` = the single canonical copy of:
   modal markup (`#mov` … steps) + modal `<style>` + modal `<script>` (the `ST/SEQ/CFG`
   engine, REFLOGIC, `msg()`/`doClose()`, modal phone combo) + guarded shared verdict helpers.
2. Both serve paths inject it **before `</body>`** (one line each), plus a tiny context shim
   `<script>window.BP_FLOW_DEST=…</script>` injected **before** the partial.
3. The inline modal markup + modal CSS + modal IIFE are **stripped** from all 5 static files.
4. Page CTAs (`onclick="mOpen('…')"`, `mOpenAny()`, `mOpenSearch()`) keep working unchanged —
   the injected partial defines those globals.

Static files stay static (fast iteration preserved). No build step. Smallest blast radius.

**Rejected:** external JS/CSS assets (more moving parts, extra requests, against the
all-inline convention); converting country pages to Blade `@include` (rewrites the deliberate
static-serve model — out of scope, YAGNI).

## 3. Components & interfaces

### 3.1 `partials/lp-flow.blade.php` (new, single source of truth)
Carries, in this order:
- **Modal markup** — the `#mov` overlay and every `.mstep[data-k]` (plan, preference,
  refreason, purpose, country, flex, tim, details, closeWa, closeCall). Lifted from the
  current lp-v2 copy (canonical).
- **Modal CSS** — the two modal `<style>` blocks (`.mov`, `.mmodal`, `.mstep`, `.mtile`,
  `.mpl-*`, `.v-*`, `.mpc*`, `.msnt*` …). One copy only.
- **Modal JS (IIFE)** — `ST/SEQ/CFG/INT`, `mOpen/mOpenAny/mOpenSearch/mClose/mBack/next`,
  pickers, `REFBASE/REFMOD/REFLEAN/REFCOPY/refTier`, `PLAN`, `mPlanFill`, `mDetailsFill`,
  `msg()`, `doClose()`, modal phone combo (`window.mPcStatus/mPcFull`).
- **Shared verdict helpers** — `window.CTRY`, `vSlot/vTime/vSnap/vChip/vVerdictInner`,
  `urgHTML/diffHTML` — each **guarded** (`window.X = window.X || …`) so the partial is
  self-sufficient but does not clobber a page's own copy (the hero flow keeps using the
  page's copy; see §5).

### 3.2 Context shim — `window.BP_FLOW_DEST`
Injected by the serve path immediately before the partial:
- Country route: `window.BP_FLOW_DEST = {dest:'France', iso:'fr'}` (etc. per `{country}`).
- lp-v2 (`LpVariantController`): `window.BP_FLOW_DEST = null`.

The partial's entry points read it:
- `mOpenAny()` / all CTA openers call `mOpen(intent, BP_FLOW_DEST?.dest, BP_FLOW_DEST?.iso)`.
- When dest is set, `mOpen` already **splices the `country` step out of `SEQ`** (existing
  behaviour) → country locked to the page, picker skipped. On lp-v2 (null) the full picker shows.

### 3.3 Serve-path changes
Both paths already inject `analytics-head` (after `<head>`) and `utm-capture` (before `</body>`).
Add the flow injection to both. To avoid a **third** copy of identical assembly logic, extract
a small shared helper:

- New `app/Support/LpAssembler.php` (or a controller trait) with
  `render(string $path, array $variantSwaps = [], ?array $flowDest = null): Response` that does:
  read file → optional hero swap → inject analytics-head → inject `BP_FLOW_DEST` shim +
  `partials.lp-flow` before `</body>` → inject `utm-capture` → return response.
- `LpVariantController::show()` calls it with `$flowDest = null` + the variant swap map.
- The `/schengen-visa/{country}` closure calls it with
  `$flowDest = ['dest'=>ucfirst($country),'iso'=>$iso]`, where `$iso` comes from a small local
  map `['france'=>'fr','spain'=>'es','germany'=>'de','netherlands'=>'nl']` (the route's
  `where('country', …)` already whitelists exactly these four).

(If the helper feels like over-reach at review, fallback = add the two injection lines inline
in both paths. Helper preferred: it also de-dups the serve logic, which is the same drift
problem one level up.)

### 3.4 The 5 static files (stripped)
Remove from each: the `#mov` modal markup, the modal `<style>` block(s), and the modal IIFE.
**Keep** everything else — hero flow (Flow A), hero CSS, shared-helper block, hero phone combo,
`postLead`, all page content. CTAs that call `mOpen*` stay as-is.

## 4. Canonical pricing (locked 2026-10-01)

`£49` replaces every `£39` ("today"/one-off) across all 9 pages. Lives **once** in the partial.

### PLAN cards
| Plan (`data-k=plan`) | Today | Then | Service fee |
|---|---|---|---|
| Slot Hunt (`start`) | £49 one-off | — | £49 |
| Full Service (`full`) | £49 | £109 | **£158** |
| Priority Concierge (`prime`) | £49 | £249 | **£298** |
| Refusal Recovery (`refusal`) | £238 one payment | — | **£238** |

### Preference tiles (now + later = fee)
| Tile | now | later | totals |
|---|---|---|---|
| Start `p40` | £49 | pay £109 later | £158 (Full, instalment) |
| Prime `p85` | **£89** | pay £209 later | £298 (Prime) |
| Full `p140` | £158 in full | — | £158 |

**Intentional dual-number (owner-confirmed):** Prime "today" is **£89 on the preference tile**
but **£49 on the PLAN card**. Not a bug — two entry framings. (Flip to £89/£209 on the PLAN
card only if owner later asks.)

All `split`/`fine`/`then`/`now` strings in `PLAN` and the tile `then`/`num` spans must be
rewritten to match the tables above. Old values (£39, £99, £119, £138, £145, £158-as-full-on-
lp-v2, £159, £184, £194, £198, £238-old, etc.) must not remain in the canonical.

> Note: these figures supersede the £130 (£40+£90) in [[schengen-service-pricing]]; update that
> memory after sign-off.

## 5. Shared helpers & load order (correctness)

- The partial is injected **before `</body>`**, i.e. **after** all page scripts (hero flow,
  shared-helper block). So modal globals are defined last; page CTAs (`onclick`) fire only on
  user click, by which time `mOpen` etc. exist. ✓
- The hero flow (Flow A, kept) uses the page's own `window.CTRY`/verdict helpers, defined
  earlier in the page — unaffected by the partial. The partial's guarded helpers only fill a
  global if absent, so they never overwrite the page copy. ✓
- Modal phone combo (`window.mPcStatus/mPcFull`) moves into the partial; hero phone combo
  (`window.pcStatus/pcFull`) stays in the page — different names, no collision. ✓
- `postLead` stays defined in the page (hero uses it too); the modal calls the global. The
  partial must **not** redefine it. Guard: partial references `window.postLead` and no-ops
  safely if absent.
- External globals `window.bpWaUrl` / `window.bpUtm` continue to come from `utm-capture`
  (injected after the partial). Both are used with `||` fallbacks, and only at click time, so
  injection order is safe.

## 6. Error handling & edge cases

- `BP_FLOW_DEST` missing/malformed → treat as null → full country picker (safe default).
- Country route already `abort_unless(is_file($path), 404)`; unchanged.
- Hero swap on lp-v2 unchanged; flow injection independent of it.
- If `partials.lp-flow` fails to render, the page still serves (wrap the extra injection so a
  render error can't 500 the page — match current defensive `stripos`/`strripos` guards).

## 7. Bugs fixed as part of this work

1. **Phone dial codes (DONE 2026-10-01, pre-spec hotfix):** Spain/Germany/Netherlands clones
   had a corrupted France row (`["<Country>","FR","+33"]`) and a duplicate `ISOM` key. Restored
   the France row + repaired `ISOM` to `{Spain:'es',France:'fr',Italy:'it',Germany:'de',Greece:'gr'}`
   on all three. Each page keeps its own correct row (ES/+34, DE/+49, NL/+31). *These files will
   be stripped of the modal anyway, but the hero combo shares the same tables, so the fix stands.*
2. **Price drift:** collapsed to the single canonical set in §4.
3. **Prime tile vs card number mismatch:** resolved per §4 (now an owner-confirmed dual framing).

## 8. Testing

Laravel HTTP feature test `tests/Feature/LpFlowSharedTest.php` over all 9 routes:
- Modal markup present **exactly once** (`id=mov` count == 1) on every route.
- Canonical prices present (`£49`, `£158`, `£298`, `£238`).
- **Removed** prices absent in the modal: `£39`, `£138`, `£145`, `£184`, `£194`, `£99`, `£119`,
  `£159`, `£198`. (Note: `£158` and `£238` are **canonical now** — assert present, not absent.
  Scope the grep to the injected flow markup, not the whole page, since hero/other sections may
  legitimately carry other figures.)
- Country routes: `BP_FLOW_DEST` shim has the right `dest`/`iso`; picker-skip asserted via
  shim presence.
- lp-v2 routes: `BP_FLOW_DEST` is null (full picker).
- Smoke: `analytics-head` + `utm-capture` still injected (no regression).
- Manual: open each of the 9 live-equivalent pages locally, run the modal end-to-end
  (eligibility, refused, a price intent), confirm WhatsApp message builds with correct dest +
  prices.

## 9. Rollout

- Per [[slot-board-toggles]] convention, new flow design goes to **staging first**, not
  straight live.
- **Hard cutover (locked 2026-10-01).** No feature flag. The inline modal copies are stripped
  from the 5 files in the same change that adds the partial + injection; parity is guaranteed by
  the feature test (§8). A flag would re-introduce the two-source drift this work removes.
  Verify on staging, then deploy.
- Deploy via [[prod-deploy-shallow-clone]] (`fetch origin && reset --hard origin/master` +
  `view:clear` + `cache:clear`).
- No-auto-deploy: nothing ships without explicit ask ([[no-auto-deploy]]).

## 10. Out of scope

- Hero inline flow (Flow A) — untouched.
- The `#faPop` / `#fbPop` mini sibling flows on country pages — untouched.
- Converting any page to Blade templates.
- Pricing anywhere outside the modal (hero bands, standalone pricing sections) — not changed
  unless a later task asks.
