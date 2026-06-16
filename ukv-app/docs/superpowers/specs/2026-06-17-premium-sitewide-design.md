# Premium site-wide design roll-out — design spec

**Goal:** lift the whole public site to a "$10K premium" feel on the locked brand (Terracotta & Sage on cool-grey, Plus Jakarta Sans), building on the already-shipped layered-depth home hero. Consistent, restrained, photo-where-meaningful, subtle motion. No new features, no data model changes — pure presentation.

**Decisions (locked):**
- Scope = **full pass** (every public page hero + shared components).
- Photos = **meaningful only** (destination photos on money pages; gradient-mesh elsewhere; clean text heroes on guides/legal).
- Motion = **subtle** (accent float, hover lift, existing scroll-reveal; reduced-motion gated; no parallax/shimmer).
- Destination/money page hero = **cinematic full-bleed photo band** (distinct from the mesh heroes on functional pages).
- Execution = **central-layer-first** (extend `ukv.css` once, pages consume shared classes).

## Central layer (`public/assets/ukv.css`) — Wave 0, owner: parent

Names kept so existing markup inherits. Additions/upgrades:
- **Type scale:** fluid `clamp()` for h1 (`clamp(34px,5vw,56px)`), h2 (existing), h3; tighten display tracking to `-.025em` on h1; lede slightly larger.
- **Elevation tokens:** `--lift-1:0 18px 44px -26px rgba(40,50,70,.30)` (= existing `--shadow`), `--lift-2:0 26px 56px -28px rgba(40,50,70,.40)`, `--lift-3:0 36px 70px -30px rgba(40,50,70,.50)`.
- **Cards:** `.pass`/`.checker`/`.faq` radius → 18px; hover uses `--lift-2`.
- **Buttons:** keep `.btn` recipe; ensure smooth transform+shadow transition (already present).
- **`.glass-chip`** helper: white rounded chip, `--lift-3`, terracotta price line — the floating accent.
- **`.mesh-hero`** + **`.mesh-hero--sm`**: terracotta→sage radial gradient-mesh on near-white; grid (left copy / right card); compact variant drops the right card + padding. Page may set its own page-scoped tints.
- **`.photo-hero`**: full-bleed `background-size:cover` photo, min-height ~440px, left-heavy dark scrim (≥.55 at text zone), white eyebrow/h1/lede + CTA + optional `.glass-chip`. Degrades to mesh when no photo.
- **`.section-head`/`.sec-head` rhythm:** more breathing room (margin-bottom 40px), bigger eyebrow→h2 gap.
- **Motion:** `@keyframes hp-float`/`-up` already on home; promote a shared `.float-soft` (≤9px, 7–8s) for accents. Card hover-lift transitions. All inside existing `prefers-reduced-motion` guard.
- Retire `.skyband` decorative band + skyline-SVG hero fillers (replaced by photo/mesh).

Also owned by parent in Wave 0: `layouts/public.blade.php` (shell sanity), no skyline filler in shared partials.

## Per-page application — Wave 1 (7 parallel agents, disjoint files, none touch `ukv.css`)

| Agent | Files | Hero | Notes |
|---|---|---|---|
| 1 | `destinations/show.blade.php` | `.photo-hero` (destination photo + scrim + price/validity glass-chip + CTA) | replace `.dhero`+`.skyband`; fallback to mesh if no `image_path` |
| 2 | `destinations/index.blade.php` | `.mesh-hero` | promote photo destination cards (home card style) |
| 3 | `apply.blade.php` | `.mesh-hero` + floating intake card | keep all form fields/validation/lanes intact |
| 4 | `tools.blade.php`, `track.blade.php`, `documents.blade.php` | mesh / mesh--sm | keep checker + lookup behaviour |
| 5 | `find-a-centre.blade.php`, `document-checklist.blade.php`, `checklist-result.blade.php` | mesh / mesh--sm | keep forms + results |
| 6 | `about.blade.php`, `contact.blade.php`, `reviews.blade.php` | mesh / mesh--sm | remove skyline filler; premium cards |
| 7 | `compare.blade.php`, `driving-abroad.blade.php`, `guides/index.blade.php`, `guides/show.blade.php`, `legal.blade.php` | mesh (compare/idp/guides-index) · clean text (guide-show/legal) | guide-show stays editorial/no-photo for readability+SEO |

Home (`home.blade.php`) already done — left as the reference.

**Agent contract:** Read-before-Edit; use Wave-0 shared classes; page-scoped `@push('head')` only for genuinely page-specific bits; preserve every form field, route, `@php`, schema block, compliance strip; never touch `ukv.css`; honour `@@`-escaped JSON-LD; no `word@directive` gluing.

## Removals (replaced, never left blank)
1. skyline-SVG decorative filler bands (`.skyband`, home skybacker already gone, testimonial avatar skyline OK to keep as avatar).
2. fake MRZ byline string `UKV<2026<004821<<<` in home testimonial.
3. flat single-shadow cards → elevation tokens.
4. plain SVG-only heroes → mesh/photo.

## Compliance / a11y (must hold)
- `.photo-hero` text white on ≥.55 scrim → WCAG AA; `.mesh-hero` ink on light → AA.
- reduced-motion disables all float/transition.
- keyboard focus rings preserved (existing canonical ring; dark-surface variant covers `.photo-hero`).
- No copy/claims change: not-a-gov-site, fee-separate, express≠faster-decision, no-guarantee all stay.

## Testing / verification (parent)
- `php artisan view:clear` → `php artisan test` (expect 141 green — guards Blade/route integrity).
- Smoke every public route (200, assets resolve, no Blade exception).
- Grep for leftover `skyband`, MRZ string, raw `.dhero`.
- Visual check via live tunnel; spot-check `.photo-hero` contrast + reduced-motion.
- No new test files (pure presentation). Commit on green.

## Out of scope
Swapping placeholder destination photos for cleared/branded imagery (owner, #95-adjacent); expanding destinations (#236); any feature/data/logic change.
