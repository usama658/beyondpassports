# Sitewide Premium Institutional Re-skin — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Re-skin the public Beyond Passports site to the Institutional system — Petrol/Teal cool palette (terracotta retired), self-hosted Outfit type, travel-document signature — by retokening the shared CSS and sweeping hardcoded colours, with the 144-test suite staying green.

**Architecture:** All design lives in `public/assets/ukv.css` (`:root` tokens + components). Blade pages inherit via `var(--*)`; the only per-page work is sweeping hardcoded hex values and updating the three standalone pages that carry their own `:root`. Fonts move from the Google CDN to self-hosted `@font-face`. Verification is a scripted hex sweep that must grep to zero, the existing Pest suite, and headless screenshots.

**Tech Stack:** Laravel 12 + Blade, plain CSS (no build step — `ukv.css` is served directly), Pest (PHPUnit), headless Chrome for screenshots. PHP via `C:\xampp\php\php.exe`. Dev server already running at `http://127.0.0.1:8000`.

**Spec:** `docs/superpowers/specs/2026-06-19-sitewide-premium-institutional-reskin-design.md`

---

## File map

- `public/assets/ukv.css` — token values + `@font-face` + mesh gradient stops + signature component classes. **Primary change.**
- `public/fonts/outfit-400.woff2`, `public/fonts/outfit-700.woff2` — already present; add `outfit-600` + `outfit-800`.
- `resources/views/layouts/public.blade.php` — remove Google Fonts `<link>`.
- `resources/views/track.blade.php`, `resources/views/public/checklist-result.blade.php` — own `:root` token blocks + Google Fonts link.
- `resources/views/errors/404.blade.php`, `resources/views/confirmation.blade.php` — Google Fonts link.
- `resources/views/**/*.blade.php` — hardcoded-hex sweep.
- `resources/views/checklist-pdf.blade.php`, `resources/views/public/documents.blade.php` — check/sweep.
- Tests: existing `tests/` suite (no new unit tests; add one guard test for the retired CDN link).

## Colour replacement map (canonical — used by the sweep)

Current theme = terracotta CTA + viridian accent (viridian applied in commit `de44dab`). Replace **both**:

| Old | New | Meaning |
|-----|-----|---------|
| `#C75D38` | `#155E7A` | CTA / primary (`--cta`,`--gold`) |
| `#b04e2c` | `#0F4A61` | CTA hover |
| `#22282b` | `#16222E` | ink / navy |
| `#2F8F86` | `#2E9A8C` | teal accent (`--stamp`) |
| `#226B64` | `#1F6E63` | accent text (`--stamp-text`) |
| `#84D2C9` | `#79CFC2` | accent-on-dark |
| `#F2C2AC` | `#A9CCDA` | soft (eyebrow-on-ink) |
| `#F4F5F6` | `#F4F6FA` | paper |
| `#e6e8ea` | `#dde3ec` | edge |
| `#E3F1EF` | `#E2F1EE` | accent tint |
| `#BEE2DD` | `#cfe6e0` | accent soft border |
| `rgba(199,93,56,X)` | `rgba(21,94,122,X)` | terracotta mesh/glow → petrol |
| `rgba(47,143,134,X)` | `rgba(46,154,140,X)` | viridian mesh/glow → teal |
| `rgba(132,210,201,X)` | `rgba(121,207,194,X)` | accent-light glow |

Leave untouched: `#25D366` (WhatsApp brand), `rgba(40,50,70,*)` (neutral shadow), `#fff`/`#ffffff`, `#8a2a22`/`#fdeceb`/`#f3c6c2` (error reds), `#697079` (`--muted`/`--hint` neutral grey).

---

### Task 1: Self-host the remaining Outfit weights

**Files:**
- Create: `public/fonts/outfit-600.woff2`, `public/fonts/outfit-800.woff2`

- [ ] **Step 1: Download the two missing weights**

