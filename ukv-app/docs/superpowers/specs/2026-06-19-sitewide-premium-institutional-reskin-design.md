# Sitewide Premium Re-skin — Institutional (Petrol / Teal + Outfit)

**Date:** 2026-06-19
**Status:** Approved (design); pending implementation plan
**Owner:** Beyond Passports (InfactAI)

## 1. Purpose

Re-skin the public Beyond Passports site to a single, premium, **Institutional** visual
system that reads *dependable and official* while remaining unmistakably an independent
commercial service (never a government website). This replaces the current warm
"Terracotta + Viridian" theme sitewide.

Decision was reached through visual brainstorming (colour-science board → trust
personality → palette → typography science → type specimens):

- **Trust personality:** Institutional — "safe & official", cool authority.
- **Palette:** Petrol & Steel (cool, distinct from gov.uk).
- **Terracotta:** fully retired (cool-only system).
- **Type:** Outfit — one geometric premium sans, self-hosted.
- **Signature:** the "travel document" motif (boarding-pass cards, passport-stamp tick,
  MRZ-style mono micro-labels).

Non-goal: changing site structure, copy, routes, or features. This is palette + type +
signature + premium polish only.

## 2. Design tokens (the locked system)

All AA-checked for text on their intended backgrounds.

### Colour
| Role | Token | Value | Notes |
|------|-------|-------|-------|
| Primary / CTA | `--cta` / `--gold` | `#155E7A` | petrol; hover `#0F4A61` |
| Ink / heroes / footer / headings | `--ink` / `--navy` | `#16222E` | cool near-navy |
| Trust accent (verified/live/done) | `--stamp` | `#2E9A8C` | teal |
| Trust accent text-on-light | `--stamp-text` | `#1F6E63` | AA on white/paper |
| Accent on dark (dots, eyebrows on ink) | (new) `--on-dark` | `#79CFC2` | |
| Eyebrow-on-ink / soft | `--soft` | `#A9CCDA` | replaces peach `#F2C2AC` |
| Accent tint (pill bg) | (page tints) | `#E2F1EE` | replaces sage tints |
| Surface | `--paper` | `#F4F6FA` | cool paper |
| Edge | `--edge` / `--paper-edge` | `#dde3ec` | steel |
| Muted text | `--muted` | `#5d6b76` | |

### Old → new replacement map (sitewide sweep)
The current theme is petrol's predecessor (terracotta + viridian, just applied). Replace:

- `#C75D38` and `#b04e2c` (terracotta CTA / hover) → `#155E7A` / `#0F4A61`
- `#22282b` (ink/navy) → `#16222E`
- `#2F8F86` (viridian accent) → `#2E9A8C`; `#226B64` (accent text) → `#1F6E63`
- `#84D2C9` (on-dark accent) → `#79CFC2`
- `#F2C2AC` (soft peach) → `#A9CCDA` (soft steel)
- `#F4F5F6` (paper) → `#F4F6FA`; `#e6e8ea` (edge) → `#dde3ec`
- mesh/glow `rgba(199,93,56,*)` (terracotta) → `rgba(21,94,122,*)` (petrol)
- mesh/glow `rgba(47,143,134,*)` (viridian) → `rgba(46,154,140,*)` (teal)
- accent tints `#E3F1EF` → `#E2F1EE`; soft `#BEE2DD` → `#cfe6e0`
- WhatsApp green `#25D366` stays (brand colour of WhatsApp, not ours).

Note the navy-mesh signature now uses **petrol top-right + teal bottom-left**.

### Type
- **Family:** Outfit (single family). `--display` / `--body` / and a mono fallback all
  reference Outfit; refs use Outfit with `font-variant-numeric: tabular-nums`.
- **Weights to self-host:** 400 (body), 600 (labels/medium), 700 (headings), 800 (optional heavy hero).
- **Scale:** h1 `clamp(38px,5vw,60px)` / 700; h2 ~27 / 700; h3 ~21 / 700; body 17 / 1.55;
  label 13 / 600; eyebrow 12 / 700 uppercase `.16em`.
