# beyondpassports.com visual identity, LOCKED 2026-10-07

**Status:** LOCKED by the owner on 2026-10-07 after reviewing nine palette directions, nine font pairings, six dark and three light treatments of direction B, three glass layouts and four gold heading treatments. This file is the single source of truth for colour, type and hero treatment on the `.com` market pages. It supersedes the recommendations in `2026-10-07-com-home-visual-tokens.md` (UI/UX agent, recommended direction C) and adopts the font recommendation of `2026-10-07-com-brand-typography-brief.md` (Fraunces + Plus Jakarta Sans). Both earlier documents stay as research record.

**Owner decision chain:** direction B teal-gold (from the first three) -> dark treatments -> B6 glass-panel layout -> B5 headline system (Fraunces) -> G1 deep teal glow surface -> T1 gold highlight stroke on the key phrase.

**Preview of the locked state (local only, never published):** `storage/previews/preview-com-g1-gold.html` section T1. Supporting previews: `preview-com-b-glass-fraunces.html` (G1-G3), `preview-com-b-dark.html`, `preview-com-b-dark-2.html`, `preview-com-b-light.html`, `preview-com-specimen.html`.

## 1. Why this, in one paragraph (competition-led)

Teal and gold is the brand's own logo v2 (`bp-logo-v2-tealgold.svg`), so the `.com` reads as one family with `.co.uk`. Dark deep-teal surfaces fix the one weakness the UI/UX agent found in B on white: the logo teal `#30B8B8` is 2.4:1 on white and unusable for text, but on `#0B3B47` it passes AA for buttons and links. Fraunces is a warm old-style serif no competitor uses (curl-verified 2026-10-06: Atlys Denton + Inter, iVisa Manrope, VFS InterUI, TLScontact Playfair + Libre Baskerville, Visard Inter, FlyFast Poppins); nine of eleven competitors run geometric or grotesque sans on white, so a serif-led dark hero is the open lane. The frosted panels carry the "we watch, you book, we prepare" steps and the two-instalment price as plain cards, which beats iVisa's buried refund ladder and Atlys's movable "guaranteed by" date. Residual risk, accepted by the owner: teal is also VFS Global's and iVisa's colour; distance comes from the serif, the gold, the glow and the honesty copy, never from flat teal blocks.

## 2. Tokens (CSS custom properties, paste into the market layout once)

```css
:root{
  /* brand */
  --bp-teal:        #30B8B8; /* logo mark, primary CTA fill, links on dark, step numerals */
  --bp-teal-deep:   #0F4C5C; /* text and CTA on light body sections */
  --bp-teal-ink:    #0B3B47; /* hero and footer surface */
  --bp-teal-night:  #06181C; /* text on teal CTA */
  --bp-gold:        #D9B46A; /* accent on dark: tags, hairlines, highlight stroke */
  --bp-gold-text:   #85631E; /* gold as text or link on white (darkened for AA; re-measure in build) */
  --bp-ink:         #0B1F26; /* body text on light */
  --bp-paper:       #FFFFFF; /* light body sections */
  --bp-paper-tint:  #F3F8F9; /* alternate light band */

  /* hero / dark surfaces (G1) */
  --bp-hero-bg:     radial-gradient(1200px 520px at 80% -10%, rgba(48,184,184,.30), transparent 60%), var(--bp-teal-ink);
  --bp-hero-fg:     #F3F8F9;
  --bp-hero-muted:  #BFD6DA;
  --bp-hero-line:   rgba(255,255,255,.16);
  --bp-glass-bg:    rgba(255,255,255,.07);
  --bp-glass-line:  rgba(255,255,255,.17);
  --bp-glass-blur:  10px;
  --bp-highlight:   rgba(217,180,106,.55); /* T1 stroke behind the key phrase */

  /* CTAs */
  --bp-cta-bg:      var(--bp-teal);
  --bp-cta-fg:      var(--bp-teal-night);
  --bp-cta-bg-light:var(--bp-teal-deep);  /* on white sections */
  --bp-cta-fg-light:#FFFFFF;
  --bp-cta-ghost:   #B9D3D8;               /* outline button border on dark */

  /* status pills, same on dark and light (tint bg, dark text, dot) */
  --bp-st-available-bg:#DDEFE4; --bp-st-available-fg:#17452E; --bp-st-available-dot:#2F6B4F;
  --bp-st-filling-bg:  #FBEBC8; --bp-st-filling-fg:  #5C3D05; --bp-st-filling-dot:  #B8780D;
  --bp-st-limited-bg:  #F6DADA; --bp-st-limited-fg:  #5E1C1C; --bp-st-limited-dot:  #B23A3A;
  --bp-st-check-bg:    #E4E7EC; --bp-st-check-fg:    #2B3545; --bp-st-check-dot:    #5B6B7D;

  /* misc */
  --bp-focus:       0 0 0 3px rgba(48,184,184,.55);
  --bp-radius:      18px; --bp-radius-sm: 12px; --bp-radius-pill: 999px;
  --bp-shadow:      0 30px 60px -30px rgba(0,0,0,.6);
  --bp-space:       8px; /* 8px grid: 8 16 24 32 48 64 */

  /* type */
  --bp-font-display:"Fraunces", Georgia, "Times New Roman", serif;
  --bp-font-body:   "Plus Jakarta Sans", "Segoe UI", Roboto, Arial, sans-serif;
}
```

