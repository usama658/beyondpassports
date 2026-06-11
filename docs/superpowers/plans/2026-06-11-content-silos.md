# Content Silos (#2) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the shared visa JSON data model + the 7 server-side shortcodes that render it, then compose 8 destination money pages and the support-guide template with correct schema and silo internal-linking.

**Architecture:** PHP is split for testability — **pure render functions** (`inc/visa-render.php`) take a decoded data array and return an HTML string with zero WordPress calls, so they run under plain `php`; **thin shortcode wrappers** (`inc/visa-shortcodes.php`) load JSON and call the pure functions inside `add_shortcode`. A dependency-free PHP validator (`bin/validate-visas.php`) checks every destination file against `data/visa.schema.json`. Money/guide pages are built in Elementor (config-and-verify) using the shortcodes; schema is emitted inline (HowTo, Service) or via RankMath (Breadcrumb, FAQ).

**Tech Stack:** PHP 8 (no framework; tiny custom assert runner — no PHPUnit needed), JSON, WordPress/Elementor + Hello-child theme (from Foundation #1), RankMath.

**Prerequisite:** Foundation #1 complete — `wordpress/hello-child/` theme exists and is active; PHP 8 available on PATH (`php -v`). All paths below are relative to the repo root `C:/Users/mumya/OneDrive/Desktop/Claude Projects/UK VIsa`.

---

## File structure (created by this plan)

- `wordpress/hello-child/data/visa.schema.json` — schema contract for destination files
- `wordpress/hello-child/data/visas/<slug>.json` — one per destination (8 files)
- `wordpress/hello-child/inc/visa-data.php` — `ukv_load_visa($slug)` loader + cache
- `wordpress/hello-child/inc/visa-render.php` — 7 pure `ukv_render_*($data)` functions (no WP)
- `wordpress/hello-child/inc/visa-shortcodes.php` — `add_shortcode` wrappers
- `wordpress/hello-child/bin/validate-visas.php` — JSON-schema validator (CLI)
- `wordpress/hello-child/tests/run.php` — assert runner (CLI, no deps)
- `wordpress/hello-child/functions.php` — modified to `require` the inc files

---

### Task 1: Visa JSON schema + canonical Turkey file + validator (test-first)

**Files:**
- Create: `wordpress/hello-child/data/visa.schema.json`
- Create: `wordpress/hello-child/data/visas/turkey.json`
- Create: `wordpress/hello-child/bin/validate-visas.php`
- Create: `wordpress/hello-child/data/visas/_bad-sample.json` (temporary, deleted in step 6)

- [ ] **Step 1: Write the schema contract**

`wordpress/hello-child/data/visa.schema.json`:
```json
{
  "required_top": ["slug", "name", "region", "visa", "tiers", "updated"],
  "required_visa": ["required_for_uk", "type", "govt_fee_gbp", "processing", "requirements", "how_to_steps"],
  "required_tiers": ["standard_gbp", "express_gbp", "premium_gbp"],
  "enum_type": ["eVisa", "eTA", "visa-on-arrival", "visa-free", "embassy"],
  "enum_idp_permit": ["1926", "1949", "1968", "both", "not-needed"]
}
```

- [ ] **Step 2: Write the canonical Turkey data file**

`wordpress/hello-child/data/visas/turkey.json`:
```json
{
  "slug": "turkey",
  "name": "Turkey",
  "region": "europe-asia",
  "flag": "🇹🇷",
  "visa": {
    "required_for_uk": true,
    "type": "eVisa",
    "evisa_available": true,
    "max_stay_days": 90,
    "validity_days": 180,
    "entry": "single",
    "govt_fee_gbp": 0,
    "processing": { "standard_days": 3, "express_hours": 24 },
    "requirements": ["Passport valid 150+ days from entry", "Return or onward ticket", "Accommodation details"],
    "how_to_steps": ["Check your passport validity", "Complete the application", "Pay and receive your eVisa by email"],
    "notes": "Passport must have a blank page."
  },
  "idp": { "recommended": true, "permit_type": "1949", "notes": "Turkey recognises the 1949 permit." },
  "tiers": { "standard_gbp": 29, "express_gbp": 49, "premium_gbp": 79 },
  "updated": "2026-06-11"
}
```

- [ ] **Step 3: Write a deliberately invalid file to prove the validator fails**

`wordpress/hello-child/data/visas/_bad-sample.json` (missing `tiers`, bad `visa.type`):
```json
{ "slug": "bad", "name": "Bad", "region": "x", "visa": { "required_for_uk": true, "type": "magic-carpet", "govt_fee_gbp": 0, "processing": {}, "requirements": [], "how_to_steps": [] }, "updated": "2026-06-11" }
```

- [ ] **Step 4: Write the validator**

`wordpress/hello-child/bin/validate-visas.php`:
```php
<?php
// Dependency-free validator for data/visas/*.json against data/visa.schema.json
$root   = dirname(__DIR__);
$schema = json_decode(file_get_contents($root . '/data/visa.schema.json'), true);
$files  = glob($root . '/data/visas/*.json');
$errors = [];

foreach ($files as $file) {
    $base = basename($file);
    if (str_starts_with($base, '_')) continue; // skip helper/sample files
    $d = json_decode(file_get_contents($file), true);
    if ($d === null) { $errors[] = "$base: invalid JSON"; continue; }
    foreach ($schema['required_top'] as $k)
        if (!array_key_exists($k, $d)) $errors[] = "$base: missing top key '$k'";
    foreach ($schema['required_visa'] as $k)
        if (!isset($d['visa']) || !array_key_exists($k, $d['visa'])) $errors[] = "$base: missing visa.$k";
    foreach ($schema['required_tiers'] as $k)
        if (!isset($d['tiers']) || !array_key_exists($k, $d['tiers'])) $errors[] = "$base: missing tiers.$k";
    if (isset($d['visa']['type']) && !in_array($d['visa']['type'], $schema['enum_type'], true))
        $errors[] = "$base: bad visa.type '{$d['visa']['type']}'";
    if (isset($d['idp']['permit_type']) && !in_array($d['idp']['permit_type'], $schema['enum_idp_permit'], true))
        $errors[] = "$base: bad idp.permit_type '{$d['idp']['permit_type']}'";
    if (isset($d['visa']['govt_fee_gbp']) && !is_numeric($d['visa']['govt_fee_gbp']))
        $errors[] = "$base: visa.govt_fee_gbp not numeric";
}

if ($errors) { fwrite(STDERR, implode("\n", $errors) . "\n"); echo count($errors) . " ERROR(S)\n"; exit(1); }
echo "OK: " . count($files) . " file(s) valid\n"; exit(0);
```

- [ ] **Step 5: Run validator — expect FAIL on the bad sample**

Run: `php wordpress/hello-child/bin/validate-visas.php`
Expected: exit 1, prints `_bad-sample.json: bad visa.type 'magic-carpet'` and `_bad-sample.json: missing tiers.standard_gbp` etc. (Note: `_`-prefixed files are skipped by the loop, so to prove failure, temporarily rename it: `mv .../_bad-sample.json .../bad-sample.json`, run, see exit 1, then rename back.)

Run: `mv wordpress/hello-child/data/visas/_bad-sample.json wordpress/hello-child/data/visas/bad-sample.json && php wordpress/hello-child/bin/validate-visas.php; echo "exit=$?"`
Expected: errors listed, `exit=1`.

- [ ] **Step 6: Delete the bad sample, re-run — expect PASS**

Run: `rm wordpress/hello-child/data/visas/bad-sample.json && php wordpress/hello-child/bin/validate-visas.php; echo "exit=$?"`
Expected: `OK: 1 file(s) valid`, `exit=0`.

- [ ] **Step 7: Commit**

```bash
git add wordpress/hello-child/data/visa.schema.json wordpress/hello-child/data/visas/turkey.json wordpress/hello-child/bin/validate-visas.php
git commit -m "feat(silos): visa JSON schema + Turkey canonical + dependency-free validator"
```

---

### Task 2: Visa data loader (test-first)

**Files:**
- Create: `wordpress/hello-child/inc/visa-data.php`
- Create: `wordpress/hello-child/tests/run.php`

- [ ] **Step 1: Write the test runner + first failing test**

`wordpress/hello-child/tests/run.php`:
```php
<?php
// Tiny assert runner — no PHPUnit. Run: php tests/run.php
define('UKV_DATA_DIR', dirname(__DIR__) . '/data');
require dirname(__DIR__) . '/inc/visa-data.php';
require dirname(__DIR__) . '/inc/visa-render.php';

$fail = 0;
function check(string $name, bool $cond): void {
    global $fail;
    if ($cond) { echo "PASS $name\n"; } else { echo "FAIL $name\n"; $GLOBALS['fail']++; }
}

// --- visa-data ---
$t = ukv_load_visa('turkey');
check('load turkey returns array', is_array($t));
check('turkey name', ($t['name'] ?? null) === 'Turkey');
check('missing slug returns null', ukv_load_visa('atlantis') === null);
check('slug is sanitised', ukv_load_visa('../../etc/passwd') === null);

echo $fail ? "\n$fail FAILURE(S)\n" : "\nALL PASS\n";
exit($fail ? 1 : 0);
```

- [ ] **Step 2: Run — expect FAIL (loader + render files don't exist)**

Run: `php wordpress/hello-child/tests/run.php`
Expected: PHP fatal `require ... inc/visa-data.php` (No such file). That is the failing state.

- [ ] **Step 3: Implement the loader**

`wordpress/hello-child/inc/visa-data.php`:
```php
<?php
// Loads + caches one destination's data. UKV_DATA_DIR is defined by WP bootstrap or the test runner.
if (!defined('UKV_DATA_DIR')) {
    define('UKV_DATA_DIR', function_exists('get_stylesheet_directory')
        ? get_stylesheet_directory() . '/data'
        : __DIR__ . '/../data');
}

function ukv_load_visa(string $slug): ?array {
    static $cache = [];
    $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($slug)); // sanitise: blocks traversal
    if ($slug === '') return null;
    if (isset($cache[$slug])) return $cache[$slug];
    $file = UKV_DATA_DIR . '/visas/' . $slug . '.json';
    if (!is_file($file)) return null;
    $data = json_decode(file_get_contents($file), true);
    if (!is_array($data)) return null;
    return $cache[$slug] = $data;
}
```

- [ ] **Step 4: Create a stub render file so the runner can require it**

`wordpress/hello-child/inc/visa-render.php`:
```php
<?php
// Pure render functions (no WordPress). Each takes a decoded data array, returns an HTML string.
function ukv_esc(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
```

- [ ] **Step 5: Run — expect PASS**

Run: `php wordpress/hello-child/tests/run.php`
Expected: 4 PASS lines, `ALL PASS`, exit 0.

- [ ] **Step 6: Commit**

```bash
git add wordpress/hello-child/inc/visa-data.php wordpress/hello-child/inc/visa-render.php wordpress/hello-child/tests/run.php
git commit -m "feat(silos): visa data loader with path sanitisation + test runner"
```

---

### Task 3: Render `[visa_status]` + `[visa_requirements]` (test-first)

**Files:**
- Modify: `wordpress/hello-child/inc/visa-render.php`
- Modify: `wordpress/hello-child/tests/run.php`

- [ ] **Step 1: Add failing tests**

Append before the summary line in `tests/run.php`:
```php
// --- status ---
$statusReq = ukv_render_visa_status($t);
check('status shows eVisa', str_contains($statusReq, 'eVisa'));
check('status shows 90 days', str_contains($statusReq, '90'));
$visaFree = ['visa' => ['required_for_uk' => false, 'type' => 'visa-free', 'max_stay_days' => 90], 'name' => 'Morocco'];
check('status visa-free wording', str_contains(ukv_render_visa_status($visaFree), 'No visa needed'));

// --- requirements ---
$reqHtml = ukv_render_visa_requirements($t);
check('requirements is a list', str_contains($reqHtml, '<ul') && str_contains($reqHtml, '<li'));
check('requirements escapes + lists all 3', substr_count($reqHtml, '<li') === 3);
```

- [ ] **Step 2: Run — expect FAIL**

Run: `php wordpress/hello-child/tests/run.php`
Expected: fatal `Call to undefined function ukv_render_visa_status()`.

- [ ] **Step 3: Implement both render functions**

Append to `inc/visa-render.php`:
```php
function ukv_render_visa_status(array $d): string {
    $v = $d['visa'] ?? [];
    if (empty($v['required_for_uk'])) {
        $days = isset($v['max_stay_days']) ? ' for up to ' . (int)$v['max_stay_days'] . ' days' : '';
        return '<p class="ukv-status ukv-status--free"><strong>No visa needed</strong> for UK citizens'
            . ukv_esc($days) . '.</p>';
    }
    $type = ukv_esc($v['type'] ?? 'visa');
    $days = isset($v['max_stay_days']) ? ' — up to ' . (int)$v['max_stay_days'] . ' days' : '';
    return '<p class="ukv-status ukv-status--required"><strong>' . $type
        . ' required</strong> for UK citizens' . ukv_esc($days) . '.</p>';
}

function ukv_render_visa_requirements(array $d): string {
    $items = $d['visa']['requirements'] ?? [];
    if (!$items) return '';
    $li = '';
    foreach ($items as $it) $li .= '<li>' . ukv_esc((string)$it) . '</li>';
    return '<ul class="ukv-requirements">' . $li . '</ul>';
}
```

- [ ] **Step 4: Run — expect PASS**

Run: `php wordpress/hello-child/tests/run.php`
Expected: all PASS, exit 0.

- [ ] **Step 5: Commit**

```bash
git add wordpress/hello-child/inc/visa-render.php wordpress/hello-child/tests/run.php
git commit -m "feat(silos): [visa_status] + [visa_requirements] render functions"
```

---

### Task 4: Render `[visa_fees]` + `[visa_processing]` (test-first)

**Files:**
- Modify: `wordpress/hello-child/inc/visa-render.php`
- Modify: `wordpress/hello-child/tests/run.php`

- [ ] **Step 1: Add failing tests**

Append to `tests/run.php` (before summary):
```php
// --- fees ---
$fees = ukv_render_visa_fees($t);
check('fees shows standard 29', str_contains($fees, '29'));
check('fees shows express 49', str_contains($fees, '49'));
check('fees shows premium 79', str_contains($fees, '79'));
check('fees labels govt fee at cost', stripos($fees, 'government fee') !== false);
check('fees hidden for visa-free', ukv_render_visa_fees($visaFree) === '');

// --- processing ---
$proc = ukv_render_visa_processing($t);
check('processing shows standard days', str_contains($proc, '3'));
check('processing shows express hours', str_contains($proc, '24'));
```

- [ ] **Step 2: Run — expect FAIL**

Run: `php wordpress/hello-child/tests/run.php`
Expected: fatal `undefined function ukv_render_visa_fees()`.

- [ ] **Step 3: Implement both**

Append to `inc/visa-render.php`:
```php
function ukv_render_visa_fees(array $d): string {
    $v = $d['visa'] ?? [];
    if (empty($v['required_for_uk'])) return ''; // visa-free: nothing to sell
    $t = $d['tiers'] ?? [];
    $govt = isset($v['govt_fee_gbp']) ? '£' . number_format((float)$v['govt_fee_gbp'], 2) : '—';
    $row = fn($label, $amt) => '<tr><td>' . ukv_esc($label) . '</td><td>£'
        . number_format((float)$amt, 0) . ' service + ' . $govt . ' government fee</td></tr>';
    return '<table class="ukv-fees"><thead><tr><th>Service</th><th>Price</th></tr></thead><tbody>'
        . $row('Standard', $t['standard_gbp'] ?? 0)
        . $row('Express',  $t['express_gbp']  ?? 0)
        . $row('Premium',  $t['premium_gbp']  ?? 0)
        . '</tbody></table>'
        . '<p class="ukv-fees-note">Government fee shown at cost; our service fee is additional.</p>';
}

function ukv_render_visa_processing(array $d): string {
    $p = $d['visa']['processing'] ?? [];
    if (!$p) return '';
    $out = '<ul class="ukv-processing">';
    if (isset($p['standard_days'])) $out .= '<li>Standard: ~' . (int)$p['standard_days'] . ' days</li>';
    if (isset($p['express_hours'])) $out .= '<li>Express: ~' . (int)$p['express_hours'] . ' hours (faster handling, not a faster government decision)</li>';
    return $out . '</ul>';
}
```

- [ ] **Step 4: Run — expect PASS**

Run: `php wordpress/hello-child/tests/run.php`
Expected: all PASS, exit 0.

- [ ] **Step 5: Commit**

```bash
git add wordpress/hello-child/inc/visa-render.php wordpress/hello-child/tests/run.php
git commit -m "feat(silos): [visa_fees] (govt-fee-at-cost, visa-free aware) + [visa_processing]"
```

---

### Task 5: Render `[visa_howto]` with HowTo JSON-LD (test-first)

**Files:**
- Modify: `wordpress/hello-child/inc/visa-render.php`
- Modify: `wordpress/hello-child/tests/run.php`

- [ ] **Step 1: Add failing tests**

Append to `tests/run.php`:
```php
// --- howto ---
$howto = ukv_render_visa_howto($t);
check('howto numbered list', str_contains($howto, '<ol'));
check('howto lists all 3 steps', substr_count($howto, '<li') === 3);
check('howto emits HowTo JSON-LD', str_contains($howto, '"@type":"HowTo"') || str_contains($howto, '"@type": "HowTo"'));
check('howto JSON-LD is valid json', (function() use ($t) {
    $h = ukv_render_visa_howto($t);
    if (!preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $h, $m)) return false;
    return json_decode($m[1], true) !== null;
})());
```

- [ ] **Step 2: Run — expect FAIL**

Run: `php wordpress/hello-child/tests/run.php`
Expected: fatal `undefined function ukv_render_visa_howto()`.

- [ ] **Step 3: Implement**

Append to `inc/visa-render.php`:
```php
function ukv_render_visa_howto(array $d): string {
    $steps = $d['visa']['how_to_steps'] ?? [];
    if (!$steps) return '';
    $name = ukv_esc($d['name'] ?? '');
    $li = ''; $jsonSteps = [];
    foreach ($steps as $i => $s) {
        $li .= '<li>' . ukv_esc((string)$s) . '</li>';
        $jsonSteps[] = ['@type' => 'HowToStep', 'position' => $i + 1, 'name' => (string)$s];
    }
    $ld = [
        '@context' => 'https://schema.org',
        '@type'    => 'HowTo',
        'name'     => 'How to get a ' . ($d['name'] ?? '') . ' visa',
        'step'     => $jsonSteps,
    ];
    $json = json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return '<ol class="ukv-howto">' . $li . '</ol>'
        . '<script type="application/ld+json">' . $json . '</script>';
}
```

- [ ] **Step 4: Run — expect PASS**

Run: `php wordpress/hello-child/tests/run.php`
Expected: all PASS, exit 0.

- [ ] **Step 5: Commit**

```bash
git add wordpress/hello-child/inc/visa-render.php wordpress/hello-child/tests/run.php
git commit -m "feat(silos): [visa_howto] with valid HowTo JSON-LD"
```

---

### Task 6: Render `[idp_crosssell]` + `[apply_cta]` (test-first)

**Files:**
- Modify: `wordpress/hello-child/inc/visa-render.php`
- Modify: `wordpress/hello-child/tests/run.php`

- [ ] **Step 1: Add failing tests**

Append to `tests/run.php`:
```php
// --- idp cross-sell (links to #6 driving silo) ---
$idp = ukv_render_idp_crosssell($t);
check('idp crosssell links to driving page', str_contains($idp, '/driving-in-turkey/'));
check('idp crosssell mentions permit type', str_contains($idp, '1949'));
$noIdp = ['name' => 'X', 'idp' => ['recommended' => false]];
check('idp crosssell empty when not recommended', ukv_render_idp_crosssell($noIdp) === '');

// --- apply CTA ---
$cta = ukv_render_apply_cta($t, 'visa');
check('cta links to apply with dest', str_contains($cta, '/apply?dest=turkey'));
check('cta hidden for visa-free', ukv_render_apply_cta($visaFree, 'visa') === '');
```

- [ ] **Step 2: Run — expect FAIL**

Run: `php wordpress/hello-child/tests/run.php`
Expected: fatal `undefined function ukv_render_idp_crosssell()`.

- [ ] **Step 3: Implement both**

Append to `inc/visa-render.php`:
```php
function ukv_render_idp_crosssell(array $d): string {
    $idp = $d['idp'] ?? [];
    if (empty($idp['recommended'])) return '';
    $slug = ukv_esc($d['slug'] ?? '');
    $name = ukv_esc($d['name'] ?? 'there');
    $permit = ukv_esc((string)($idp['permit_type'] ?? ''));
    return '<aside class="ukv-idp-crosssell"><strong>Driving in ' . $name . '?</strong> '
        . 'You may need a ' . $permit . ' International Driving Permit. '
        . '<a href="/driving-in-' . $slug . '/">See our driving-in-' . $name . ' guide</a>.</aside>';
}

function ukv_render_apply_cta(array $d, string $product = 'visa'): string {
    if (empty($d['visa']['required_for_uk'])) return ''; // visa-free: no order to start
    $slug = ukv_esc($d['slug'] ?? '');
    $product = ukv_esc(preg_replace('/[^a-z]/', '', strtolower($product)) ?: 'visa');
    return '<a class="ukv-cta elementor-button" href="/apply?dest=' . $slug
        . '&product=' . $product . '">Start your application</a>';
}
```

- [ ] **Step 4: Run — expect PASS**

Run: `php wordpress/hello-child/tests/run.php`
Expected: all PASS, exit 0.

- [ ] **Step 5: Commit**

```bash
git add wordpress/hello-child/inc/visa-render.php wordpress/hello-child/tests/run.php
git commit -m "feat(silos): [idp_crosssell] (→ #6 driving silo) + [apply_cta] (visa-free aware)"
```

---

### Task 7: Register shortcodes in WordPress + wire into theme

**Files:**
- Create: `wordpress/hello-child/inc/visa-shortcodes.php`
- Modify: `wordpress/hello-child/functions.php`

- [ ] **Step 1: Create the shortcode wrappers**

`wordpress/hello-child/inc/visa-shortcodes.php`:
```php
<?php
// Thin WordPress wrappers. Each loads the destination JSON and calls a pure render function.
require_once __DIR__ . '/visa-data.php';
require_once __DIR__ . '/visa-render.php';

function ukv_sc_dest(array $atts): ?array {
    $slug = isset($atts['dest']) ? (string)$atts['dest'] : '';
    return ukv_load_visa($slug); // null if missing → caller returns ''
}

add_shortcode('visa_status',       fn($a) => ($d = ukv_sc_dest((array)$a)) ? ukv_render_visa_status($d) : '');
add_shortcode('visa_requirements', fn($a) => ($d = ukv_sc_dest((array)$a)) ? ukv_render_visa_requirements($d) : '');
add_shortcode('visa_howto',        fn($a) => ($d = ukv_sc_dest((array)$a)) ? ukv_render_visa_howto($d) : '');
add_shortcode('visa_fees',         fn($a) => ($d = ukv_sc_dest((array)$a)) ? ukv_render_visa_fees($d) : '');
add_shortcode('visa_processing',   fn($a) => ($d = ukv_sc_dest((array)$a)) ? ukv_render_visa_processing($d) : '');
add_shortcode('idp_crosssell',     fn($a) => ($d = ukv_sc_dest((array)$a)) ? ukv_render_idp_crosssell($d) : '');
add_shortcode('apply_cta',         fn($a) => ($d = ukv_sc_dest((array)$a)) ? ukv_render_apply_cta($d, $a['product'] ?? 'visa') : '');
```

- [ ] **Step 2: Require it from the theme**

Append to `wordpress/hello-child/functions.php` (before the closing of the file, top-level):
```php
// Content-silo visa shortcodes (#2)
require_once __DIR__ . '/inc/visa-shortcodes.php';
```

- [ ] **Step 3: Sync theme to WordPress + smoke-test a shortcode on staging**

Upload the changed `hello-child` (functions.php + inc/ + data/ + bin/) to `wp-content/themes/hello-child/` on `$STAGING`. Create a throwaway page containing `[visa_status dest=turkey]` and `[visa_fees dest=turkey]`.

Run (substitute `$STAGING`):
```bash
curl -s "https://$STAGING/sc-smoke-test/" | grep -o "eVisa required"        # expect a match
curl -s "https://$STAGING/sc-smoke-test/" | grep -o "government fee"          # expect a match
curl -s "https://$STAGING/sc-smoke-test/" | grep -c "elementor-button"        # expect >=0 (no CTA on this page is fine)
```
Expected: the status + fees HTML render server-side (visible with JS disabled). Then delete the throwaway page.

- [ ] **Step 4: Verify a bad dest renders nothing (no fatal)**

Add `[visa_status dest=atlantis]` to the throwaway page (or re-test):
```bash
curl -sI "https://$STAGING/sc-smoke-test/" | head -1   # expect 200, no 500
```
Expected: HTTP 200; the bad shortcode outputs empty string, page still renders.

- [ ] **Step 5: Commit**

```bash
git add wordpress/hello-child/inc/visa-shortcodes.php wordpress/hello-child/functions.php
git commit -m "feat(silos): register 7 visa shortcodes + wire into hello-child theme"
```

---

### Task 8: Remaining 7 destination data files (validated)

**Files:**
- Create: `wordpress/hello-child/data/visas/{egypt,india,morocco,uae,australia,usa,schengen}.json`

> Values below are best-known starting data; the spec's open item "accurate per-country data" requires verifying each against the official government source before launch. Verification is a content-QA step, not a code change.

- [ ] **Step 1: Create `egypt.json`**
```json
{ "slug": "egypt", "name": "Egypt", "region": "africa", "flag": "🇪🇬",
  "visa": { "required_for_uk": true, "type": "eVisa", "evisa_available": true, "max_stay_days": 30, "validity_days": 90, "entry": "single", "govt_fee_gbp": 20,
    "processing": { "standard_days": 7, "express_hours": 72 },
    "requirements": ["Passport valid 6+ months from entry", "Confirmed accommodation", "Return ticket"],
    "how_to_steps": ["Check passport validity", "Complete the application", "Pay and receive your eVisa by email"], "notes": "" },
  "idp": { "recommended": true, "permit_type": "1949", "notes": "" },
  "tiers": { "standard_gbp": 29, "express_gbp": 49, "premium_gbp": 79 }, "updated": "2026-06-11" }
```

- [ ] **Step 2: Create `india.json`**
```json
{ "slug": "india", "name": "India", "region": "asia", "flag": "🇮🇳",
  "visa": { "required_for_uk": true, "type": "eVisa", "evisa_available": true, "max_stay_days": 30, "validity_days": 365, "entry": "multiple", "govt_fee_gbp": 22,
    "processing": { "standard_days": 4, "express_hours": 48 },
    "requirements": ["Passport valid 6+ months with 2 blank pages", "Passport-style photo", "Return or onward ticket"],
    "how_to_steps": ["Check passport validity", "Complete the e-Visa application", "Pay and receive your e-Visa by email"], "notes": "" },
  "idp": { "recommended": true, "permit_type": "1949", "notes": "" },
  "tiers": { "standard_gbp": 29, "express_gbp": 49, "premium_gbp": 79 }, "updated": "2026-06-11" }
```

- [ ] **Step 3: Create `morocco.json` (visa-free)**
```json
{ "slug": "morocco", "name": "Morocco", "region": "africa", "flag": "🇲🇦",
  "visa": { "required_for_uk": false, "type": "visa-free", "evisa_available": false, "max_stay_days": 90, "validity_days": 0, "entry": "single", "govt_fee_gbp": 0,
    "processing": { "standard_days": 0, "express_hours": 0 },
    "requirements": ["Passport valid 6+ months from entry"],
    "how_to_steps": ["Check passport validity", "Travel — no visa required for stays up to 90 days"], "notes": "Visa-free for UK citizens up to 90 days." },
  "idp": { "recommended": true, "permit_type": "1968", "notes": "" },
  "tiers": { "standard_gbp": 29, "express_gbp": 49, "premium_gbp": 79 }, "updated": "2026-06-11" }
```

- [ ] **Step 4: Create `uae.json` (visa-free)**
```json
{ "slug": "uae", "name": "United Arab Emirates", "region": "middle-east", "flag": "🇦🇪",
  "visa": { "required_for_uk": false, "type": "visa-free", "evisa_available": false, "max_stay_days": 90, "validity_days": 180, "entry": "multiple", "govt_fee_gbp": 0,
    "processing": { "standard_days": 0, "express_hours": 0 },
    "requirements": ["Passport valid 6+ months from entry"],
    "how_to_steps": ["Check passport validity", "Receive a free visa-on-arrival stamp for up to 90 days"], "notes": "UK citizens get a free 90-day entry on arrival." },
  "idp": { "recommended": true, "permit_type": "1968", "notes": "" },
  "tiers": { "standard_gbp": 29, "express_gbp": 49, "premium_gbp": 79 }, "updated": "2026-06-11" }
```

- [ ] **Step 5: Create `australia.json` (ETA)**
```json
{ "slug": "australia", "name": "Australia", "region": "oceania", "flag": "🇦🇺",
  "visa": { "required_for_uk": true, "type": "eTA", "evisa_available": true, "max_stay_days": 90, "validity_days": 365, "entry": "multiple", "govt_fee_gbp": 10,
    "processing": { "standard_days": 2, "express_hours": 24 },
    "requirements": ["Passport valid for the duration of stay", "No serious criminal record"],
    "how_to_steps": ["Check passport eligibility", "Complete the ETA/eVisitor application", "Receive approval linked to your passport by email"], "notes": "ETA is electronically linked to your passport; no document is issued." },
  "idp": { "recommended": true, "permit_type": "1949", "notes": "" },
  "tiers": { "standard_gbp": 29, "express_gbp": 49, "premium_gbp": 79 }, "updated": "2026-06-11" }
```

- [ ] **Step 6: Create `usa.json` (ESTA / eTA-type)**
```json
{ "slug": "usa", "name": "United States", "region": "north-america", "flag": "🇺🇸",
  "visa": { "required_for_uk": true, "type": "eTA", "evisa_available": true, "max_stay_days": 90, "validity_days": 730, "entry": "multiple", "govt_fee_gbp": 17,
    "processing": { "standard_days": 3, "express_hours": 24 },
    "requirements": ["Passport valid for the duration of stay", "ESTA-eligible (Visa Waiver Program) traveller"],
    "how_to_steps": ["Check ESTA eligibility", "Complete the ESTA application", "Receive approval linked to your passport by email"], "notes": "ESTA is electronically linked to your passport; no document is issued." },
  "idp": { "recommended": true, "permit_type": "1949", "notes": "Rules vary by state." },
  "tiers": { "standard_gbp": 29, "express_gbp": 49, "premium_gbp": 79 }, "updated": "2026-06-11" }
```

- [ ] **Step 7: Create `schengen.json` (hub; visa-free for UK)**
```json
{ "slug": "schengen", "name": "Schengen Area", "region": "europe", "flag": "🇪🇺",
  "visa": { "required_for_uk": false, "type": "visa-free", "evisa_available": false, "max_stay_days": 90, "validity_days": 180, "entry": "multiple", "govt_fee_gbp": 0,
    "processing": { "standard_days": 0, "express_hours": 0 },
    "requirements": ["Passport valid 3+ months beyond departure and issued within last 10 years"],
    "how_to_steps": ["Check passport validity", "Travel visa-free for up to 90 days in any 180-day period (ETIAS pre-authorisation expected in future)"], "notes": "Visa-free for UK citizens; ETIAS travel authorisation expected to apply later." },
  "idp": { "recommended": false, "permit_type": "1968", "notes": "Not needed for UK photocard licence holders." },
  "tiers": { "standard_gbp": 99, "express_gbp": 129, "premium_gbp": 159 }, "updated": "2026-06-11" }
```

- [ ] **Step 8: Validate all 8 files**

Run: `php wordpress/hello-child/bin/validate-visas.php; echo "exit=$?"`
Expected: `OK: 8 file(s) valid`, `exit=0`.

- [ ] **Step 9: Re-run unit tests (loader/render still green against real data)**

Run: `php wordpress/hello-child/tests/run.php`
Expected: `ALL PASS`.

- [ ] **Step 10: Commit**

```bash
git add wordpress/hello-child/data/visas/
git commit -m "feat(silos): 7 remaining destination data files (validated)"
```

---

### Task 9: Build the Turkey money page (config-and-verify, canonical)

**Files:** none tracked (Elementor page in DB). Composition per spec + parent §13.2.

- [ ] **Step 1: Create the page** `/turkey/` (Title "Turkey Visa for UK Citizens"). Set RankMath focus keyword "turkey visa".

- [ ] **Step 2: Lay out blocks in this exact order** (Elementor Text/Shortcode widgets):
  H1 "Turkey Visa for UK Citizens" → `[visa_status dest=turkey]` → intro prose (hand-written, 100–150 words) → `[visa_requirements dest=turkey]` → `[visa_fees dest=turkey]` → `[apply_cta dest=turkey product=visa]` → `[visa_howto dest=turkey]` → `[visa_processing dest=turkey]` → `[idp_crosssell dest=turkey]` → FAQ (RankMath FAQ block, 3–5 Q&As → emits FAQPage schema) → "Turkey travel guides" link list (silo up-links, added in Task 11).

- [ ] **Step 3: Add Service schema** via RankMath → page Schema tab → add **Service** (name "Turkey Visa Service", provider = the Organization, areaServed "GB"). Breadcrumb is automatic (Foundation RankMath).

- [ ] **Step 4: Enforce anchor discipline** — at most ONE exact-match internal anchor ("turkey visa") on the page; other internal links use partial/branded anchors (parent §3.2).

- [ ] **Step 5: Verify render + schema**

```bash
curl -s "https://$STAGING/turkey/" | grep -o "eVisa required"              # status block
curl -s "https://$STAGING/turkey/" | grep -o '"@type":"HowTo"'             # HowTo JSON-LD
curl -s "https://$STAGING/turkey/" | grep -o '"@type":"Service"'           # Service schema
curl -s "https://$STAGING/turkey/" | grep -o '"@type":"FAQPage"'           # FAQ schema
curl -s "https://$STAGING/turkey/" | grep -o '"@type":"BreadcrumbList"'    # breadcrumb
```
Also paste `https://$STAGING/turkey/` into Google Rich Results Test → Service + HowTo + FAQPage + BreadcrumbList detected, 0 errors.
Expected: all five greps match; Rich Results clean.

---

### Task 10: Replicate money pages for the other 7 destinations (config-and-verify)

**Files:** none tracked (Elementor pages).

- [ ] **Step 1: Duplicate the Turkey page** 7× to slugs `/egypt/`, `/india/`, `/morocco/`, `/uae/`, `/australia/`, `/usa/`, `/schengen/`; swap every shortcode `dest=` to the matching slug and rewrite the intro prose + FAQ + H1 per destination. Set each page's RankMath focus keyword (e.g. "egypt visa", "india visa", "esta usa", "australia eta").

- [ ] **Step 2: Confirm visa-free pages behave** — on `/morocco/`, `/uae/`, `/schengen/` the `[visa_status]` shows "No visa needed", and `[visa_fees]` + `[apply_cta]` render empty (no sell). Replace the empty CTA area with an IDP/driving cross-sell + travel-guide links instead.

- [ ] **Step 3: Verify each page renders + 200**

```bash
for s in egypt india morocco uae australia usa schengen; do
  echo "$s: $(curl -sI "https://$STAGING/$s/" | head -1)"
done
curl -s "https://$STAGING/morocco/" | grep -o "No visa needed"     # expect match (visa-free wording)
curl -s "https://$STAGING/usa/"     | grep -o '"@type":"Service"'  # expect match (sellable ETA)
```
Expected: all 200; Morocco shows visa-free wording; USA shows Service schema.

- [ ] **Step 4: Verify no visa-free page exposes a fee table or apply CTA**

```bash
curl -s "https://$STAGING/uae/" | grep -c "Start your application"   # expect 0
curl -s "https://$STAGING/uae/" | grep -c "ukv-fees"                 # expect 0
```
Expected: both 0.

---

### Task 11: Support-guide template + one guide per silo + internal-linking discipline

**Files:** none tracked (Elementor/WP posts).

- [ ] **Step 1: Build the guide template** — a standard post layout: H1 (informational query), answer-first opening passage (50–80 words that directly answer the query, AI-citable), 800–1500 words body, RankMath Article schema enabled, optional FAQ/HowTo block where the format fits.

- [ ] **Step 2: Publish one launch guide per silo** (highest-volume per parent §3.1; for non-Turkey use the destination's top informational query), each linking: **1 up-link to its money page** (partial/branded anchor, e.g. "applying for your Turkey visa"), **1–2 sibling guides**, and **1 contextual CTA** to `/apply` or the checker. Turkey first guide = "Things to do in Side, Turkey".

- [ ] **Step 3: Wire money-page → guide links** — add the "travel guides" link list at the bottom of each money page (Task 9 step 2 placeholder) pointing to that silo's guides only.

- [ ] **Step 4: Verify silo discipline (no cross-silo leakage)**

```bash
# A Turkey guide must link UP to /turkey/ and not to other destination money pages
curl -s "https://$STAGING/things-to-do-in-side/" | grep -o 'href="[^"]*/turkey/"' | head -1   # expect a match
curl -s "https://$STAGING/things-to-do-in-side/" | grep -oE 'href="[^"]*/(egypt|india|usa)/"' # expect NO matches
```
Expected: up-link to `/turkey/` present; zero links to other silos' money pages.

- [ ] **Step 5: Verify Article schema**

```bash
curl -s "https://$STAGING/things-to-do-in-side/" | grep -o '"@type":"Article"'   # expect a match
```
Expected: Article schema present; Rich Results Test clean.

---

### Task 12: Final acceptance verification (per spec)

**Files:** none.

- [ ] **Step 1: Data integrity**

Run: `php wordpress/hello-child/bin/validate-visas.php && php wordpress/hello-child/tests/run.php`
Expected: `OK: 8 file(s) valid` and `ALL PASS`.

- [ ] **Step 2: Edit-propagation check** — change `tiers.standard_gbp` in `turkey.json` from 29 to 31, sync theme, reload `/turkey/`, confirm the fee table shows 31, then revert to 29 + re-sync.

```bash
curl -s "https://$STAGING/turkey/" | grep -o "£31"   # expect match while changed
```
Expected: rendered page reflects the JSON edit (proves single-source rendering).

- [ ] **Step 3: All 8 money pages live + schema** — confirm each returns 200 and the four schema types validate (Service on sellable pages; FAQPage + BreadcrumbList on all; HowTo where a visa exists).

```bash
for s in turkey egypt india morocco uae australia usa schengen; do
  echo "$s $(curl -sI "https://$STAGING/$s/" | head -1)"
done
```
Expected: all 200.

- [ ] **Step 4: Anchor + linking discipline spot-check** — pick 2 money pages and 2 guides; confirm ≤1 exact-match anchor per page and guides link only within their silo (manual review + the Task 11 greps).

- [ ] **Step 5: One support guide per launched silo published** with Article schema and a correct up-link (re-run Task 11 greps for each silo's launch guide).

- [ ] **Step 6: Tag the milestone**

```bash
git add -A && git commit -m "chore(silos): content silos #2 acceptance passed" || true
git tag silos-live
```

---

## Notes for dependent sub-plans
- `data/visas/*.json` is the shared source: #3 checker fetches the same files client-side; #4 funnel prices from `tiers` + `visa.govt_fee_gbp`; #6 reads `idp.*` for the cross-sell target `/driving-in-<slug>/`.
- Visa-free destinations (Morocco, UAE, Schengen) have no sellable funnel path — money pages are informational + cross-sell only. #4 must treat a visa-free `dest` as non-orderable.
- The `[idp_crosssell]` link target `/driving-in-<slug>/` is built by #6; until then the link 404s (acceptable pre-#6).
