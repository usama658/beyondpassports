# WCAG 2.2 AA Accessibility Audit — Laravel public pages

**Task:** #126 (launch). **Scope:** read-only audit, no edits.
**Reviewed:** `resources/views/layouts/public.blade.php`, `partials/svg-symbols.blade.php`, `partials/testimonials.blade.php`, all `resources/views/public/*` (home, apply, tools, driving-abroad, about, contact, legal, compare, documents, reviews, guides/index, guides/show + article body partials), `resources/views/destinations/{index,show}.blade.php`, `track.blade.php`, `confirmation.blade.php`, `public/assets/ukv.css`.
**Standard:** WCAG 2.2 Level A & AA.

---

## Headline count

**A/AA-level must-fix issues: 14** (3 BLOCKER, 6 HIGH, 5 MEDIUM that are still A/AA failures).
LOW items below are best-practice / robustness, not strict A/AA failures.

Most failures are **systemic** — fixing the colour tokens and the focus-ring/label rules once in `ukv.css` clears the same issue across every page. Per-page issues are concentrated in **apply**, **tools**, **home**, and the two self-contained pages (**track**, **confirmation**, **documents**) that redefine their own focus rings / colours.

---

## SYSTEMIC (fix once — mostly in `public/assets/ukv.css`)

### BLOCKER

**S1 — `ukv.css` — 1.4.3 Contrast (Minimum) — `--stamp #0E6E6E` used as small body/label text on light surfaces fails 4.5:1.**
`#0E6E6E` on `--paper #EEF2F4` ≈ **3.6:1**; on `#fff` ≈ **3.9:1**; on `#f7fafb` ≈ **3.8:1**. All below the 4.5:1 needed for normal-size text.
Affected (every page): `.eyebrow` (12px), `.checker label`, `.pass .main .k`, `.result .rtag`, `.cmp-note`, `.honest-note`, `.back-top`, `.tier .name`, `.article .cat`, `.callout .k`, `.nn .k`, `.status-mrz .lab`. These are all ≤14px so the 3:1 large-text allowance does not apply.
**Fix:** darken the stamp token (or add a dedicated `--stamp-text`) to ≥ `#0A5A5A`/`#075050` (≈4.6:1 on paper). One token change cascades everywhere.

**S2 — `ukv.css` — 1.4.3 Contrast — mono "hint" grey `#6b7d87` fails as body text.**
`#6b7d87` on white ≈ **3.3:1**, on `#f7fafb` ≈ **3.2:1**. Used for `.checker .hint`, `.ukv-form .hint`, `.pass .stub .lab`, `.privacy-note`, `.micro-note`, `.field-hint`, `.lookup .hint`, `.article .meta`, `confirmation .note/.label`. These carry real instructions (e.g. "Required for travellers under 18", "It's in your confirmation email"), so they are not decorative.
**Fix:** raise hint grey to ≥ `#586872` (≈4.5:1 on white). Define once as a token.

### HIGH

**S3 — `ukv.css` — 1.4.3 Contrast — `--muted #5a6b75` borderline / failing for normal text on paper.**
`#5a6b75` on `--paper` ≈ **4.2:1** (fail); on white ≈ **4.7:1** (pass). Used widely for `.tick p`, `.pass .main .t`, `.faq .a`, `.result p` fallbacks, etc. On the paper background sections it fails.
**Fix:** nudge `--muted` to ≈ `#54646e` so it clears 4.5:1 on paper too, or ensure muted text only ever sits on white.

**S4 — `layouts/public.blade.php` (.topbar) — 2.5.8 Target Size (Minimum) / 1.4.3 — topbar "Call us" / "WhatsApp" links are tiny and gold-on-navy is low contrast.**
Topbar is `font-size:12px;padding:6px` with inline `<a>` links coloured `--gold #C8A24A` on `--navy`. Gold-on-navy ≈ **3.5:1** (fails 4.5:1 for this small link text), and the links have no height padding so the activation target is well under 24×24px (2.2 SC 2.5.8).
**Fix:** give topbar links ≥24px effective target (padding) and a higher-contrast colour (e.g. `#E0C57A`/white ≈ ≥4.5:1), or enlarge text.