Run (bash):
```bash
cd "/c/Users/mumya/OneDrive/Desktop/Claude Projects/UK VIsa/ukv-app"
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36"
for w in 600 800; do
  url=$(curl -s -A "$UA" "https://fonts.googleapis.com/css2?family=Outfit:wght@$w&display=swap" | grep -oE 'https://[^)]+\.woff2' | tail -1)
  curl -s -A "$UA" "$url" -o "public/fonts/outfit-$w.woff2"
done
ls -la public/fonts/
```
Expected: `outfit-400/600/700/800.woff2` all present, each >10 KB.

- [ ] **Step 2: Commit**

```bash
git add public/fonts/outfit-600.woff2 public/fonts/outfit-800.woff2
git commit -m "chore: self-host Outfit 600/800 weights"
```

---

### Task 2: Retoken `ukv.css` `:root` + add `@font-face` + font tokens

**Files:**
- Modify: `public/assets/ukv.css:7-22` (the `:root` block) + insert `@font-face` above it.

- [ ] **Step 1: Replace the `:root` block**

Replace lines 7–22 (`:root{ ... }`) with:

```css
/* Self-hosted Outfit — no external CDN (renders behind privacy/ad blockers; removes a
   Google Fonts IP transfer). All weights live in public/fonts. */
@font-face{font-family:'Outfit';src:url('/fonts/outfit-400.woff2') format('woff2');font-weight:400;font-display:swap}
@font-face{font-family:'Outfit';src:url('/fonts/outfit-600.woff2') format('woff2');font-weight:600;font-display:swap}
@font-face{font-family:'Outfit';src:url('/fonts/outfit-700.woff2') format('woff2');font-weight:700;font-display:swap}
@font-face{font-family:'Outfit';src:url('/fonts/outfit-800.woff2') format('woff2');font-weight:800;font-display:swap}
:root{
  /* Institutional palette — Petrol & Steel. --cta + --gold both map to petrol. */
  --ink:#16222E; --navy:#16222E; --paper:#F4F6FA; --gold:#155E7A; --stamp:#2E9A8C; --cta:#155E7A;
  /* soft steel accent — on dark surfaces (badges, eyebrows, labels on ink). */
  --soft:#A9CCDA;
  /* teal accent text-on-light — passes 4.5:1 on paper/white. (WCAG 1.4.3) */
  --stamp-text:#1F6E63;
  /* teal-on-dark (live dots, eyebrows on ink). */
  --on-dark:#79CFC2;
  /* instructional-hint grey — passes 4.5:1 on white and grey panels. (WCAG 1.4.3) */
  --hint:#5d6b76;
  --paper-edge:#dde3ec; --white:#fff;
  /* body/label muted text — clears 4.5:1 on the cool paper background. (WCAG 1.4.3) */
  --muted:#5d6b76;
  /* Outfit across the board — display, body AND mono (tabular numerals for refs/fees). */
  --display:"Outfit",system-ui,sans-serif; --body:"Outfit",system-ui,sans-serif; --mono:"Outfit",ui-monospace,monospace;
  --shadow:0 18px 44px -26px rgba(20,34,46,.34);
}
```

- [ ] **Step 2: Verify the file still parses (no syntax break)**

Run: `curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8000/assets/ukv.css`
Expected: `200`

- [ ] **Step 3: Commit**

```bash
git add public/assets/ukv.css
git commit -m "design: retoken ukv.css :root to petrol/teal + self-hosted Outfit"
```

---

### Task 3: Sweep hardcoded hex + mesh stops across `ukv.css` and all blades

**Files:**
- Modify: `public/assets/ukv.css` + `resources/views/**/*.blade.php` (only those containing the old values).

- [ ] **Step 1: Run the sweep**

