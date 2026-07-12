# Lead-funnel section options — filling the gaps

_Companion to `lead-funnel-analysis.md`. Maps each funnel gap to the section option(s) that
fill it, plus the reusable-partial plan to build them. Last reviewed: 2026-07-12._

The analysis found the dominant gap is **R2 (email capture via the free document checklist)**,
missing from the top and middle of the funnel. This doc lists the concrete sections that close it.

## Gap → section options

| Gap (from analysis) | Section option(s) to fill it | Captures | Where it lives |
|---|---|---|---|
| **R2 missing on TOFU** (guides-hub, reviews, compare, LPs) | **A. Checklist lead-magnet band** — "Get your free personalised checklist" + destination select + email field, posts to `/document-checklist` | email + destination | above the closing CTA band |
| **R2 missing on MOFU** (schengen hub, tools) | Same band **A**, or **B. Inline checklist card** (compact) | email + destination | mid or bottom |
| **R2 on destination detail** | **C. Per-country checklist CTA** — "Get your free {Greece} checklist" (destination pre-filled) | email (destination known) | after the fees/requirements section |
| **Long-page scroller leak** (guides, dest detail) | **D. Mid-page inline strip** — one line "Not ready to apply? Get the free checklist ->" | click-through to magnet | ~50% scroll |
| **LP email fallback** | **E. Secondary link under the appt form** — "Prefer email? Get your checklist" | email | under `lp-appt-form` |
| **Confirmation reassurance** | **F. WhatsApp reassure strip** — "Questions? Message us" | chat lead | confirmation page |
| **Nurture (checklist -> apply)** | **G. Email sequence** (not a section) — day 0 checklist, day 2 "ready to start?", day 5 offer callback | re-engage | backend (email) |

## The efficient build: one partial, several variants

A-E are the **same block with a `variant` param** — build ONE reusable partial and drop it
everywhere:

```blade
@include('partials.lead-checklist', ['variant' => 'band'])            {{-- TOFU/MOFU full width --}}
@include('partials.lead-checklist', ['variant' => 'card'])            {{-- compact card --}}
@include('partials.lead-checklist', ['variant' => 'inline'])          {{-- mid-page one-liner --}}
@include('partials.lead-checklist', ['destination' => $destination])  {{-- per-country prefill --}}
```

So the whole R2 fix = **1 partial + 3 variants**, placed on 7 pages. Plus **F** (tiny WhatsApp
strip, one include) and **G** (one nurture email, separate).

## Section mockups (pick the look)

### A — Band (primary recommendation)
```
┌───────────────────────────────────────────────┐
│  FREE · no obligation                          │
│  Know exactly what you need before you apply   │
│  Get your personalised Schengen checklist —    │
│  tailored to your trip, sent to your inbox.    │
│  [ Choose destination ▾ ] [ email ] [ Get it → ]│
└───────────────────────────────────────────────┘
```

### B — Card (compact, sidebar / mid-content)
```
┌──────────────────────┐
│ 📋 Free checklist     │
│ Tailored to your trip │
│ [ Get yours → ]       │
└──────────────────────┘
```

### D — Inline strip
```
── Not ready to apply? Get your free document checklist → ──
```

## Recommendation

Build one **`partials.lead-checklist`** partial (variants band / card / inline + destination
prefill), then:

1. **Band** onto guides-hub, reviews, compare, schengen-hub.
2. **Per-country** variant onto destination detail (prefills the destination).
3. **Inline** strip mid-guide (catch scrollers).

That single partial closes the entire R2 column (4 -> 11 pages). Then add **F** (confirmation
WhatsApp strip) and **G** (nurture email) after.

## Build order

1. `lead-checklist` partial, variant A (band) — build + preview.
2. Roll band onto the 4 TOFU/MOFU pages.
3. Per-country variant on destination detail.
4. Inline variant mid-guide.
5. F (confirmation reassurance) + E (LP fallback).
6. G (nurture email sequence).

## Wiring notes

- Posts to the existing `/document-checklist` flow (`ChecklistController::result` -> tailored
  checklist -> `/checklist/{token}/send` captures email + fires `NewChecklistLead` to the owner
  and HubSpot). No new backend needed for A-E; the magnet + lead capture already exist.
- Destination prefill: pass `$destination->name` (matches the apply-form + checklist wizard
  destination handling, which resolves by slug or display name).
- Keep the site's warm-light petrol/teal tokens + `cta-band` styling; no ukv.css collisions
  (namespace new classes, e.g. `.lc-*`).