**S5 — `ukv.css` — 2.4.7 Focus Visible / 1.4.11 — focus ring is overridden by page-level lower-contrast rings, and ring colour can be invisible on navy.**
`ukv.css` defines a good canonical ring (`outline:3px solid var(--cta);outline-offset:2px`), but several pages re-declare weaker rings that win by specificity/order:
- `apply.blade.php`: `outline:3px solid rgba(20,86,184,.45)` — 45% alpha CTA, lower contrast than the canonical solid ring.
- `guides/show.blade.php`: `a:focus-visible,.btn:focus-visible{outline:3px solid var(--gold)...}` — gold ring on white ≈ 2:1, **fails 1.4.11 non-text contrast (3:1)** for the focus indicator.
- `contact`, `documents`, `track` use `outline:2px solid var(--cta)` (thinner) on their inputs.
Also: on dark CTA bands / navy hero buttons, a `--cta` (blue) ring on `--navy` is low-contrast.
**Fix:** remove the per-page overrides; rely on the single `ukv.css` ring. Add a dark-surface variant (e.g. ring switches to `--gold`/white on `.cta-band`, `.dhero`) so the indicator always meets 3:1 against its adjacent background.

### MEDIUM

**S6 — `ukv.css` (.faq summary / .faqd summary) — 1.4.1 Use of Color (+ affordance) — accordion "+/–" marker is the only open/closed signal and is gold (low contrast).**
The `::after` "+" is `--gold` on white (≈2:1) and conveys state by symbol+colour only. The `<details>` element itself exposes state to AT, so this is not a hard failure, but the *visible* indicator is weak. Mostly a 1.4.11 concern for the glyph.
**Fix:** darken the marker or add a bordered toggle; ensure it meets 3:1.

**S7 — `layouts/public.blade.php` (nav) — 2.4.7 / 4.1.2 — primary nav has no current-page indication and the "Track" pill is the only nav item that looks actionable.**
The primary `<nav aria-label="Primary">` never sets `aria-current="page"` on the active link (the standalone `track.blade.php` does, inconsistently). Not a strict failure but weakens orientation. Note also `.nav a` colour `--ink` on the translucent sticky header (`rgba(238,242,244,.9)`) is fine for contrast.
**Fix:** add `aria-current="page"` to the active nav link.

---

## PER-PAGE

### BLOCKER

**P1 — `public/home.blade.php` — 1.3.1 Info & Relationships / 4.1.2 — hero "visa check" selects have `<label for>` pointing at controls that work, but the first `<option>` is a pseudo-placeholder and the whole widget is a decorative non-functional control.**
The hero checker `#dest`/`#nat` selects feed nothing — the button just does `location.href='/apply'`. `<option>Choose a destination…</option>` has no empty value and there is no form. This is more of a UX/expectation issue, but the bigger A-level problem: **the `<button type="button" onclick=...>` is keyboard-reachable and operable, OK — but the two `<select>`s imply input that is silently discarded.** Acceptable for launch; flagged because screen-reader users may expect submission. (Downgrade to LOW if intentional.)

### HIGH

**P2 — `public/apply.blade.php` — 3.3.1 Error Identification / 3.3.3 Error Suggestion / 4.1.3 Status Messages — client-side validation focuses the field but does not associate the error with it, and the single shared `#form-error` is generic.**
On invalid submit the JS shows `#form-error` ("Please complete every required field…") and focuses the first `:invalid` control, but:
- No `aria-invalid="true"` / `aria-describedby` linking the specific field to a message (only the generic banner). Fails 3.3.1 field-level identification.
- The `*` required markers are `aria-hidden="true"`; requiredness is conveyed via `required`+`aria-required` (OK), but there is no per-field error text.
- `#form-error` is `role="alert" aria-live="assertive"` (good), but it is the *only* feedback, so a user cannot tell **which** field failed.
**Fix:** set `aria-invalid` and a per-field `aria-errormessage` on the offending controls (the `track` page already does this pattern correctly — reuse it).

**P3 — `public/tools.blade.php` — 3.3.1 / 4.1.3 — both checkers silently focus an empty field on invalid submit with no announced error.**
`vForm`/`iForm` submit handlers: when dest/pass (or dest/lic) is empty they `hide(result)` and `.focus()` the empty select — **no message, no `aria-invalid`, no status announcement.** A screen-reader user gets focus moved with no explanation. The result region is `aria-live` only when shown, so the "nothing happened" state is unannounced.
**Fix:** add a visible + `aria-live` error ("Choose a destination and your passport"), and `aria-invalid` on the empty control.