Run (bash):
```bash
cd "/c/Users/mumya/OneDrive/Desktop/Claude Projects/UK VIsa/ukv-app"
files=$(grep -rliE "#c75d38|#b04e2c|#22282b|#2f8f86|#226b64|#84d2c9|#f2c2ac|#f4f5f6|#e6e8ea|#e3f1ef|#bee2dd|rgba\(199, *93, *56|rgba\(47, *143, *134|rgba\(132, *210, *201" public/assets/ukv.css resources/views)
for f in $files; do
  sed -i -E \
    -e 's/#[Cc]75[Dd]38/#155E7A/g' \
    -e 's/#b04e2c/#0F4A61/g' \
    -e 's/#22282[bB]/#16222E/g' \
    -e 's/#2[Ff]8[Ff]86/#2E9A8C/g' \
    -e 's/#226[Bb]64/#1F6E63/g' \
    -e 's/#84[Dd]2[Cc]9/#79CFC2/g' \
    -e 's/#[Ff]2[Cc]2[Aa][Cc]/#A9CCDA/g' \
    -e 's/#[Ff]4[Ff]5[Ff]6/#F4F6FA/g' \
    -e 's/#e6e8ea/#dde3ec/g' \
    -e 's/#[Ee]3[Ff]1[Ee][Ff]/#E2F1EE/g' \
    -e 's/#[Bb][Ee][Ee]2[Dd][Dd]/#cfe6e0/g' \
    -e 's/rgba\(199, *93, *56,/rgba(21,94,122,/g' \
    -e 's/rgba\(47, *143, *134,/rgba(46,154,140,/g' \
    -e 's/rgba\(132, *210, *201,/rgba(121,207,194,/g' \
    "$f"
done
echo "swept: $files"
```

- [ ] **Step 2: Verify zero leftovers**

Run:
```bash
grep -rinoE "#c75d38|#b04e2c|#22282b|#2f8f86|#226b64|#84d2c9|#f2c2ac|#f4f5f6|#e6e8ea|#e3f1ef|#bee2dd|rgba\(199, *93, *56|rgba\(47, *143, *134|rgba\(132, *210, *201" public/assets/ukv.css resources/views | wc -l
```
Expected: `0`

- [ ] **Step 3: Commit**

```bash
git add public/assets/ukv.css resources/views
git commit -m "design: sweep hardcoded terracotta/viridian -> petrol/teal sitewide"
```

---

### Task 4: Update standalone-page `:root` blocks + remove Google Fonts links

**Files:**
- Modify: `resources/views/track.blade.php`, `resources/views/public/checklist-result.blade.php` (own `:root` + font link)
- Modify: `resources/views/layouts/public.blade.php`, `resources/views/errors/404.blade.php`, `resources/views/confirmation.blade.php` (font link only)
- Check: `resources/views/checklist-pdf.blade.php`, `resources/views/public/documents.blade.php`

- [ ] **Step 1: Confirm the standalone `:root` hexes already swept**

Task 3 swept blades, so `track.blade.php`'s `:root` (e.g. `--stamp:#2F8F86`) is already petrol/teal. Verify:
Run: `grep -nE "\-\-stamp:|\-\-cta:|\-\-navy:|\-\-soft:" resources/views/track.blade.php resources/views/public/checklist-result.blade.php`
Expected: shows `#2E9A8C`, `#155E7A`, `#16222E` (and `--soft` if present `#A9CCDA`). If any old value remains, fix it with Edit.

- [ ] **Step 2: Replace each Google Fonts `<link>` with self-hosted note**

