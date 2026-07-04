# Social production — capabilities mapped to the cycle

Owner reference. Who/what does each stage of the automation cycle
(`docs/social-automation-cycle.md`), split across: **Claude code** (deterministic,
in-repo), **installed skills**, **free tools**, and **human**. Goal: maximise the
deterministic (code/skill) share, minimise fragile GUI automation.

## Environment (verified 2026-07-04)
- Node v24 + npm 11 ✓ · Chrome + Edge ✓ · Python PIL 12 ✓ · FFmpeg ✗ (optional install)
- Skills present: nano-banana-edit, open-images-for-free-use, google-slides,
  seo-images, landing-page-copywriter, frontend-design, ui-ux-pro-max,
  web-design-guidelines, find-skills.
- No video-editing skill. No Canva/CapCut connector in this CLI.

---

## Stage-by-stage capability map

### 1. Plan
- **Claude code:** content-log sheet (`docs/content-log-template.csv`), calendar from
  `docs/social-campaigns.md`.
- Human: approve the monthly batch.

### 2. Produce — content
- **Copy / captions / hooks / reel scripts:** Claude + **landing-page-copywriter** skill.
- **Static graphics** (carousels, cards, checklist PDFs): Claude **code** (HTML→PNG +
  PIL) — deterministic, brand-locked, free. Design principles from **frontend-design /
  ui-ux-pro-max / web-design-guidelines**.
- **Carousels / decks:** **google-slides** skill or HTML→PNG.
- **Photos (license-safe):** **open-images-for-free-use** skill (verifies license from
  metadata — safer than hand-picked stock).
- **AI photo edits / background swaps / touch-ups:** **nano-banana-edit** skill
  (Google Nano Banana 2 via RunComfy CLI — paid endpoint; replaces "open in Canva to
  tweak"). Free fallback = re-render in code.

### 3. Produce — video
- **Animated / templated reels** (refusal cards, stat counters, kinetic text,
  slideshows): Claude **code — Remotion** (video-as-React, renders MP4; Node+Chrome
  present, bundles own FFmpeg). Deterministic.
- **Slideshow-from-images / caption-burn on existing clips:** **FFmpeg** (install once:
  `winget install Gyan.FFmpeg`).
- **Live filmed footage** (founder on camera, real b-roll): **CapCut, human.** No
  skill/connector/code does this reliably.

### 4. Schedule
- **UTM links:** Google Campaign URL Builder (standard in `docs/social-tools-setup.md`).
- **Post scheduling:** Buffer (LinkedIn+IG+FB); long-tail manual.
- (GUI automation via claude-in-chrome possible but fragile — not for production.)

### 5. Distribute + engage
- Auto-post: Buffer.
- Engagement (<60 min first hour), Reddit/Quora answers: **human** — never automate.

### 6. Capture
- **Already built (Claude code, in-app):** checklist + eligibility tools, WhatsApp CTA
  (`SiteStats::chatUrl`), floating WA button, HubSpot sync, first-touch `utm_source`.

### 7. Nurture
- **Already built:** `ukv-emails` drip (fires on checklist/apply submit), HubSpot Free,
  WhatsApp template first-reply.

### 8. Measure → feed back
- Native analytics (Meta Business Suite, LinkedIn) + GA4 (UTM) → content-log KPI columns.
- Weekly glance, monthly report vs `docs/linkedin-kpis.md`. Human review.

---

## Capability summary

| Need | Deterministic (code/skill) | Human/tool |
|---|---|---|
| Copy / scripts | ✅ Claude + landing-page-copywriter | — |
| Static graphics | ✅ code (HTML→PNG + PIL) | Canva (optional tweak) |
| Photos | ✅ open-images-for-free-use skill | — |
| AI image edit | ✅ nano-banana-edit skill (paid CLI) | — |
| Carousels/decks | ✅ google-slides / code | — |
| Animated reels | ✅ code (Remotion) | — |
| Caption-burn / stitch | ✅ FFmpeg | — |
| Live-footage video | ❌ none | CapCut (human) |
| Schedule | Buffer + UTM builder | — |
| Engagement | ❌ never automate | human |
| Capture / nurture / measure | ✅ already built (app) | human review |

## Read
- ~85% of production = **deterministic** (Claude code + skills): all copy, static
  graphics, photos, carousels, animated reels, plus the whole capture→nurture→measure
  back-end (already built).
- ~15% = **human**: filming face content (CapCut), first-hour engagement, monthly review.
- **Zero fragile GUI automation** in the production path.
- Only paid pieces: nano-banana AI edit (optional), Meta ad spend (optional).

## Setup to unlock the code-video path
1. Scaffold **Remotion** (`npm i` + brand components + render script) → animated reels.
2. Optional: `winget install Gyan.FFmpeg` → slideshow/caption-burn.
3. Optional: RunComfy CLI → nano-banana AI edits.
Static graphics + photos + copy + carousels already work now (no install).