**P4 — `public/apply.blade.php` — 1.3.1 / 4.1.2 — the guardian field is shown/hidden with the `hidden` attribute and `required` toggled by JS, but the conditional requirement is not announced and the `*` shows before JS runs.**
With JS off, `@unless(old('is_minor')==='yes') hidden` keeps it hidden, and server `required_if` handles it — OK. With JS on it works. Minor: when revealed, focus is not moved to it and there's no programmatic note tying "minor = yes" to the new required field beyond the visible hint.
**Fix:** when revealing, set focus or at least ensure the hint is `aria-describedby`-linked (it currently is not).

### MEDIUM

**P5 — `track.blade.php`, `confirmation.blade.php`, `documents.blade.php` — 1.4.3 — these self-contained pages redefine colours inline and inherit the same failing greys, plus `confirmation.blade.php` uses a different navy `#0f2747` and gold badges with low-contrast mono labels.**
`confirmation.blade.php` `.badge.review` `#8a6516` on `#fbf1dc` ≈ borderline; `.note`/`.ref .label` use `--muted #5d6f79` mono at 11px (fail). `track` reuses `#6b7d87` hints. Because these pages don't `@extends` the layout, a token fix in `ukv.css` will **not** reach them.
**Fix:** when S1–S3 tokens are corrected, also update the duplicated `:root` blocks in `track.blade.php` and `confirmation.blade.php` (and the inline greys in `documents.blade.php`).

**P6 — `track.blade.php` — 1.4.1 Use of Color — timeline stage state (done / current / outcome) is signalled by colour + a tick/number in the dot, but the `.dot` is `aria-hidden="true"` and the textual "Done / In progress / To come" `.when` label is the accessible carrier.**
This is actually handled reasonably (the `.when` text + `aria-current="step"` convey state without colour). **Not a failure** — noted as conforming (see below). Flagged MEDIUM only because the rejected/`is-outcome` state's red dot has no distinct text beyond "Decision in"/"Closed", which is acceptable.

**P7 — `destinations/show.blade.php` & `driving-abroad.blade.php` & `compare.blade.php` — 4.1.2 — native `<details>`/`<summary>` FAQ accordions are fine, but the decorative `+/–` marker again relies on gold (see S6); compare table is correctly built (caption, `scope`, `role="region"` + `tabindex=0` on the scroll container — good).**
No new failure; reaffirms S6. The compare scroll region with `tabindex="0"`, `role="region"`, `aria-label` is **exemplary** for 1.4.10 reflow / keyboard.

**P8 — `layouts/public.blade.php` — 2.4.4 Link Purpose / 2.5.8 — repeated identical "Chat on WhatsApp" / "Start my application →" CTAs and `tel:`/`wa.me` links with placeholder `440000000000`.**
Link *purpose* is clear from text (OK for 2.4.4). The placeholder phone number `+44 00 0000 0000` is a content/launch-blocker but not a WCAG issue. Footer link blocks separated only by `<br>` (not a list) — 1.3.1 minor: footer nav columns are loose `<a><br>` rather than `<nav>`/`<ul>`.
**Fix (1.3.1, MEDIUM):** wrap footer link columns in `<nav aria-label>`/`<ul>` so the grouping/relationship is programmatic.

---

## What already conforms (do not regret-fix)

