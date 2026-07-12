# CMS Phase 2 — Services Pilot Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: superpowers:subagent-driven-development or superpowers:executing-plans. Steps use checkbox (`- [ ]`).

**Goal:** Prove the block-CMS pattern end to end on a real page (`/services`) with a byte-identical result, then keep it reversible: editable prose blocks (hero, CTA) + a locked include-block for the config-driven catalogue body, rendered through shared partials so coded and CMS output match pixel-for-pixel.

**Architecture:** Extract the two Services sub-sections that are structural into shared partials — `partials.services-hero` (thin, takes eyebrow/title/lede) and `partials.services-catalogue` (the existing config-driven `@foreach` silo body, unchanged). The **coded** `services.blade.php` includes these partials (no visual change). New CMS blocks — `hero`, `cta-band`, and a generic `locked-include` — render the same partials. A `cms` Services page stacks: hero → locked-include(services-catalogue) → cta. Golden-master screenshot diff (coded vs cms `/services`) must be identical before the route inverts.

**Tech Stack:** Laravel 11, Filament v3 native Builder, Blade + ukv.css, Playwright (existing automation/*.cjs) for the screenshot gate. No new deps.

## Global Constraints

- No new front-end/composer deps. No em-dashes in user-facing copy. `declare(strict_types=1);` on new PHP.
- Public output byte-identical: coded `/services` before and after extraction, and cms `/services` vs coded `/services`, must match.
- Reversibility preserved: `UKV_CMS_ENABLED` flag + per-page `mode` both flip back to coded with no data loss.
- Blocks render EXISTING ukv.css classes only. Locked-include renders an existing partial by name — no editable internals.

---

## File Structure

- `resources/views/partials/services-hero.blade.php` — extracted hero (shared).
- `resources/views/partials/services-catalogue.blade.php` — extracted catalogue body (shared, config-driven, unchanged markup).
- `resources/views/public/services.blade.php` — modified to `@include` the two partials (coded path unchanged visually).
- `app/Cms/Blocks/HeroBlock.php` + `resources/views/cms/blocks/hero.blade.php`
- `app/Cms/Blocks/CtaBandBlock.php` + `resources/views/cms/blocks/cta-band.blade.php`
- `app/Cms/Blocks/LockedIncludeBlock.php` + `resources/views/cms/blocks/locked-include.blade.php` — renders a whitelisted partial by key.
- `app/Cms/BlockRegistry.php` — register the three new blocks.
- `automation/cms-golden.cjs` — screenshot-diff harness (coded vs cms), reusable for all phases.
- `database/seeders/CmsServicesPilotSeeder.php` — builds the cms Services page from current content.
- `tests/Feature/Cms/ServicesPilotTest.php`

---

### Task 1: Golden-master screenshot-diff harness (the reusable gate)

**Files:** Create `automation/cms-golden.cjs`

**Interfaces:** Produces a CLI: `node cms-golden.cjs <urlA> <urlB> <outDir>` — screenshots both full pages at 1280px, writes `a.png`, `b.png`, `diff.png`, prints `MISMATCH pixels: N` (0 = identical). Uses playwright-core + pixelmatch if available, else falls back to byte-compare of screenshots.

- [ ] **Step 1: Write the harness**

```js
// automation/cms-golden.cjs — full-page screenshot diff of two URLs (coded vs cms).
const { chromium } = require('playwright-core');
const fs = require('fs');
let pixelmatch, PNG;
try { pixelmatch = require('pixelmatch'); PNG = require('pngjs').PNG; } catch (e) {}
const [urlA, urlB, outDir] = process.argv.slice(2);
(async () => {
  const b = await chromium.launch({ executablePath: chromium.executablePath() });
  const shoot = async (url, file) => {
    const p = await b.newPage({ viewport: { width: 1280, height: 900 }, deviceScaleFactor: 1 });
    await p.goto(url, { waitUntil: 'domcontentloaded', timeout: 40000 });
    await p.waitForTimeout(1200);
    await p.screenshot({ path: outDir + '/' + file, fullPage: true });
    await p.close();
  };
  await shoot(urlA, 'a.png');
  await shoot(urlB, 'b.png');
  if (pixelmatch) {
    const a = PNG.sync.read(fs.readFileSync(outDir + '/a.png'));
    const c = PNG.sync.read(fs.readFileSync(outDir + '/b.png'));
    const { width, height } = a;
    const diff = new PNG({ width, height });
    const n = pixelmatch(a.data, c.data, diff.data, width, height, { threshold: 0.1 });
    fs.writeFileSync(outDir + '/diff.png', PNG.sync.write(diff));
    console.log('MISMATCH pixels:', n);
  } else {
    const a = fs.readFileSync(outDir + '/a.png'), c = fs.readFileSync(outDir + '/b.png');
    console.log('MISMATCH pixels:', a.equals(c) ? 0 : 'BYTE-DIFF (install pixelmatch+pngjs for pixel count)');
  }
  await b.close();
})().catch(e => { console.error('ERR:' + e.message); process.exit(1); });
```

- [ ] **Step 2: Smoke the harness against a stable page vs itself**

Run (dev server on a port): `node automation/cms-golden.cjs http://127.0.0.1:PORT/about http://127.0.0.1:PORT/about ./scratch`
Expected: `MISMATCH pixels: 0`.

- [ ] **Step 3: Commit**

```bash
git add automation/cms-golden.cjs
git commit -m "test(cms): golden-master screenshot-diff harness"
```

---

### Task 2: Extract Services hero + catalogue into shared partials (coded path unchanged)

**Files:** Create `partials/services-hero.blade.php`, `partials/services-catalogue.blade.php`; Modify `public/services.blade.php`.

- [ ] **Step 1: Baseline screenshot the current coded /services** (before any change) — keep as `services-baseline.png`.
- [ ] **Step 2: Move the `<section class="sv-hero">...</section>` block verbatim into `partials/services-hero.blade.php`**, parameterised only for `$eyebrow`, `$title`, `$lede` with the current values as defaults so the coded include is unchanged.
- [ ] **Step 3: Move the catalogue `<div class="wrap"><div class="sv-layout">...</div></div>` (the config `@foreach` body) verbatim into `partials/services-catalogue.blade.php`** — no markup edits.
- [ ] **Step 4: Replace those blocks in `services.blade.php` with `@include('partials.services-hero', [...])` and `@include('partials.services-catalogue')`.**
- [ ] **Step 5: Golden-master coded /services vs the pre-extraction baseline** — must be `MISMATCH pixels: 0`.
- [ ] **Step 6: Commit** `refactor(services): extract hero + catalogue to shared partials (no visual change)`.

---

### Task 3: Hero, CtaBand, LockedInclude blocks + register

**Files:** the three block classes + partials + BlockRegistry.

- [ ] Hero block: fields `eyebrow`, `title`, `lede`; view `cms.blocks.hero` includes `partials.services-hero` with the field data.
- [ ] CtaBand block: fields `heading`, `button_label`, `whatsapp_message`; view renders the existing `.cta-band` markup.
- [ ] LockedInclude block: a `Select` of whitelisted partial keys (initially `services-catalogue`); view `@includeIf` the mapped partial. No editable internals.
- [ ] Register all three in `BlockRegistry::$types`.
- [ ] Test: `BlockRegistry::all()` has `hero`, `cta-band`, `locked-include`; each `view()` resolves.
- [ ] Commit `feat(cms): hero, cta-band, locked-include blocks`.

---

### Task 4: Seed the cms Services page + golden-master gate

**Files:** `CmsServicesPilotSeeder`, `ServicesPilotTest`.

- [ ] Seeder creates a `Page` slug `services`, `mode=cms`, `status=draft`, blocks = [hero(current copy), locked-include(services-catalogue), cta-band(current copy)].
- [ ] Test: with flag on + page published, a temporary `/__cms/services` preview route (or direct render) returns 200 and contains the hero title + a catalogue service label.
- [ ] Manual gate: run `automation/cms-golden.cjs` on coded `/services` vs the cms render — require `MISMATCH pixels: 0` (or within an explicit, logged tolerance for antialiasing).
- [ ] Lighthouse/PSI on both — no CWV regression.
- [ ] Commit `feat(cms): services pilot page + golden-master gate`.

---

### Task 5: Invert the Services route (cms when published, coded fallback)

**Files:** Modify `routes/web.php`.

- [ ] Change `Route::view('/services', 'public.services')` to a closure/controller that renders the cms Services page when `UKV_CMS_ENABLED` + published cms page exists, else the coded `public.services` view. (Reuses CmsController logic; the catch-all already handles brand-new slugs, this handles the existing named route.)
- [ ] Test: flag off => coded services; flag on + published => cms services; both assertSee the hero title + a catalogue label.
- [ ] Golden-master coded-forced vs cms one more time = 0.
- [ ] Full suite green (minus the 4 known pre-existing failures).
- [ ] Commit `feat(cms): serve Services from CMS when published, coded fallback`.

---

## Self-Review

- Editable prose blocks (hero, cta) → Tasks 3-4 ✅
- Config-driven body preserved exactly via shared partial + locked-include → Tasks 2-3 ✅
- Golden-master pixel gate → Task 1 (harness) + Tasks 2,4,5 (gates) ✅
- Lighthouse/CWV gate → Task 4 ✅
- Reversibility (flag + per-page mode, coded fallback) → Task 5 ✅
- No placeholders; block field names (eyebrow/title/lede/heading/button_label/whatsapp_message) consistent across tasks.

## Notes for Phase 3

Reuse the same blocks + `locked-include` whitelist for about (and later home): extract each config/JS-driven section into a shared partial, wrap as locked-include, keep prose as editable blocks, gate every page with cms-golden.cjs.
