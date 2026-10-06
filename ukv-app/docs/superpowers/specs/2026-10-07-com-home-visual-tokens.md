# beyondpassports.com market home pages: colour and typography tokens

Date: 2026-10-07. Scope: /za, /ae, /us, /ca home pages (new .com site). Positioning: honest advisor, "we watch the calendar, you book, we prepare your file". Must never read as a government or visa-centre site, nor as a bot app. English only, Latin Extended glyphs required (Afrikaans, Quebec French). Dark mode out of scope.

Competitor evidence (3 fetches used): Atlys and iVisa home pages expose no inline CSS; Atlys indigo/purple is [unverified] (brief + memory). iVisa teal is partially verified (hero asset `cover_english_teal.webp`, Trustpilot green stars); exact hex [unverified]. Visard dark navy + lime, VFS institutional teal, local ZA/AE red/gold: all [unverified], taken from the brief.

Logo facts (sampled from PNGs): v2 gold lockup is a single gold #C09038 (near brand gold #C5963A); tealgold lockup = teal mark #30B8B8 + gold wordmark; reversed = teal mark + off-white wordmark. The logo teal is bright: 2.42:1 on white, so it can never be text or a CTA fill. All three SVGs are raster-in-SVG (18 KB each); optimise before shipping.

Note: the ui-ux-pro-max palette/font CSV database was not reachable in this checkout (data/ and scripts/ are dangling links), so palette and pairing choices below follow the skill's Quick Reference rules (4.5:1 text, 3:1 UI, semantic tokens, one primary CTA per screen, 8px rhythm) rather than its lookup tables.

## 1. Three directions

| | A. Inherit UK brand | B. Teal-gold | C. Ink and paper + stamp green |
|---|---|---|---|
| Primary | Navy #12224D / #0B1528 | Deep teal #0F7C7C (logo teal #30B8B8 mark only) | Ink #0B1528 |
| Surface | Cream #FFFDF8, white cards | White #FFFFFF, cool grey bands #F3F7F7 | Paper #FFFDF8, parchment bands #F4EFE4, white cards |
| Text | #0B1528, muted #5B6B7D (5.4:1) | #0F2A2A (15.2:1), muted #44546A | #0B1528 (17.9:1), muted #44546A (7.6:1) |
| Accent / CTA | Gold #C5963A fill, ink text (6.8:1); amber hover | Deep teal #0F7C7C fill, white text (5.0:1); gold hairlines | Deep green #2F6B4F fill, white text (6.3:1); gold logo only |
| Status family | Tailwind-style green/amber/red/slate (UK site) | Teal-tinted: teal/amber/clay/slate | "Ledger" family: 4 equal-chroma tints with dark text (see tokens) |
| Headline font | DM Serif Display | DM Serif Display | DM Serif Display |
| Body font | Plus Jakarta Sans | Plus Jakarta Sans | Plus Jakarta Sans |
| Beats | Atlys (editorial serif vs app chrome); Visard (warm vs dark bot) | Atlys (indigo); Visard (lime); local agencies (no red) | Atlys (paper letterhead vs purple app); iVisa and VFS (zero teal, zero blue CTA); Visard (no dark, no lime); local ZA/AE agencies (no red, no gold panels, no flags) |
| Risk | Gold + navy is the UAE/ZA agency cliché and nudges toward "crest/government"; gold CTA competes with WhatsApp green = two accents | Sits beside iVisa (teal, partially verified) and VFS (teal, unverified): the one look we must not resemble; logo teal unusable as text | Quieter, "boutique" feel; stamp green #5C9A7B fails text contrast (3.3:1) so CTA needs the deeper #2F6B4F; must keep paper warm enough not to look grey |

### Recommendation: C, ink and paper with stamp green