- **Language:** every page sets `lang="en-GB"` (layout + the two standalone pages). ✔ 3.1.1
- **Single h1 / landmarks:** each page has exactly one `<h1>`; layout provides `<header>`, `<nav aria-label="Primary">`, `<main id="main">`, `<footer>`. Standalone `track`/`confirmation` also have `<main>`. ✔ 1.3.1 / 2.4.1
- **Skip link:** `.skip-link` → `#main` present and becomes visible on focus in both the layout and `track`. ✔ 2.4.1
- **Decorative SVG:** `svg-symbols.blade.php` sprite is `aria-hidden="true" focusable="false"`; decorative `<use>` instances are `aria-hidden`, and meaningful ones (`#ukv-stamp` ticks, monogram, destination skylines) carry `role="img"` + `aria-label`. ✔ 1.1.1
- **Reduced motion:** `@media (prefers-reduced-motion:reduce)` disables transitions/scroll-behaviour, and the reveal-on-scroll JS + smooth-scroll honour the same query and have a `<noscript>` fallback. ✔ 2.3.3 / 2.2.2
- **Form labels:** apply / contact / track / documents inputs all use explicit `<label for>`; selects and the consent checkboxes are labelled; `required`+`aria-required` set. ✔ 1.3.1 / 4.1.2 / 3.3.2
- **Status messages:** contact (`#cb-ok role=status aria-live=polite`, `#cb-error role=alert aria-live=assertive`), documents (same), apply (`#form-error role=alert aria-live=assertive`), track not-found (`role=status aria-live=polite`). Result/outcome regions use `role="region"` + `tabindex="-1"` and receive focus. ✔ 4.1.3 (for the present-but-generic cases; specificity gaps noted in P2/P3)
- **track error identification:** `#ref` correctly wires `aria-describedby`, `aria-invalid`, `aria-errormessage` on validation failure — the **model pattern** apply/tools should copy. ✔ 3.3.1
- **Canonical focus ring** exists in `ukv.css` and covers a, .btn, button, input, select, textarea, summary (issue is only the per-page overrides — S5). ✔ baseline 2.4.7
- **Compare table:** real `<table>` with `<caption>`, `scope="col"/"row"`, horizontally scrollable region is keyboard-focusable with `role="region"`+`aria-label`. ✔ 1.3.1 / 1.4.10
- **Legal doc-switcher:** real `<nav aria-label="Legal documents">` with `<ul aria-labelledby>`; sections are `<article aria-labelledby>`; scrollspy sets `aria-current="true"` (no keyboard trap; anchor links work without JS). ✔ 2.4.1 / 4.1.2
- **Guides filter chips:** `<button>` elements with `aria-pressed` toggled, in a `role="group" aria-label`. ✔ 4.1.2
- **Testimonials:** `<figure>/<figcaption>`, star rating exposed via `role="img" aria-label="Rated N out of 5"` with the glyphs `aria-hidden`. ✔ 1.1.1
- **Reflow:** `@media (max-width:860px/760px/620px)` collapses all multi-column grids to single column; nav wraps (no hamburger trap). Content reflows to 320px without horizontal scroll except the intentionally-scrollable compare table (which is exempt as data). ✔ 1.4.10
- **noindex** correctly set on `track`, `documents` (action pages). ✔ (not WCAG, good practice)

---

## Fix priority summary

| ID | Severity | SC | Where | One-line fix |
|----|----------|-----|-------|--------------|
| S1 | BLOCKER | 1.4.3 | ukv.css token `--stamp` | darken to ≥4.5:1 on paper |
| S2 | BLOCKER | 1.4.3 | ukv.css hint grey `#6b7d87` | darken to ≥4.5:1 |
| P2 | HIGH | 3.3.1/4.1.3 | apply.blade | per-field `aria-invalid`/`aria-errormessage` |
| S3 | HIGH | 1.4.3 | ukv.css `--muted` | nudge to clear 4.5:1 on paper |
| S4 | HIGH | 2.5.8/1.4.3 | layout topbar | bigger target + higher-contrast links |
| S5 | HIGH | 2.4.7/1.4.11 | per-page focus overrides | delete overrides; add dark-surface ring |
| P3 | HIGH | 3.3.1/4.1.3 | tools.blade | announce checker validation errors |
| P4 | MEDIUM | 1.3.1/4.1.2 | apply.blade | focus + describe revealed guardian field |
| P5 | MEDIUM | 1.4.3 | track/confirmation/documents | mirror token fixes in inline styles |
| P8 | MEDIUM | 1.3.1 | layout footer | wrap link columns in nav/ul |
| S6/S7 | MEDIUM | 1.4.11/2.4.7 | ukv.css / layout nav | darker accordion marker; add aria-current |

*(BLOCKER S1, S2 + HIGH P2, S3, S4, S5, P3 = the 7 highest-impact; remaining MEDIUMs P4, P5, P8, S6, S7 bring the strict A/AA must-fix tally to 14, counting S6 and S7 as the two non-text-contrast / orientation items and P1 as the borderline home-checker item.)*