Measured contrast (WCAG 2.1, 2026-10-07): hero text 11.3:1; hero muted 8.0:1; gold on hero 6.2:1; CTA text on teal 7.5:1; teal link on hero 5.0:1; ink on white 17.0:1; muted on white 5.8:1; deep teal on white 9.5:1; pills 8.4 to 10.0:1. Gold as link on white measured 4.37:1 at `#9A7224`, so the token is darkened to `#85631E`; re-measure once in CSS.

## 3. Typography

Google Fonts, one request, roman only, latin + latin-ext (Afrikaans, Quebec French):
`https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500..700&family=Plus+Jakarta+Sans:wght@400..700&display=swap`

| Role | Face | Weight | 375px | 1280px | Line height | Tracking |
|---|---|---|---|---|---|---|
| h1 hero | Fraunces, opsz 144 | 600 | 40px | 72px | 1.02 | -0.02em |
| h2 section | Fraunces | 600 | 30px | 44px | 1.08 | -0.015em |
| h3 panel title | Fraunces | 600 | 20px | 22px | 1.2 | -0.01em |
| price numerals | Fraunces | 600 | 32px | 38px | 1.0 | -0.01em, tabular lining |
| body | Plus Jakarta Sans | 400 | 17px | 18px | 1.55 | 0 |
| lead | Plus Jakarta Sans | 400 | 17px | 18px | 1.55 | 0 |
| small / trust line | Plus Jakarta Sans | 500 | 13px | 13px | 1.5 | 0 |
| uppercase tag | Plus Jakarta Sans | 700 | 12px | 12px | 1.2 | 0.14em |
| button | Plus Jakarta Sans | 700 | 16px | 16px | 1.3 | 0 |

Rules: key phrase in h1 gets the T1 stroke via `background:linear-gradient(transparent 68%, var(--bp-highlight) 68%)` on an inline element with no text decoration; one phrase per headline; never italic for emphasis in body; dates spelled "Thu 14 Nov 2026"; prices with thousands separators and no decimals when whole. Open check from the typography brief: confirm `font-variant-numeric: tabular-nums` works in Plus Jakarta Sans; fallback for fee tables is Source Sans 3.

## 4. Composition rules (from G1 / B6)

1. Hero and footer dark (`--bp-hero-bg`); body sections white, alternating with `--bp-paper-tint`.
2. Hero grid: copy left (tag, h1, lead, two CTAs, trust line), one frosted panel right (four-step "how it works"). Below the hero: three frosted fee cards (total, start, remainder) in the hero surface before the first white section.
3. Frosted panel fallback: `@supports not (backdrop-filter)` raises panel background to `rgba(255,255,255,.12)`; content must stay readable without blur.
4. Primary CTA teal fill, secondary CTA ghost outline; one primary per screen. WhatsApp CTA is the ghost on dark, teal fill on white.
5. Gold appears only as: wordmark, uppercase tags, hairlines, the highlight stroke, trust-line dots. Never as a button, never as a background block.
6. Status pills identical on dark and light, tint background with dark text and a dot; never solid.
7. Logo: `bp-logo-v2-tealgold.svg` on dark and on white. Optimise the raster-in-SVG assets to true vector or WebP before launch (18 KB each today).
8. Imagery: no stock photos of passports, flags or smiling travellers; if any image, real centre exteriors or document crops with consent. Icons: one stroke set (Lucide), 1.5px.
9. Touch targets 48px min; 8px spacing grid; radius 18 / 12 / pill.
10. Copy guards unchanged: no em-dashes, no "guaranteed", "fast-track", "priority", "early appointment", no "package".

## 5. What this locks and what stays open

Locked: palette tokens, two typefaces and weights, hero composition and highlight treatment, logo variant, pill system. Open for the home page section-stack spec: section order below the hero, availability board placement, trips block, FAQ, trust strip integration with the locked disclaimer partial, mobile hero stacking, and whether the UK `.co.uk` adopts Fraunces (recommended by the brief, not decided).

## 6. Build notes

Implement as `resources/css/bp-com.css` loaded by the `.com` market layout only; SP1 Task 5 partials (`market-trust-strip`, `market-price`) and SP5 partials must read these tokens and drop their hard-coded Outfit / `#155E7A` / `#16222E` values. Tests: a build check that greps `.com` views for raw hex outside the token file.