In each of the 5 files, find the line(s):
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
```
Delete all three. For the standalone pages (`track`, `checklist-result`, `404`, `confirmation`) that do NOT load `ukv.css`'s `@font-face`, add an inline `@font-face` for Outfit 400/700 at the top of their `<style>` block:
```css
@font-face{font-family:'Outfit';src:url('/fonts/outfit-400.woff2') format('woff2');font-weight:400;font-display:swap}
@font-face{font-family:'Outfit';src:url('/fonts/outfit-700.woff2') format('woff2');font-weight:700;font-display:swap}
```
(Pages that load `ukv.css` — `layouts/public.blade.php` — need no inline `@font-face`; they inherit it.) Also update any `font-family:"Plus Jakarta Sans"...` in those pages' inline `:root`/`--display`/`--body`/`--mono` to `'Outfit'`.

- [ ] **Step 3: Verify no Google Fonts references remain**

Run: `grep -rin "fonts.googleapis.com\|Plus+Jakarta\|Plus Jakarta" resources/views | wc -l`
Expected: `0`

- [ ] **Step 4: Commit**

```bash
git add resources/views
git commit -m "design: remove Google Fonts CDN, self-host Outfit on standalone pages"
```

---

### Task 5: Add the travel-document signature utilities + apply to the home hero

**Files:**
- Modify: `public/assets/ukv.css` (append signature component classes)
- Modify: `resources/views/public/home.blade.php` (apply stamp/MRZ to the hero card — exemplar)

- [ ] **Step 1: Append signature classes to `ukv.css`**

Add at the end of `public/assets/ukv.css`:
```css
/* ── Signature: travel-document system ───────────────────────────── */
/* Passport-stamp tick — verified/checked states. */
.stamp{display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:50%;
  border:2.5px solid var(--stamp);color:var(--stamp-text);transform:rotate(-12deg);
  font-family:var(--body);font-size:9px;font-weight:700;letter-spacing:.04em;text-align:center;line-height:1.1}
/* MRZ micro-label — refs, dates (machine-readable flavour). */
.mrz{font-family:var(--mono);font-size:11px;letter-spacing:.12em;color:#8a97a1;
  font-variant-numeric:tabular-nums;text-transform:uppercase}
/* Tabular numerals for prices/fees so columns don't jitter. */
.tnum{font-variant-numeric:tabular-nums}
```

- [ ] **Step 2: Verify CSS still serves**

Run: `curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8000/assets/ukv.css`
Expected: `200`

- [ ] **Step 3: Apply the stamp to the home hero card (exemplar)**

Open `resources/views/public/home.blade.php`, find the hero card/region (the destination preview or hero panel). Add a `<span class="stamp">CHECKED<br>&amp; READY</span>` inside the hero card above its heading, and add `class="mrz"` to any reference/code line if present. (Keep it to one stamp in the hero — signature lives in one place per view.) Exact insertion depends on current markup; match the surrounding indentation.

- [ ] **Step 4: Screenshot the home hero**

Run (bash):
```bash
CHROME="/c/Program Files/Google/Chrome/Application/chrome.exe"; [ -f "$CHROME" ] || CHROME="/c/Program Files (x86)/Google/Chrome/Application/chrome.exe"
UD=$(mktemp -d)
"$CHROME" --headless=new --disable-gpu --hide-scrollbars --user-data-dir="$UD" --window-size=1280,1200 --virtual-time-budget=6000 --screenshot="C:/Users/mumya/reskin-home.png" "http://127.0.0.1:8000/" 2>/dev/null
```
Then Read `C:/Users/mumya/reskin-home.png` and confirm: petrol CTA, teal accents, Outfit type, stamp visible, no terracotta.

- [ ] **Step 5: Commit**

```bash
git add public/assets/ukv.css resources/views/public/home.blade.php
git commit -m "design: travel-document signature utilities (stamp/MRZ/tnum) + home hero"
```

---

### Task 6: Guard test for the retired Google Fonts CDN

**Files:**
- Create: `tests/Feature/NoGoogleFontsTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The site self-hosts Outfit (privacy + reliability). No public page may pull from
 * the Google Fonts CDN, and the body font must be Outfit.
 */
final class NoGoogleFontsTest extends TestCase
{
    /** @return list<array{0:string}> */
    public static function publicRoutes(): array
    {
        return [['/'], ['/track'], ['/tools'], ['/about'], ['/contact'], ['/reviews']];
    }

