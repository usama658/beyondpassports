# beyondpassports.com: brand and typography brief for the lead designer

Date: 2026-10-06. Scope: market homes for ZA, AE, US, CA on white layouts. Rule applied throughout: every recommendation names the competitor or customer evidence behind it. Companion spec: `2026-10-07-com-home-visual-tokens.md` (colour tokens; this brief does not change them).

## Part 1: competitor typography audit

Method: 10 WebFetch calls (markdown output stripped all CSS), then raw HTML/CSS read with curl on 2026-10-06. visard.co, flyfastvisa.co.uk and regalvisa.ae did not resolve; the live domains from our competitor doc were used instead. "Font" means what the stylesheet actually loads.

| Brand (URL, seen 2026-10-06) | Headline | Body | Personality | Tone signalled | Layout | Hero |
|---|---|---|---|---|---|---|
| Atlys (atlys.com) | Denton (self-hosted, `--font-denton`) | Inter, Lato | Sharp display serif over grotesque | Startup "app that happens to have a serif" | White, flag-gradient cards | Country cards, "Guaranteed On-Time Delivery" |
| iVisa (ivisa.com) | Manrope 400-800 (Google Fonts) | Manrope (DM Sans, Inter fallbacks) | Geometric | Friendly consumer tech | White | Travellers photo, Trustpilot, app QR |
| Visard (visard.io) | Inter (Framer default) | Open Sans | Grotesque/humanist | Bot app; dark + lime [unverified, from companion spec] | [unverified] | [unverified] |
| VisaHQ (visahq.com) | Helvetica/Arial | Helvetica/Arial, Roboto | Neo-grotesque system stack | Utility directory, dated | White | Dual dropdown form |
| VFS Global (vfsglobal.com) | InterUI Bold (self-hosted) | InterUI, Georgia | Grotesque | Institutional processing centre | White, banner carousel | "25 years" banner strip |
| TLScontact (tlscontact.com) | Playfair Display 700, Libre Baskerville | Montserrat 300/400 | Transitional serif + geometric | Corporate-official, WordPress | White | Photo + "CLICK HERE" |
| Breakout Holidays, UK (breakoutholidays.co.uk) | Plus Jakarta Sans | Inter, Dancing Script | Geometric + script | Travel-agent generic, blue #066aab | White | "Schengen Visa Consultancy" |
| FlyFast Holiday, UK (flyfastholiday.co.uk) | League Spartan | Poppins, Roboto Slab | Geometric | Loud discount travel, teal #0F766E | White with dark panels | [unverified] |
| Akira Tourism, UAE (akiratourism.com) | [unverified] | [unverified] | [unverified] | "Everything at 1 place", "Get Discounts Now!" | Dark photo overlay (white H1) | Promo |
| The Visa Agent, ZA (thevisaagent.co.za) | Open Sans | Open Sans, Roboto | Humanist/grotesque | Price-led agency | Dark photo carousel | Destination slides with prices |
| VisaPlace, CA/US (visaplace.com) | Montserrat | Montserrat (Elementor) | Geometric | Law-firm template | White | "Top Immigration Lawyers", press logos |

The typographic white space nobody occupies: a warm, readable old-style serif carrying real weight at headline sizes, paired with a humanist sans with tabular numerals, set large (17px+) on paper-white with controlled line length. Nine of eleven are geometric or grotesque sans throughout (Inter, Manrope, Montserrat, Poppins, Open Sans, Roboto). The two serif users are the extremes we must avoid: Atlys (sharp display serif, app chrome) and TLScontact (Playfair/Libre Baskerville, the official centre). Inter is the single most crowded choice (Atlys, VFS, Visard, Breakout). Warning: our live .co.uk templates load DM Serif Display + Inter in 58 files and Plus Jakarta Sans in only 11, so the UK brand is already drifting toward the VFS/Atlys body font. Fix that before cloning.

## Part 2: customer requirements

