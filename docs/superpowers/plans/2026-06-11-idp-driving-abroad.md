# IDP + Driving Abroad (#6) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A guided self-service IDP/driving silo — an accurate checker (destination + licence type → which permit, honestly handling the EU photocard exemption), the `idp-conventions.json` dataset, and a hub-and-spoke content silo cross-selling the visa service. No issuance, no payment, no CRM.

**Architecture:** The accuracy-critical logic is a **pure checker function** (`idp-core.js`) tested under Node: it encodes the gov.uk rule that UK photocard holders need no IDP in the EU/EEA/CH/NO/IS/LI, but do for non-EEA 1926/1949/1968 countries. A dependency-free PHP validator checks `idp-conventions.json`. The silo (hub + how-to spoke + `/driving-in-<country>/` destination spoke) is built in Elementor (config-and-verify), reusing the #3 photo maker (UK preset).

**Tech Stack:** JS (pure logic + browser widget), PHP validator, WordPress/Elementor + hello-child, RankMath (FAQ/HowTo/Article schema), shared visa JSON `idp.*` (#2) for the cross-sell target.

**Prerequisite:** #1 Foundation, #2 (`idp_crosssell` links target `/driving-in-<slug>/`), #3 (photo maker). `node`/`php` on PATH. Paths relative to repo root.

---

## File structure

- `wordpress/hello-child/data/idp-conventions.json` — per-country convention + photocard rule
- `wordpress/hello-child/data/idp.schema.json` — validator contract
- `wordpress/hello-child/bin/validate-idp.php` — validator
- `wordpress/hello-child/assets/js/idp-core.js` — pure checker logic
- `wordpress/hello-child/assets/js/idp-widget.js` — browser hydrator
- `wordpress/hello-child/tests/js/run.mjs` — extend Node runner

---

### Task 1: IDP conventions data + schema + validator (test-first)

**Files:**
- Create: `wordpress/hello-child/data/idp.schema.json`
- Create: `wordpress/hello-child/data/idp-conventions.json`
- Create: `wordpress/hello-child/bin/validate-idp.php`

- [ ] **Step 1: Write the schema contract**

`wordpress/hello-child/data/idp.schema.json`:
```json
{
  "required_per_country": ["name", "convention", "idp_required_for_photocard", "idp_required_for_paper", "validity"],
  "enum_convention": ["1926", "1949", "1968", "1949+1968", "1926+1968"]
}
```

- [ ] **Step 2: Write the conventions dataset** (popular set; long tail is the spec open item)

`wordpress/hello-child/data/idp-conventions.json`:
```json
{
  "france":      { "name": "France",        "convention": "1968", "idp_required_for_photocard": false, "idp_required_for_paper": true,  "validity": "3 years or until your licence expires", "drive_notes": "Drive on the right; carry a UK sticker. Photocard holders need no IDP; hire firms may still ask.", "idp_notes": "Only for paper or Crown Dependency licences." },
  "spain":       { "name": "Spain",         "convention": "1949", "idp_required_for_photocard": false, "idp_required_for_paper": true,  "validity": "12 months", "drive_notes": "Drive on the right; UK sticker required.", "idp_notes": "Spain uses the 1949 convention; not needed for UK photocard holders." },
  "italy":       { "name": "Italy",         "convention": "1968", "idp_required_for_photocard": false, "idp_required_for_paper": true,  "validity": "3 years or until your licence expires", "drive_notes": "Drive on the right; UK sticker required.", "idp_notes": "Only for paper licences." },
  "germany":     { "name": "Germany",       "convention": "1968", "idp_required_for_photocard": false, "idp_required_for_paper": true,  "validity": "3 years or until your licence expires", "drive_notes": "Drive on the right; UK sticker required.", "idp_notes": "Only for paper licences." },
  "portugal":    { "name": "Portugal",      "convention": "1968", "idp_required_for_photocard": false, "idp_required_for_paper": true,  "validity": "3 years or until your licence expires", "drive_notes": "Drive on the right; UK sticker required.", "idp_notes": "Only for paper licences." },
  "greece":      { "name": "Greece",        "convention": "1968", "idp_required_for_photocard": false, "idp_required_for_paper": true,  "validity": "3 years or until your licence expires", "drive_notes": "Drive on the right; UK sticker required.", "idp_notes": "Only for paper licences." },
  "croatia":     { "name": "Croatia",       "convention": "1968", "idp_required_for_photocard": false, "idp_required_for_paper": true,  "validity": "3 years or until your licence expires", "drive_notes": "Drive on the right; UK sticker required.", "idp_notes": "Only for paper licences." },
  "switzerland": { "name": "Switzerland",   "convention": "1968", "idp_required_for_photocard": false, "idp_required_for_paper": true,  "validity": "3 years or until your licence expires", "drive_notes": "Drive on the right; vignette required for motorways.", "idp_notes": "Only for paper licences." },
  "norway":      { "name": "Norway",        "convention": "1968", "idp_required_for_photocard": false, "idp_required_for_paper": true,  "validity": "3 years or until your licence expires", "drive_notes": "Drive on the right.", "idp_notes": "Only for paper licences." },
  "iceland":     { "name": "Iceland",       "convention": "1949", "idp_required_for_photocard": false, "idp_required_for_paper": true,  "validity": "12 months", "drive_notes": "Drive on the right; headlights on at all times.", "idp_notes": "Only for paper licences." },
  "ireland":     { "name": "Ireland",       "convention": "1949", "idp_required_for_photocard": false, "idp_required_for_paper": false, "validity": "12 months", "drive_notes": "Drive on the left; UK licence accepted.", "idp_notes": "Not needed for UK licence holders." },
  "usa":         { "name": "USA",           "convention": "1949", "idp_required_for_photocard": true,  "idp_required_for_paper": true,  "validity": "12 months", "drive_notes": "Drive on the right; rules vary by state.", "idp_notes": "1949 permit; carry alongside your UK licence." },
  "canada":      { "name": "Canada",        "convention": "1949", "idp_required_for_photocard": true,  "idp_required_for_paper": true,  "validity": "12 months", "drive_notes": "Drive on the right.", "idp_notes": "1949 permit recommended." },
  "australia":   { "name": "Australia",     "convention": "1949", "idp_required_for_photocard": true,  "idp_required_for_paper": true,  "validity": "12 months", "drive_notes": "Drive on the left.", "idp_notes": "1949 permit; carry with your UK licence." },
  "new-zealand": { "name": "New Zealand",   "convention": "1949", "idp_required_for_photocard": true,  "idp_required_for_paper": true,  "validity": "12 months", "drive_notes": "Drive on the left.", "idp_notes": "1949 permit." },
  "japan":       { "name": "Japan",         "convention": "1949", "idp_required_for_photocard": true,  "idp_required_for_paper": true,  "validity": "12 months", "drive_notes": "Drive on the left; IDP must be obtained before arrival.", "idp_notes": "1949 permit required." },
  "india":       { "name": "India",         "convention": "1949", "idp_required_for_photocard": true,  "idp_required_for_paper": true,  "validity": "12 months", "drive_notes": "Drive on the left.", "idp_notes": "1949 permit." },
  "turkey":      { "name": "Turkey",        "convention": "1968", "idp_required_for_photocard": true,  "idp_required_for_paper": true,  "validity": "3 years or until your licence expires", "drive_notes": "Drive on the right.", "idp_notes": "1968 permit; Turkey also recognises 1949." },
  "morocco":     { "name": "Morocco",       "convention": "1968", "idp_required_for_photocard": true,  "idp_required_for_paper": true,  "validity": "3 years or until your licence expires", "drive_notes": "Drive on the right.", "idp_notes": "1968 permit recommended." },
  "uae":         { "name": "United Arab Emirates", "convention": "1968", "idp_required_for_photocard": true, "idp_required_for_paper": true, "validity": "3 years or until your licence expires", "drive_notes": "Drive on the right.", "idp_notes": "1968 permit; required for car hire." },
  "egypt":       { "name": "Egypt",         "convention": "1949", "idp_required_for_photocard": true,  "idp_required_for_paper": true,  "validity": "12 months", "drive_notes": "Drive on the right.", "idp_notes": "1949 permit." },
  "thailand":    { "name": "Thailand",      "convention": "1949+1968", "idp_required_for_photocard": true, "idp_required_for_paper": true, "validity": "12 months (1949) / 3 years (1968)", "drive_notes": "Drive on the left.", "idp_notes": "May need 1949 or 1968 depending on region; check both." },
  "south-africa":{ "name": "South Africa",  "convention": "1968", "idp_required_for_photocard": true,  "idp_required_for_paper": true,  "validity": "3 years or until your licence expires", "drive_notes": "Drive on the left.", "idp_notes": "1968 permit recommended." },
  "mexico":      { "name": "Mexico",        "convention": "1926+1968", "idp_required_for_photocard": true, "idp_required_for_paper": true, "validity": "varies", "drive_notes": "Drive on the right.", "idp_notes": "1926 or 1968 depending on area." }
}
```

- [ ] **Step 3: Write the validator**

`wordpress/hello-child/bin/validate-idp.php`:
```php
<?php
$root = dirname(__DIR__);
$schema = json_decode(file_get_contents($root . '/data/idp.schema.json'), true);
$data = json_decode(file_get_contents($root . '/data/idp-conventions.json'), true);
$errors = [];
if (!is_array($data)) { fwrite(STDERR, "invalid JSON\n"); exit(1); }
foreach ($data as $slug => $c) {
    foreach ($schema['required_per_country'] as $k)
        if (!array_key_exists($k, $c)) $errors[] = "$slug: missing '$k'";
    if (isset($c['convention']) && !in_array($c['convention'], $schema['enum_convention'], true))
        $errors[] = "$slug: bad convention '{$c['convention']}'";
    foreach (['idp_required_for_photocard', 'idp_required_for_paper'] as $b)
        if (isset($c[$b]) && !is_bool($c[$b])) $errors[] = "$slug: $b must be boolean";
}
if ($errors) { fwrite(STDERR, implode("\n", $errors) . "\n"); echo count($errors) . " ERROR(S)\n"; exit(1); }
echo "OK: " . count($data) . " countries valid\n"; exit(0);
```

- [ ] **Step 4: Run validator — expect PASS**

Run: `php wordpress/hello-child/bin/validate-idp.php; echo "exit=$?"`
Expected: `OK: 24 countries valid`, `exit=0`.

- [ ] **Step 5: Commit**

```bash
git add wordpress/hello-child/data/idp.schema.json wordpress/hello-child/data/idp-conventions.json wordpress/hello-child/bin/validate-idp.php
git commit -m "feat(idp): conventions dataset (photocard rule) + schema + validator"
```

---

### Task 2: IDP checker core (test-first — the accuracy logic)

**Files:**
- Create: `wordpress/hello-child/assets/js/idp-core.js`
- Modify: `wordpress/hello-child/tests/js/run.mjs`

- [ ] **Step 1: Add failing tests** (the photocard rule is the critical case)

Append to `wordpress/hello-child/tests/js/run.mjs`:
```js
const { checkIdp } = await import('../../assets/js/idp-core.js');
const idpData = JSON.parse(readFileSync(join(here, '../../data/idp-conventions.json'), 'utf8'));

check('France + photocard -> NOT needed', () => {
  const r = checkIdp(idpData.france, 'photocard');
  assert.equal(r.needed, false);
});
check('France + paper -> needed (1968)', () => {
  const r = checkIdp(idpData.france, 'paper');
  assert.equal(r.needed, true);
  assert.equal(r.convention, '1968');
});
check('USA + photocard -> needed (1949)', () => {
  const r = checkIdp(idpData.usa, 'photocard');
  assert.equal(r.needed, true);
  assert.equal(r.convention, '1949');
});
check('always shows in-person PayPoint how-to', () => {
  const r = checkIdp(idpData.usa, 'photocard');
  assert.match(r.howto, /PayPoint/);
  assert.match(r.howto, /in person/i);
  assert.equal(r.cost_gbp, 5.5);
});
```

- [ ] **Step 2: Run — expect FAIL**

Run: `node wordpress/hello-child/tests/js/run.mjs`
Expected: cannot import `idp-core.js`.

- [ ] **Step 3: Implement**

`wordpress/hello-child/assets/js/idp-core.js`:
```js
// Pure IDP checker. Honest about the EU photocard exemption + in-person-only PayPoint issuance.
const HOWTO = 'Apply in person at a PayPoint store with your original full driving licence and a passport-standard photo (plus your passport if you hold a paper licence). Issued on the spot for £5.50. Apply up to 3 months before travel.';

export function checkIdp(country, licenceType) {
  const isPaper = String(licenceType).toLowerCase() === 'paper';
  const needed = isPaper ? !!country.idp_required_for_paper : !!country.idp_required_for_photocard;
  return {
    needed,
    convention: needed ? country.convention : null,
    validity: needed ? country.validity : null,
    cost_gbp: 5.5,
    howto: HOWTO,
    message: needed
      ? `You need a ${country.convention} International Driving Permit for ${country.name}.`
      : `You do not need an IDP for ${country.name} with a UK photocard licence. ${country.idp_notes || ''}`.trim(),
  };
}

if (typeof window !== 'undefined') { window.UKVIdp = { checkIdp }; }
```

- [ ] **Step 4: Run — expect PASS**

Run: `node wordpress/hello-child/tests/js/run.mjs`
Expected: all PASS (checker, photo, pricing, payload, idp), exit 0.

- [ ] **Step 5: Commit**

```bash
git add wordpress/hello-child/assets/js/idp-core.js wordpress/hello-child/tests/js/run.mjs
git commit -m "feat(idp): checker core — photocard exemption + in-person PayPoint how-to"
```

---

### Task 3: IDP checker browser widget + enqueue

**Files:**
- Create: `wordpress/hello-child/assets/js/idp-widget.js`
- Modify: `wordpress/hello-child/inc/tools-enqueue.php`

- [ ] **Step 1: Implement the hydrator**

`wordpress/hello-child/assets/js/idp-widget.js`:
```js
// Hydrates #ukv-idp. Needs idp-core.js (window.UKVIdp).
(function () {
  const root = document.getElementById('ukv-idp');
  if (!root || !window.UKVIdp) return;
  const dataUrl = root.getAttribute('data-url'); // path to idp-conventions.json
  const destSel = root.querySelector('[name=dest]');
  const licSel = root.querySelector('[name=licence]');
  const out = root.querySelector('.ukv-idp-result');
  let data = null;

  async function ensure() { if (!data) data = await (await fetch(dataUrl, { cache: 'force-cache' })).json(); return data; }
  async function run() {
    const all = await ensure();
    const c = all[destSel.value];
    if (!c) { out.innerHTML = "We don't cover this country yet."; return; }
    const r = window.UKVIdp.checkIdp(c, licSel.value);
    out.innerHTML = `<p class="ukv-status">${r.message}</p>`
      + (r.needed ? `<p>Validity: ${r.validity}. Cost: £${r.cost_gbp}.</p>` : '')
      + `<p class="ukv-idp-howto">${r.howto}</p>`
      + `<p><a href="https://www.paypoint.com/instore-services/international-driving-permits" rel="nofollow">Find your nearest PayPoint store</a></p>`;
  }
  [destSel, licSel].forEach(el => el.addEventListener('change', run));
  root.querySelector('.ukv-idp-go')?.addEventListener('click', run);
})();
```

- [ ] **Step 2: Enqueue on the IDP check page** — extend `inc/tools-enqueue.php`:
```php
if (is_page('international-driving-permit') || is_page('idp-check')) {
    $dir = get_stylesheet_directory_uri() . '/assets/js';
    wp_enqueue_script('ukv-idp-core', "$dir/idp-core.js", [], '1.0', true);
    wp_enqueue_script('ukv-idp-widget', "$dir/idp-widget.js", ['ukv-idp-core'], '1.0', true);
}
```
(Add `ukv-idp-core` to the module-tag filter list from #3.)

- [ ] **Step 3: Lint-check**

Run: `node --check wordpress/hello-child/assets/js/idp-widget.js; echo "exit=$?"`
Expected: `exit=0`.

- [ ] **Step 4: Commit**

```bash
git add wordpress/hello-child/assets/js/idp-widget.js wordpress/hello-child/inc/tools-enqueue.php
git commit -m "feat(idp): checker widget + conditional enqueue"
```

---

### Task 4: Build the IDP hub (config-and-verify)

**Files:** none tracked (Elementor page).

- [ ] **Step 1: Create hub** `/international-driving-permit/` — title "International Driving Permit (UK): Do You Need One?". RankMath focus "international driving permit uk".

- [ ] **Step 2: Content** — what an IDP is, the 3 conventions (1926/1949/1968) + validity, the **photocard exemption** stated plainly, how/where/cost (PayPoint in person, £5.50). Embed the checker widget:
```html
<div id="ukv-idp" data-url="/wp-content/themes/hello-child/data/idp-conventions.json">
  <select name="dest"><!-- options from idp-conventions keys --></select>
  <select name="licence"><option value="photocard" selected>Photocard licence</option><option value="paper">Paper licence</option></select>
  <button class="ukv-idp-go">Check</button>
  <div class="ukv-idp-result"></div>
</div>
```

- [ ] **Step 3: Schema** — RankMath FAQPage + HowTo on the hub. Reuse the #3 photo maker (UK preset) in a "get your IDP photo" section.

- [ ] **Step 4: Verify**

```bash
curl -s "https://$STAGING/international-driving-permit/" | grep -o "idp-core.js"      # enqueued
curl -s "https://$STAGING/international-driving-permit/" | grep -ci "photocard"        # >=1 (exemption stated)
curl -s "https://$STAGING/international-driving-permit/" | grep -o '"@type":"FAQPage"' # schema
```
Then in a browser: France + photocard → "do not need an IDP"; France + paper → "need a 1968 IDP"; USA + photocard → "need a 1949 IDP"; all show the PayPoint in-person how-to.
Expected: matches; checker accurate.

---

### Task 5: How-to / service spoke (lead capture) (config-and-verify)

**Files:** none tracked.

- [ ] **Step 1: Build spoke-A pages** targeting the lead-gen cluster:
  - `/international-driving-permit/how-to-get/` ("how to get an IDP")
  - `/international-driving-permit/cost/` ("IDP cost")
  - `/international-driving-permit/1949-vs-1968/` ("1949 vs 1968 permit")
  - `/international-driving-permit/online/` — **honest**: "There is no online IDP issuance in the UK — IDPs are issued in person at PayPoint. Here's how to apply." + CTA cross-selling the visa service.

- [ ] **Step 2: Each page** — answer-first passage, FAQ/HowTo schema, up-link to the hub (partial anchor), CTA to the visa funnel `/apply` or a relevant money page.

- [ ] **Step 3: Verify**

```bash
for s in how-to-get cost 1949-vs-1968 online; do
  echo "$s: $(curl -sI "https://$STAGING/international-driving-permit/$s/" | head -1)"
done
curl -s "https://$STAGING/international-driving-permit/online/" | grep -ci "no online"   # >=1 (honest)
```
Expected: all 200; the "online" page states no online issuance.

---

### Task 6: Destination spoke `/driving-in-<country>/` (config-and-verify)

**Files:** none tracked.

- [ ] **Step 1: Build destination pages** merging "IDP [country]" + "driving in [country] requirements/checklist". **Priority order: France/Europe first, then Thailand (low KD), then Spain, Italy, Japan, India, Australia, USA.** Each renders from `idp-conventions.json`: needs-IDP per licence, convention, validity, plus driving prose (side of road, UK sticker, insurance) from `drive_notes`.

- [ ] **Step 2: Cross-link** — each `/driving-in-<country>/` up-links to the hub (partial anchor) + sibling destinations + **cross-links to the matching visa money page `/<country>/`** (#2). This is the target of the money page's `[idp_crosssell]` link (closing the loop).

- [ ] **Step 3: Schema** — Article + HowTo (+ FAQ) per RankMath.

- [ ] **Step 4: Verify the cross-sell loop closes**

```bash
# money page idp_crosssell -> driving page exists (was a 404 before #6)
curl -sI "https://$STAGING/driving-in-turkey/" | head -1                          # 200
curl -s  "https://$STAGING/driving-in-france/" | grep -ci "photocard"             # >=1 (exemption)
# driving page links back to the visa money page (same silo discipline)
curl -s  "https://$STAGING/driving-in-turkey/" | grep -o 'href="[^"]*/turkey/"' | head -1   # match
```
Expected: driving pages live; France page states the exemption; Turkey driving page links to `/turkey/`.

---

### Task 7: Acceptance

**Files:** none.

- [ ] **Step 1: Data + logic tests**

Run: `php wordpress/hello-child/bin/validate-idp.php && node wordpress/hello-child/tests/js/run.mjs`
Expected: `OK: 24 countries valid` and `ALL PASS`.

- [ ] **Step 2: Acceptance (per spec)** — confirm:
  - EU/EEA+CH/NO/IS/LI entries set `idp_required_for_photocard:false`; non-EEA set `true` (validator + spot-check France=false, USA=true).
  - Checker correct for (a) France+photocard=no, (b) France+paper=1968, (c) USA+photocard=1949.
  - Checker always shows the in-person PayPoint how-to; never implies postal/online issuance.
  - Hub + ≥3 destination pages live with correct schema (Rich Results clean), each cross-linked to the hub + a visa money page.
  - Destination pages target the "driving in [country]" cluster (not the dead "do you need IDP for [country]" phrasing).
  - No payment/cart/CRM path in this subsystem.

- [ ] **Step 3: Confirm #2 cross-sell loop** — every money page's `[idp_crosssell]` now resolves to a live `/driving-in-<slug>/` page (no 404s).

```bash
for s in turkey egypt india morocco uae australia usa; do
  echo "$s: $(curl -sI "https://$STAGING/driving-in-$s/" | head -1)"
done
```
Expected: all 200.

- [ ] **Step 4: Tag**

```bash
git add -A && git commit -m "chore(idp): IDP/driving-abroad #6 acceptance passed" || true
git tag idp-live
```

---

## Notes
- The photocard exemption is the trust-critical correctness property — `idp-core.js` tests lock it; never simplify destination pages to "you need a 1968 IDP for France" (false for photocard holders).
- `/driving-in-<slug>/` is the resolution of #2's `[idp_crosssell]` link target — building these closes the loop and removes the temporary 404s noted in the #2 plan.
- Reuses #3 photo maker (UK preset) — no new photo code.
- Long-tail countries beyond the 24 here are the spec's open item; add by extending `idp-conventions.json` (re-run the validator).