1. It is the only direction that is clear of every named competitor at once: no indigo (Atlys), no teal (iVisa, VFS), no dark-plus-lime (Visard), no red/gold/flag kit (local ZA/AE agencies).
2. One accent does two jobs: deep green fills both the WhatsApp CTA and the "Start" CTA, so WhatsApp's own green stops being a second competing accent and reads as part of our palette.
3. It carries the UK brand forward where it matters (same two typefaces, same ink navy, gold kept in the logo), so .com and .co.uk feel like one firm without exporting the gold-heavy look to markets where gold is the cliché.
4. Paper surfaces plus a serif headline read as "a letter from your advisor", which is the positioning; a navy/gold crest or a teal portal reads as "an office you queue at".
5. Every text pair clears AA with headroom (lowest 6.2:1), the status family keeps its meaning without colour alone (dot + label), and gold is reduced to a 1px hairline and the logo, so it cannot tip into the UAE agency look.

## 2. Token set (direction C)

```css
:root {
  /* Backgrounds */
  --bp-bg: #FFFDF8;            /* paper */
  --bp-bg-alt: #F4EFE4;        /* parchment section band */
  --bp-surface: #FFFFFF;       /* cards, inputs */
  --bp-surface-ink: #0B1528;   /* dark band: fee block, footer */

  /* Text */
  --bp-text: #0B1528;          /* on bg 17.9:1, on bg-alt 15.9:1, on surface 18.2:1 */
  --bp-text-2: #44546A;        /* muted: on bg 7.6:1, on bg-alt 6.7:1 */
  --bp-text-on-ink: #FFFDF8;   /* on surface-ink 17.9:1 */
  --bp-text-on-ink-2: #B8C2D1; /* on surface-ink 10.1:1 */

  /* Borders */
  --bp-border: #D9D2C3;        /* decorative hairline, cards and dividers */
  --bp-border-input: #7D766A;  /* form controls: 4.4:1 on bg (UI 3:1 met) */
  --bp-border-ink: #1E2B45;    /* dividers on surface-ink */

  /* Brand */
  --bp-brand-ink: #0B1528;
  --bp-brand-gold: #C5963A;    /* logo + 1px hairline only; as text ONLY on surface-ink (6.8:1), never on paper (2.7:1) */
  --bp-brand-gold-text: #7F5C1C; /* if gold text on paper is unavoidable: 6.0:1 */
  --bp-brand-stamp: #5C9A7B;   /* decorative stamp/tick/icon colour; never text on paper (3.3:1); as icon on ink 5.5:1 */

  /* CTA (one accent for both WhatsApp and Start) */
  --bp-cta: #2F6B4F;           /* white text 6.3:1; vs bg 6.2:1 */
  --bp-cta-hover: #255640;     /* white text 8.5:1 */
  --bp-cta-text: #FFFFFF;
  --bp-cta-on-ink: #7FB899;    /* CTA fill when placed on surface-ink; ink text 8.0:1 */
  --bp-cta-on-ink-text: #0B1528;

  /* Link */
  --bp-link: #255640;          /* on bg 8.3:1, on surface 8.5:1; always underlined */
  --bp-link-hover: #0B1528;

  /* Status family (pill bg / text / dot). Dot is UI colour, 3:1 vs pill bg. */
  --bp-status-available-bg: #E3F1E8; --bp-status-available-text: #1F5A3C; --bp-status-available-dot: #2F8A5B; /* text 7.0:1, dot 3.7:1 */
  --bp-status-filling-bg:   #FBF0D6; --bp-status-filling-text:   #7A4F08; --bp-status-filling-dot:   #9E6E0A; /* text 6.3:1, dot 3.9:1 */
  --bp-status-limited-bg:   #F9E3DD; --bp-status-limited-text:   #8A3324; --bp-status-limited-dot:   #C8553D; /* text 6.6:1, dot 3.5:1 */
  --bp-status-check-bg:     #E8ECF1; --bp-status-check-text:     #3B4A5E; --bp-status-check-dot:     #6B7B90; /* text 7.6:1, dot 3.6:1 */

  /* Focus ring */
  --bp-focus: 0 0 0 2px #FFFDF8, 0 0 0 4px #0B1528;  /* ink ring, paper gap; 17.9:1 vs paper */
  --bp-focus-on-ink: 0 0 0 2px #0B1528, 0 0 0 4px #7FB899;

  /* Shadow (ink-tinted, low) */
  --bp-shadow-sm: 0 1px 2px rgba(11, 21, 40, 0.06);
  --bp-shadow-md: 0 8px 24px rgba(11, 21, 40, 0.08);

  /* Shape and rhythm */
  --bp-radius-sm: 6px;  --bp-radius-md: 10px;  --bp-radius-pill: 999px;
  --bp-space: 8px;
}
```

