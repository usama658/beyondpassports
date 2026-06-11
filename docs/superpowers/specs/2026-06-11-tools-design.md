# Tools — Sub-project Design Spec

**Date:** 2026-06-11 · **Parent:** `2026-06-11-uk-outbound-evisa-site-design.md` (subsystem #3 of 6)
**Depends on:** Foundation (#1). **Reads:** visa JSON defined in Content Silos (#2). **Feeds:** /apply funnel (#4).

## Goal
Two client-side tools that capture top-of-funnel intent and route to `/apply`: a **visa checker** ("do I need a visa?") and a **photo maker** ("visa photo"). Both server-render crawlable content for SEO, then hydrate an interactive widget.

## Decisions (locked)
| Item | Choice |
|---|---|
| Visa checker data | Reads same `data/visas/<slug>.json` as money pages (#2). UK-citizen path authoritative; other nationalities = generic fallback. |
| Photo maker | 100% client-side canvas; no upload, no PII stored. |
| Photo specs data | `data/photo-specs.json` (size_mm, dpi, bg, face_ratio per country). |
| SEO pattern | Server-render intro + static spec table (crawlable); interactive widget hydrates on top. Result states are JS, NOT separate URLs (avoid thin pages). |
| Routing | `/tools/do-i-need-a-visa/` · `/tools/visa-photo/`. |

## Tool 1 — Visa checker (`/tools/do-i-need-a-visa/`)
- **Inputs:** nationality dropdown (default British) + destination dropdown (populated from JSON slugs).
- **Logic (client-side JS):** fetch `data/visas/<dest>.json`.
  - UK citizen → authoritative result from `visa.required_for_uk`, `type`, `max_stay_days`, `validity_days`, `govt_fee_gbp`.
  - Non-UK nationality → generic card: "Rules differ by nationality — check the official source," no fee/CTA promise (we only own UK-outbound data).
- **Output card:** status badge ("eVisa required — 90 days") → requirement checklist (`visa.requirements`) → fee summary (service tiers + govt fee) → **primary CTA `Start application`** → `/apply?dest=<slug>&product=visa` · secondary link to money page `/<slug>/`.
- **SEO:** page server-renders an intro + a static destination summary table (all launch destinations, crawlable). Widget hydrates over it. Result states do not change the URL.
- **Edge cases:** unknown/missing JSON → "We don't cover this destination yet" + link to contact; never a JS error.

## Tool 2 — Visa photo maker (`/tools/visa-photo/`)
- **Flow:** upload photo → crop/zoom against a guide overlay → pick destination preset → optional auto light/white background (basic canvas fill) → download JPG/PNG at target px + DPI.
- **Presets:** `data/photo-specs.json`, e.g.:
```jsonc
{
  "uk":       { "label": "UK",       "size_mm": [35, 45], "dpi": 600, "bg": "light-grey", "face_ratio": 0.75 },
  "us":       { "label": "USA",      "size_mm": [51, 51], "dpi": 300, "bg": "white",      "face_ratio": 0.65 },
  "schengen": { "label": "Schengen", "size_mm": [35, 45], "dpi": 600, "bg": "light-grey", "face_ratio": 0.75 },
  "india":    { "label": "India",    "size_mm": [51, 51], "dpi": 300, "bg": "white",      "face_ratio": 0.70 }
}
```
- **Privacy:** all processing in-browser; no upload to server; no PII stored. Disclaimer: "Check your destination's exact photo requirements."
- **Output:** single image download. 6-up print sheet = **out of scope for v1** (YAGNI).
- **SEO:** server-render intro + a per-country photo-spec table (crawlable). Widget hydrates over it.
- **Cross-link:** CTA back to `/apply` and to relevant money pages.

## Implementation notes
- Widgets are vanilla JS (or a small Alpine.js sprinkle) enqueued only on the two tool pages — no heavy framework (keeps CWV budget from Foundation).
- JSON fetched from the theme data dir; cached by the browser; same files as #2 (no duplication).

## Out of scope
Money pages/guides (#2) · funnel + payment (#4) · CRM (#5) · IDP-specific tooling (#6).

## Acceptance criteria
- Checker returns the correct authoritative result for a British citizen for all 8 launch destinations, with a working `/apply?dest=…&product=visa` CTA.
- Non-UK nationality shows the generic fallback (no fee/CTA promise).
- Checker + photo pages each server-render a crawlable spec table (visible with JS disabled); no separate result URLs.
- Photo maker crops to the selected preset and downloads an image at the correct pixel size for the spec's mm + dpi, entirely client-side (verify no network upload).
- Both tools pass Lighthouse mobile ≥ 90 (no CWV regression vs Foundation baseline).

## Open items
- Accurate photo specs per country (size/dpi/bg) for the preset list.
- Background-removal quality: basic fill for v1; evaluate a client-side ML matting library later if needed.