Audience: UK residents on visas, South Africans, UAE expats (many South Asian), US visa and green-card holders, Canadian PRs; 25-60; phones; anxious; scanning for price, dates and "is this a scam".

1. Body minimum 17px at 375px, 18px at 1280px, line-height 1.5 minimum. Source: WCAG 2.1 SC 1.4.12 (line spacing 1.5, paragraph spacing 2x) must hold without breakage; GOV.UK Design System sets body at 19px desktop/16px mobile. Design judgement: anxious readers on phones get 17 not 16.
2. Line length 45-75 characters, target 60-66 (Bringhurst, The Elements of Typographic Style, 2.1.2). Implement `max-width: 36em` on prose.
3. Prices and dates in lining tabular numerals (`font-variant-numeric: lining-nums tabular-nums`) so fee tables and date columns align; the fee is the largest numeral on the screen. Design judgement based on the scan pattern in our lead chats (price asked first).
4. Dates spelled, never numeric-only: "Thu 14 Nov 2026". US readers expect month-first, everyone else day-first; spelling the month removes the ambiguity. Design judgement.
5. Serif credibility: on-screen legibility between serif and sans shows no reliable difference (Arditi and Cho, "Serifs and font legibility", Vision Research 2005). Processing fluency does affect judged truth and effort (Song and Schwarz, Psychological Science 2008), so whatever we pick must render cleanly at small sizes. Errol Morris's 2012 NYT quiz found Baskerville nudged agreement; informal, not peer reviewed. Conclusion: a serif headline is a tone choice, not a trust guarantee; legibility is the trust lever.
6. Latin Extended subset mandatory: Afrikaans (ê, ë, ô, û, ŉ), Quebec French (é, è, ç, œ, à). Arabic not needed on .com. Load `latin, latin-ext` only.
7. Dyslexia-friendly traits: sans body, 16-19px, line spacing 1.5, left aligned, never justified, no italics or underline for emphasis, sentence case, off-white not pure white background (British Dyslexia Association Style Guide 2023). Our paper #FFFDF8 satisfies the last point.
8. Contrast: body text 4.5:1 minimum, large text and UI 3:1 (WCAG 2.1 SC 1.4.3, 1.4.11); tap targets 24px minimum (WCAG 2.2 SC 2.5.8). Companion spec tokens already clear 6.2:1.
9. Trust signalled typographically the way banks and gov.uk do it: one typeface family for all numbers, no all-caps shouting, no fake badges, consistent weights, visible hierarchy. GOV.UK uses a single sans, large body, left aligned, zero decoration. Wise pairs a display face with Inter body and keeps numbers plain [brand site observation, 2026]. Monzo custom typeface [unverified]. Law firms lean on a text serif plus white space (design judgement). Our version: numbers always in the sans, never in the serif.
10. Scam-check moments need quiet type: company number, registered address, "we are not the government" strip and refund terms set in body size (not fine print), text-2 colour at 7.6:1. Design judgement: small grey legal text is itself a scam signal to this audience.

## Part 3: brand requirement

Positioning: honest advisor. "We watch the calendar, you book, we prepare your file." Never government-looking (TLScontact and VFS own the official register), never a bot app (Atlys, Visard, iVisa own that), no "guaranteed" (Atlys headline literally says it), no glitter gold (UAE/ZA agency cliché; companion spec confines gold to logo and 1px hairline).

Must keep across .co.uk and .com (hreflang siblings read as one firm):
- Logo v2 lockups unchanged; ink navy #0B1528; paper #FFFDF8; gold #C5963A at logo and hairline only.
- Body typeface, type scale ratios, spacing rhythm, header and footer structure, disclaimer strip, component library.
- Voice: plain sentences, numbers up front, no urgency theatre.