Contrast pairs stated above are WCAG 2.x relative-luminance ratios computed on 2026-10-07. Lowest text pair in the set: paper on CTA 6.2:1. The legacy UK greens #16A34A / #22C55E are not in this set (3.3:1 and lower on white).

## 3. Type scale

Families and fallbacks
- Headline: `"DM Serif Display", "Iowan Old Style", "Palatino Linotype", Georgia, serif`. Weight 400 only (the family ships no other weight). Covers latin + latin-ext (Afrikaans ê ë ô, ʼn; French œ à ç). Verify ʼn (U+0149) renders on first build.
- Body and UI: `"Plus Jakarta Sans", "Segoe UI", system-ui, -apple-system, Arial, sans-serif`. Weights 400, 500, 600, 700. Covers latin + latin-ext + vietnamese.
- Numbers in fee blocks and status counts: `font-variant-numeric: tabular-nums`.

Google Fonts (one request, display=swap, preconnect to fonts.gstatic.com):
`https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap`

| Role | Font / weight | Mobile 375 | Desktop >= 1024 | Line height | Letter spacing |
|---|---|---|---|---|---|
| h1 | DM Serif Display 400 | 34px | 56px | 1.10 / 1.05 | -0.01em / -0.015em |
| h2 | DM Serif Display 400 | 26px | 38px | 1.15 / 1.10 | -0.005em / -0.01em |
| h3 | Plus Jakarta Sans 600 | 20px | 24px | 1.25 / 1.30 | 0 |
| Eyebrow / label | Plus Jakarta Sans 600, uppercase | 12px | 12px | 1.40 | 0.08em |
| Body | Plus Jakarta Sans 400 | 16px | 18px | 1.60 | 0 |
| Small / caption | Plus Jakarta Sans 400 (500 for pills) | 14px | 14px | 1.50 | 0 |
| Button | Plus Jakarta Sans 600 | 16px | 16px | 1.00, min height 48px | 0 |

Measure: body max 65ch; h1 max 18ch on desktop. Serif never below 20px.

## 4. Usage rules

- Gold appears in exactly three places: the logo, a 1px hairline rule under the eyebrow or above the footer, and as text only on the ink band (#C5963A on #0B1528). Never gold fills, never gold panels, never gold text on paper.
- CTA hierarchy: one filled green CTA per viewport (WhatsApp or Start, whichever is the section's job), one outline ink secondary. Both CTAs share --bp-cta; WhatsApp is distinguished by its glyph and label, not a different green.
- Status pills: tint background + dark text + 8px dot + label. Four states only (Available, Filling, Limited, Check with us). These hues are reserved for status and form validation and appear nowhere else on the page.
- Imagery: no stock photos of passports, flags, planes, stamps, maps with pins, or handshakes. Allowed: real team photos, real appointment-centre exteriors we took, and typographic or line-art compositions on parchment. Country identity comes from the name and the data, not a flag.
- Iconography: one set (Lucide), 1.5px stroke, 20px or 24px, drawn in --bp-text or --bp-brand-stamp; no emoji, no filled icons mixed with outline.
- Spacing: 8px unit; component gaps 8/16/24, section padding 48 mobile / 96 desktop; 16px minimum side gutter at all widths.
- Border radius: 6px inputs and pills' inner elements, 10px cards and buttons, 999px for status pills only. Nothing fully rounded except pills, so the page does not read as an app.
- Shadow: --bp-shadow-sm on cards at rest, --bp-shadow-md on the hero fee block and open menus only; never on buttons; 1px --bp-border always accompanies a shadow.