- **Self-hosting:** all weights live in `public/fonts/*.woff2` with `@font-face`
  (`font-display:swap`) in `ukv.css`. **Remove the Google Fonts `<link>`** from
  `layouts/public.blade.php` and every standalone page (`track`, `checklist-result`,
  `checklist-pdf`, etc.). Rationale: Google Fonts is blocked for some users (privacy
  extensions / networks), which silently breaks the brand; self-hosting also removes a
  third-party request and a GDPR data-transfer (IP to Google).

### Signature — the "travel document" system
- **Boarding-pass card:** white card + perforated **ink** stub (radial-gradient notch),
  UK→DESTINATION dashed route with a small mark. Already exists on track + checklist;
  promote to destinations and key CTAs.
- **Passport-stamp tick:** a rotated circular "CHECKED & READY" stamp in teal for
  verified/checked states (hero card, QA-passed, reviews).
- **MRZ micro-label:** mono, letter-spaced refs/dates, e.g. `UKV<<2026<<004821<<GBR`.
- Used sparingly — signature lives in one place per view; everything else stays quiet.

## 3. Architecture / where changes land

- **`public/assets/ukv.css`** — single source of design tokens + component styles. The
  bulk of the change: token values, `@font-face` block, mesh gradient stops, the
  signature component classes. Page-scoped `@push('head')` styles in Blade inherit via
  `var(--*)`, so most pages need no per-page edits — but hardcoded hex values in page
  styles must be swept (same approach as the prior viridian sweep).
- **`resources/views/partials/site-header.blade.php` / `site-footer.blade.php`** — already
  the shared chrome; drop the Google Fonts link here + in standalone pages.
- **Self-hosted fonts** — `public/fonts/outfit-*.woff2` (already downloaded during
  brainstorming; prune the other candidate fonts not used).
- **Standalone pages** (`track`, `checklist-result`, `checklist-pdf`) carry their own
  `:root` token blocks — update those to the petrol values too.

## 4. Rollout order (each = preview-verify-commit)

1. **Tokens + fonts in `ukv.css`** (the global swap) + remove Google links. Screenshot
   home + a content page + a navy-hero page.
2. **Hardcoded-hex sweep** across all blades (terracotta/viridian → petrol/teal), same
   scripted sed approach as before, then 0-leftover grep.
3. **Signature components** — boarding-pass + stamp + MRZ classes; apply to home hero,
   destinations cards, track, checklist.
4. **Per-page premium polish** (home → destinations → apply → tools → guides → about →
   contact → compare → reviews → legal), section-by-section via the existing
   preview→pick→apply loop.
5. **Full QA** — 144-test suite + screenshot every public route + AA contrast pass.

## 5. Compliance (load-bearing)

- The petrol palette and "official document" stamp must **not** imply a government
  service. Keep the "Independent service — not a government website" topbar and footer
  disclaimers prominent on every page.
- Deliberately avoid gov.uk's `#1d70b8` (royal blue) and `#00703c` (gov green).
- No fabricated stats/ratings; "4.9★ / 12,000+" stat band uses only real, substantiable
  figures (replace placeholders with verified numbers before go-live, or soften copy).
- Self-hosting fonts also removes the Google Fonts IP transfer (minor GDPR plus).

## 6. Testing

- `php artisan test` — full suite stays green (144 tests). Watch for any test asserting
  old hex values or the Google Fonts link.
- Headless-Chrome screenshot every public route; eyeball for contrast + leftover warm
  colours.
- Manual AA spot-check: petrol-on-white, teal-text-on-paper, white-on-petrol,
  soft-steel-on-ink.

## 7. Risks & mitigations

- **Big visual shift** (warm→cool, terracotta gone): mitigated by previewing the full
  system first (done + approved).
- **Hardcoded hex misses:** mitigated by grep-to-zero after the sweep.
- **Font flash/missing weights:** `font-display:swap` + self-host all used weights.
- **Stat-band placeholders:** flag for real figures before launch (ties to existing
  backlog #299 data verification).

## 8. Out of scope

Routes, copy, features, admin/Filament theme, emails (unless they reference the retired
hexes), logo (separate brainstorm #294–298).