May change per market:
- Headline serif (see Part 4), applied to both domains via one CSS token so siblings converge.
- Currency, date order, phone and WhatsApp formats, local company registration lines, imagery.
- CTA colour follows the companion spec (deep green #2F6B4F), not gold.

## Part 4: pairing evaluation

Scores 1-5: competitor distance / customer legibility / premium on white / brand continuity / performance (weights, bytes).

| Pair | Dist | Legib | Premium | Cont | Perf | Total | Note |
|---|---|---|---|---|---|---|---|
| A DM Serif Display + Plus Jakarta Sans | 3 | 3 | 4 | 5 | 4 | 19 | Serif reads like Atlys's Denton; one weight, hairlines fail at h3 on 375px; Breakout uses PJS |
| B Lora + Source Sans 3 | 3 | 5 | 2 | 2 | 4 | 16 | WordPress-default feel |
| C Libre Baskerville + Nunito Sans | 1 | 4 | 3 | 2 | 3 | 13 | Libre Baskerville is TLScontact's face (verified) |
| D Fraunces + Plus Jakarta Sans | 4 | 4 | 5 | 4 | 3 | 20 | No competitor uses Fraunces; optical-size axis keeps h3 sturdy; keeps body font |
| E EB Garamond + Work Sans | 4 | 2 | 4 | 2 | 3 | 15 | Small x-height, too light on phones |
| F Instrument Serif + DM Sans | 3 | 3 | 4 | 3 | 5 | 18 | Condensed single weight, close to Denton; DM Sans is an iVisa fallback |
| G Cormorant Garamond + Inter | 2 | 1 | 4 | 2 | 3 | 12 | Cormorant too thin at any size; Inter is the most crowded font in the set |
| H Playfair Display + Manrope | 1 | 3 | 3 | 2 | 3 | 12 | Playfair = TLScontact, Manrope = iVisa (both verified) |
| I Newsreader + Figtree | 4 | 4 | 4 | 2 | 3 | 17 | Strong, but drops both current faces |

Recommendation: D, Fraunces + Plus Jakarta Sans, for all four markets. Runner-up: A, if the owner refuses any headline change; then raise h3 to 24px minimum to protect the hairlines. Apply D to .co.uk in the same release by swapping the single `--bp-font-display` token, and replace the stray Inter loads with Plus Jakarta Sans.

Google Fonts URL (one request, two variable files, Latin + Latin Extended only):

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500..700&family=Plus+Jakarta+Sans:wght@400..700&display=swap" rel="stylesheet">
```

Weights: Fraunces 500-700 roman only (no italic file; emphasis is weight, per requirement 7). Plus Jakarta Sans 400, 500, 600, 700 from one variable file. Budget: total webfont transfer under 150 KB; measure in Lighthouse before launch (a budget, not a measured figure). Confirm `latin-ext` coverage and `tnum` support for Plus Jakarta Sans on the Google Fonts specimen before sign-off; prices must never be set in the serif, so if `tnum` is missing, use Source Sans 3 for numerals only.

```css
:root {
  --bp-font-display: "Fraunces", Georgia, "Times New Roman", serif;
  --bp-font-body: "Plus Jakarta Sans", "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}
h1, h2, h3 { font-family: var(--bp-font-display); font-optical-sizing: auto; font-weight: 600; letter-spacing: -0.01em; text-wrap: balance; }
body { font-family: var(--bp-font-body); font-weight: 400; }
.price, .date, td.num { font-family: var(--bp-font-body); font-variant-numeric: lining-nums tabular-nums; font-weight: 700; }
.prose { max-width: 36em; }
```

Type scale (size/line-height, px):

| Role | 375px | 1280px |
|---|---|---|
| h1 | Fraunces 600, 34/1.10 | 56/1.05 |
| h2 | Fraunces 600, 26/1.15 | 38/1.10 |
| h3 | Fraunces 600, 20/1.25 | 26/1.20 |
| body | PJS 400, 17/1.55 | 18/1.60 |
| small | PJS 400, 14/1.45 | 15/1.50 |
| price numerals | PJS 700 tabular, 30/1.0 | 40/1.0 |

Use `clamp()` between the two columns; never let small drop below 14px. Fraunces with `font-optical-sizing: auto` picks the sturdier low-opsz design at h3, which is exactly where DM Serif Display breaks.