    /** @dataProvider publicRoutes */
    public function test_page_does_not_load_google_fonts(string $path): void
    {
        $html = $this->get($path)->getContent();
        $this->assertStringNotContainsString('fonts.googleapis.com', $html);
        $this->assertStringNotContainsString('Plus Jakarta', $html);
    }
}
```

- [ ] **Step 2: Run it — should pass (Tasks 2–4 removed the CDN)**

Run: `$env:Path += ";C:\xampp\php"; php artisan test --filter=NoGoogleFontsTest`
Expected: PASS. If FAIL, an offending page still references the CDN — fix it, then re-run.

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/NoGoogleFontsTest.php
git commit -m "test: guard against Google Fonts CDN on public pages"
```

---

### Task 7: Full verification — suite + screenshots + AA

**Files:** none (verification only)

- [ ] **Step 1: Run the full Pest suite**

Run: `$env:Path += ";C:\xampp\php"; php artisan test --compact`
Expected: all green (≈145 tests incl. the new guard). If any test asserts an old hex (`#C75D38`, `#22282b`, etc.) or the font link, update that assertion to the new value — the design changed intentionally.

- [ ] **Step 2: Screenshot the key public routes**

Run (bash) for each of `/`, `/destinations`, `/tools`, `/apply`, `/guides`, `/track`, `/reviews`, `/about`, `/compare`, `/legal`:
```bash
CHROME="/c/Program Files/Google/Chrome/Application/chrome.exe"; [ -f "$CHROME" ] || CHROME="/c/Program Files (x86)/Google/Chrome/Application/chrome.exe"
UD=$(mktemp -d)
for r in "" destinations tools apply guides track reviews about compare legal; do
  "$CHROME" --headless=new --disable-gpu --hide-scrollbars --user-data-dir="$UD" --window-size=1280,1600 --virtual-time-budget=6000 --screenshot="C:/Users/mumya/rs-${r:-home}.png" "http://127.0.0.1:8000/$r" 2>/dev/null
done
```
Read each PNG. Confirm: no terracotta/sage anywhere, petrol CTAs, teal trust accents, Outfit type, AA-legible text. Note any page with leftover warm colour or contrast issue.

- [ ] **Step 3: Fix any leftovers found, then re-grep**

For any stray colour found in screenshots, locate with Grep and Edit, then re-run the Task 3 Step 2 grep (expect `0`).

- [ ] **Step 4: Final commit**

```bash
git add -A
git commit -m "design: Institutional re-skin verified — suite green + all routes screenshot-checked"
```

---

## Notes for the implementer

- **`ukv.css` is served raw** (no build/compile). Edits are live on next request; no `npm`/`vite` step.
- **Page-by-page premium polish** (hero layouts, section redesigns per the spec's rollout step 4) is **not** in this plan — it continues afterward via the interactive preview→pick→apply loop with the user. This plan delivers the foundational system (palette + type + signature utilities), which is independently shippable.
- **Stat-band figures** ("12,000+", "100% checked") are placeholders flagged in the spec — do not invent new numbers; leave existing copy as-is (separate task #299 verifies real data).
- Dev server assumed running. If not: `php artisan serve` (via xampp php on PATH).

## Self-review

- **Spec coverage:** palette tokens (Task 2,3,4) ✓; old→new map (Task 3) ✓; Outfit self-host + remove CDN (Tasks 1,2,4) ✓; signature (Task 5) ✓; compliance disclaimers — unchanged markup, retained by default ✓; testing (Tasks 6,7) ✓; per-page polish — explicitly deferred ✓.
- **Placeholders:** none — every step has exact commands/code. Home-hero stamp insertion (Task 5 Step 3) is markup-dependent and says so, with the exact element to add.
- **Type/value consistency:** replacement map values match the `:root` values in Task 2 (`#155E7A`, `#16222E`, `#2E9A8C`, `#1F6E63`, `#79CFC2`, `#A9CCDA`, `#F4F6FA`, `#dde3ec`). `--on-dark` added in Task 2 and used by signature/components.
