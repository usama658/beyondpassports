# International Market Foundation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** make `beyondpassports.com` able to serve per-market pages for `/za/`, `/ae/`, `/us/`, `/ca/` from the existing Laravel app, switchable by config, with correct chrome, canonical, hreflang, robots state and analytics tag, while `.co.uk` stays untouched.

**Architecture:** one `markets` config block drives a `Market` value object resolved from the `{market}` URL segment by middleware (never from Host or IP). A `market_url()` helper makes all chrome links market-aware. Market pages are standalone Blade views (same pattern as the existing lp-* pages) sharing `lp-chrome`, `lp-footer`, a new trust-strip partial and a new hreflang partial. A second sitemap and a single host-bound root route complete the `.com` surface.

**Tech Stack:** PHP 8.2+, Laravel 12 (`bootstrap/app.php` middleware config), Blade, PHPUnit via `php artisan test` (sqlite in-memory), existing partials `partials.lp-chrome`, `partials.lp-footer`, `partials.disclaimer-strip`, `partials.analytics-head`, `partials.utm-capture`, `App\Support\SiteStats`.

**Spec:** `ukv-app/docs/superpowers/specs/2026-10-06-international-market-foundation-design.md` (owner-approved 2026-10-06). Decision basis: `ukv-app/docs/product-goals-2026-10.md`, `ukv-app/docs/superpowers/research/2026-10-06-README-decision-basis.md`.

All paths below are relative to `ukv-app/`. Run every command from `ukv-app/`.

## Global Constraints

- No Host or IP based market detection anywhere except the single `Route::domain` root route in Task 12 (spec 4.2, 4.4).
- Every market ships `enabled => false` and `indexable => false` by default (spec 4.1, assumption A2).
- Prices stay `null` in config; a null price renders nothing, never a placeholder string (spec 4.1, 6).
- No em-dashes in any user-facing copy. No "N services" style counters. No "guaranteed", "early", "priority", "fast-track" or dated appointment wording (spec 12, memory no-em-dash-ai-tells, flows-serve-not-inform).
- Never call `App\Support\SlotBoard` or `App\Support\SlotTiles` from a `.com` route (spec 6).
- UAE copy never offers a WhatsApp call (assumption A5). No "30-minute" promise on `.com` (assumption A3).
- Always include `partials.disclaimer-strip` via the partial, never bare `.disc-strip` markup (memory disclaimer-strip-partial).
- Keep `ukv_`/`ukv.`/`UKV_` technical identifiers; display copy says Beyond Passports (memory brand-beyond-passports).
- Existing suites must stay green: `php artisan test --filter=AvailabilityServiceTest`, `--filter=LpAssemblerTest`, `--filter=LpFlow`.
- Commit after every task. Never push; the owner pushes (memory no-auto-deploy).

## Review Focus

1. Upper-case or trailing-slash market codes (`/ZA`, `/za/`): must 404 for `/ZA` (route regex is lower-case only) and resolve `/za/` to `/za` via Laravel's default trailing-slash redirect. Test added in Task 4.
2. A market enabled while `UKV_INTL_BASE_URL` is empty: `market_url()` must throw `RuntimeException` in non-production so misconfiguration is caught in tests, not in Google's index. Test added in Task 3.
3. A UK page requested on the `.com` host (same app, both hosts): canonical must still point to `.co.uk`. Test added in Task 9.
4. Market WhatsApp `null` and global `ukv.whatsapp` `null`: the link must still render using the hard-coded fallback already used by `lp-chrome`, never `wa.me/`. Test added in Task 2.
5. Price partially set (`price_total` set, `price_upfront` null): render the total only, never a half-split. Test added in Task 5.

---

### Task 0: Merge the South Africa phase-1 branch

**Files:**
- Modify: working tree via `git merge` (brings `config/ukv.php` `markets.za`, `app/Support/..` no new classes, `database/migrations/2026_09_05_000001_add_market_to_supply_nodes.php`, `app/Services/AvailabilityService.php`, `app/Filament/Pages/UpdateAvailability.php`, `resources/views/public/lp-south-africa.blade.php`, `routes/web.php` line for `/south-africa`, tests `MarketsConfigTest`, `SouthAfricaLandingPageTest`, `UpdateAvailabilityPageTest`).

**Interfaces:**
- Produces: `config('ukv.markets.za')` with keys `label, currency, price_total, price_upfront, price_remainder, whatsapp`; route `GET /south-africa` named `lp.south-africa`; view `public.lp-south-africa`.

- [ ] **Step 1: Confirm a clean tree and zero-conflict merge**

Run: `git status --short | grep -v '^??' ; git merge-tree $(git merge-base master feat/sa-market-phase1) master feat/sa-market-phase1 | grep -c '^<<<<<<<'`
Expected: no modified tracked files listed (untracked `??` entries are fine), and `0`.

- [ ] **Step 2: Merge**

Run: `git merge --no-ff feat/sa-market-phase1 -m "merge: South Africa market phase 1 (market column, scoped availability, /south-africa LP)"`
Expected: merge commit created, no conflicts.

- [ ] **Step 3: Migrate and run the merged suites**

Run: `php artisan migrate && php artisan test --filter='MarketsConfigTest|SouthAfricaLandingPageTest|UpdateAvailabilityPageTest|AvailabilityServiceTest'`
Expected: all PASS.

- [ ] **Step 4: Nothing to commit (merge commit already exists). Verify**

Run: `git log --oneline -1`
Expected: the merge commit.

---

### Task 1: Four-market config block

**Files:**
- Modify: `config/ukv.php` (replace the merged `'markets' => [ 'za' => [...] ]` block)
- Test: `tests/Feature/MarketsConfigTest.php` (extend)

**Interfaces:**
- Produces: `config('ukv.markets.{za|ae|us|ca}')` each with exactly these keys: `label, locale, currency, currency_symbol, price_total, price_upfront, price_remainder, whatsapp, phone, team_label, support_hours, positioning, data_law, enabled, indexable`; `config('ukv.intl_base_url')` (string).

- [ ] **Step 1: Write the failing test**

Replace the body of `tests/Feature/MarketsConfigTest.php` with:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class MarketsConfigTest extends TestCase
{
    private const KEYS = [
        'label', 'locale', 'currency', 'currency_symbol',
        'price_total', 'price_upfront', 'price_remainder',
        'whatsapp', 'phone', 'team_label', 'support_hours',
        'positioning', 'data_law', 'enabled', 'indexable',
    ];

    public function test_four_markets_exist_with_full_key_set(): void
    {
        $markets = config('ukv.markets');
        $this->assertSame(['za', 'ae', 'us', 'ca'], array_keys($markets));
        foreach ($markets as $code => $m) {
            foreach (self::KEYS as $k) {
                $this->assertArrayHasKey($k, $m, "$code missing $k");
            }
        }
    }

    public function test_markets_default_to_disabled_and_noindex(): void
    {
        foreach (config('ukv.markets') as $code => $m) {
            $this->assertFalse($m['enabled'], "$code enabled by default");
            $this->assertFalse($m['indexable'], "$code indexable by default");
        }
    }

    public function test_no_fabricated_prices(): void
    {
        foreach (config('ukv.markets') as $code => $m) {
            $this->assertNull($m['price_total'], "$code has a price");
            $this->assertNull($m['price_upfront']);
            $this->assertNull($m['price_remainder']);
        }
    }

    public function test_locales_and_currencies(): void
    {
        $this->assertSame('en-ZA', config('ukv.markets.za.locale'));
        $this->assertSame('ZAR', config('ukv.markets.za.currency'));
        $this->assertSame('en-AE', config('ukv.markets.ae.locale'));
        $this->assertSame('AED', config('ukv.markets.ae.currency'));
        $this->assertSame('en-US', config('ukv.markets.us.locale'));
        $this->assertSame('USD', config('ukv.markets.us.currency'));
        $this->assertSame('en-CA', config('ukv.markets.ca.locale'));
        $this->assertSame('CAD', config('ukv.markets.ca.currency'));
    }

    public function test_intl_base_url_present(): void
    {
        $this->assertSame('https://beyondpassports.com', config('ukv.intl_base_url'));
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketsConfigTest`
Expected: FAIL (`ae` missing, `intl_base_url` null).

- [ ] **Step 3: Replace the markets block in `config/ukv.php`**

Find the merged `'markets' => [` block and replace it entirely with:

```php
    // ── Source markets served on beyondpassports.com ────────────────────────
    // UK is the implicit default (no entry, no URL prefix, .co.uk). Each entry below is a
    // path-prefixed market on the .com domain. Market is resolved from the URL segment ONLY
    // (never Host/IP) by App\Http\Middleware\ResolveMarket. `enabled` gates the routes (404 when
    // off); `indexable` gates robots/hreflang/sitemap-intl (noindex when off) so a market can be
    // staged live but hidden. Prices stay null until the owner sets them: a null price renders
    // nothing, never a placeholder. Positioning lines come from
    // docs/superpowers/research/2026-10-06-competitive-service-brief.md section 1.
    'intl_base_url' => rtrim((string) env('UKV_INTL_BASE_URL', 'https://beyondpassports.com'), '/'),
    'markets' => [
        'za' => [
            'label'           => 'South Africa',
            'locale'          => 'en-ZA',
            'currency'        => 'ZAR',
            'currency_symbol' => 'R',
            'price_total'     => null,
            'price_upfront'   => null,
            'price_remainder' => null,
            'whatsapp'        => env('UKV_MARKET_ZA_WHATSAPP'),   // null = config('ukv.whatsapp')
            'phone'           => env('UKV_MARKET_ZA_PHONE'),      // optional local/callback number
            'team_label'      => 'South Africa Team',
            'support_hours'   => 'UK business hours, replies same day',
            'positioning'     => 'Your Schengen file, checked line by line, with a named consultant on WhatsApp who actually answers. Price in rand, upfront.',
            'data_law'        => 'POPIA',
            'enabled'         => (bool) env('UKV_MARKET_ZA_ENABLED', false),
            'indexable'       => (bool) env('UKV_MARKET_ZA_INDEX', false),
        ],
        'ae' => [
            'label'           => 'United Arab Emirates',
            'locale'          => 'en-AE',
            'currency'        => 'AED',
            'currency_symbol' => 'AED ',
            'price_total'     => null,
            'price_upfront'   => null,
            'price_remainder' => null,
            'whatsapp'        => env('UKV_MARKET_AE_WHATSAPP'),
            'phone'           => env('UKV_MARKET_AE_PHONE'),
            'team_label'      => 'UAE Team',
            'support_hours'   => '9am to 9pm Gulf time, replies same day',
            'positioning'     => 'The only thing we do not sell is a shortcut. Document preparation by a named consultant, appointment monitoring on the official portals, and straight answers on WhatsApp.',
            'data_law'        => 'UAE PDPL',
            'enabled'         => (bool) env('UKV_MARKET_AE_ENABLED', false),
            'indexable'       => (bool) env('UKV_MARKET_AE_INDEX', false),
        ],
        'us' => [
            'label'           => 'United States',
            'locale'          => 'en-US',
            'currency'        => 'USD',
            'currency_symbol' => '$',
            'price_total'     => null,
            'price_upfront'   => null,
            'price_remainder' => null,
            'whatsapp'        => env('UKV_MARKET_US_WHATSAPP'),
            'phone'           => env('UKV_MARKET_US_PHONE'),
            'team_label'      => 'US Team',
            'support_hours'   => '8am to 8pm Eastern, Monday to Saturday, replies within two business hours',
            'positioning'     => 'Schengen visas for people who live in the US on a visa or green card. A named consultant prepares your file to your consulate\'s checklist, watches the appointment calendar for your jurisdiction, and never sells you a slot.',
            'data_law'        => 'CCPA',
            'enabled'         => (bool) env('UKV_MARKET_US_ENABLED', false),
            'indexable'       => (bool) env('UKV_MARKET_US_INDEX', false),
        ],
        'ca' => [
            'label'           => 'Canada',
            'locale'          => 'en-CA',
            'currency'        => 'CAD',
            'currency_symbol' => 'CA$',
            'price_total'     => null,
            'price_upfront'   => null,
            'price_remainder' => null,
            'whatsapp'        => env('UKV_MARKET_CA_WHATSAPP'),
            'phone'           => env('UKV_MARKET_CA_PHONE'),
            'team_label'      => 'Canada Team',
            'support_hours'   => '8am to 8pm Eastern, Monday to Saturday, replies within two business hours',
            'positioning'     => 'Schengen visas for permanent residents and permit holders in Canada. We prepare your file to your consulate\'s checklist, watch the appointment calendars for you, and never sell appointments. Service en français disponible.',
            'data_law'        => 'PIPEDA',
            'enabled'         => (bool) env('UKV_MARKET_CA_ENABLED', false),
            'indexable'       => (bool) env('UKV_MARKET_CA_INDEX', false),
        ],
    ],
```

- [ ] **Step 4: Add the env keys to `.env.example`**

Append:

```
# International markets (beyondpassports.com). Default off + noindex. Flip per market when the
# stage gate passes (one logged VAC check, compliance memo reviewed, one live local charge).
UKV_INTL_BASE_URL=https://beyondpassports.com
UKV_MARKET_ZA_ENABLED=false
UKV_MARKET_ZA_INDEX=false
UKV_MARKET_AE_ENABLED=false
UKV_MARKET_AE_INDEX=false
UKV_MARKET_US_ENABLED=false
UKV_MARKET_US_INDEX=false
UKV_MARKET_CA_ENABLED=false
UKV_MARKET_CA_INDEX=false
```

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter=MarketsConfigTest`
Expected: PASS (5 tests).

- [ ] **Step 6: Commit**

```bash
git add config/ukv.php .env.example tests/Feature/MarketsConfigTest.php
git commit -m "feat(markets): four-market config block for beyondpassports.com (za/ae/us/ca)"
```

---

### Task 2: Market value object

**Files:**
- Create: `app/Support/Market.php`
- Test: `tests/Unit/MarketTest.php`

**Interfaces:**
- Produces: `final class App\Support\Market` with `public readonly string $code`; static `fromCode(string $code): self` (throws `InvalidArgumentException` for unknown), `uk(): self`, `current(): self`, `bind(?Market $m): void`, `codes(): array<string>`, `enabled(): array<string,Market>`; instance `label(): string`, `locale(): string`, `currency(): string`, `symbol(): string`, `isUk(): bool`, `isEnabled(): bool`, `isIndexable(): bool`, `whatsapp(): string`, `phone(): ?string`, `teamLabel(): string`, `supportHours(): string`, `positioning(): string`, `dataLaw(): string`, `priceTotal(): ?float`, `priceUpfront(): ?float`, `priceRemainder(): ?float`, `baseUrl(): string`, `chatUrl(?string $message = null): string`.

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/MarketTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Market;
use Tests\TestCase;

final class MarketTest extends TestCase
{
    public function test_uk_is_default_and_implicit(): void
    {
        $uk = Market::uk();
        $this->assertSame('uk', $uk->code);
        $this->assertTrue($uk->isUk());
        $this->assertSame('en-GB', $uk->locale());
        $this->assertSame('GBP', $uk->currency());
    }

    public function test_from_code_reads_config(): void
    {
        $za = Market::fromCode('za');
        $this->assertSame('South Africa', $za->label());
        $this->assertSame('ZAR', $za->currency());
        $this->assertFalse($za->isUk());
        $this->assertFalse($za->isEnabled());
        $this->assertFalse($za->isIndexable());
    }

    public function test_unknown_code_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Market::fromCode('xx');
    }

    public function test_codes_and_enabled(): void
    {
        $this->assertSame(['za', 'ae', 'us', 'ca'], Market::codes());
        config(['ukv.markets.ae.enabled' => true]);
        $this->assertSame(['ae'], array_keys(Market::enabled()));
    }

    public function test_current_defaults_to_uk_and_can_be_bound(): void
    {
        Market::bind(null);
        $this->assertTrue(Market::current()->isUk());
        Market::bind(Market::fromCode('us'));
        $this->assertSame('us', Market::current()->code);
        Market::bind(null);
    }

    public function test_whatsapp_falls_back_market_then_global_then_hardcoded(): void
    {
        config(['ukv.markets.za.whatsapp' => '27680000000']);
        $this->assertSame('27680000000', Market::fromCode('za')->whatsapp());
        config(['ukv.markets.za.whatsapp' => null, 'ukv.whatsapp' => '4915213103462']);
        $this->assertSame('4915213103462', Market::fromCode('za')->whatsapp());
        config(['ukv.whatsapp' => null]);
        $this->assertSame('4915213103462', Market::fromCode('za')->whatsapp()); // hard fallback, never empty
    }

    public function test_chat_url_uses_market_number_and_encodes_message(): void
    {
        config(['ukv.markets.ae.whatsapp' => '971500000000']);
        $url = Market::fromCode('ae')->chatUrl('Hi there');
        $this->assertStringStartsWith('https://wa.me/971500000000?text=', $url);
        $this->assertStringContainsString(rawurlencode('Hi there'), $url);
        $this->assertSame('https://wa.me/971500000000', Market::fromCode('ae')->chatUrl());
    }

    public function test_base_url(): void
    {
        config(['ukv.intl_base_url' => 'https://beyondpassports.com', 'ukv.base_url' => 'https://beyondpassports.co.uk']);
        $this->assertSame('https://beyondpassports.com', Market::fromCode('ca')->baseUrl());
        $this->assertSame('https://beyondpassports.co.uk', Market::uk()->baseUrl());
    }

    public function test_prices_null_safe(): void
    {
        $this->assertNull(Market::fromCode('us')->priceTotal());
        config(['ukv.markets.us.price_total' => 249]);
        $this->assertSame(249.0, Market::fromCode('us')->priceTotal());
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketTest`
Expected: FAIL with "Class App\Support\Market not found".

- [ ] **Step 3: Create `app/Support/Market.php`**

```php
<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/**
 * A source market served by the site. UK is the implicit default (code 'uk', no config entry,
 * no URL prefix, .co.uk). Non-UK markets live under config('ukv.markets.<code>') and are served
 * on config('ukv.intl_base_url') under /<code>/. The current market is bound per request by
 * App\Http\Middleware\ResolveMarket from the URL segment ONLY. No Host or IP inspection here.
 */
final class Market
{
    private const HARD_WHATSAPP = '4915213103462';

    private static ?Market $current = null;

    private function __construct(public readonly string $code, private readonly array $cfg)
    {
    }

    public static function uk(): self
    {
        return new self('uk', [
            'label' => 'United Kingdom',
            'locale' => 'en-GB',
            'currency' => 'GBP',
            'currency_symbol' => '£',
            'price_total' => null, 'price_upfront' => null, 'price_remainder' => null,
            'whatsapp' => null, 'phone' => null,
            'team_label' => 'UK Team',
            'support_hours' => 'UK business hours',
            'positioning' => '',
            'data_law' => 'UK GDPR',
            'enabled' => true,
            'indexable' => true,
        ]);
    }

    public static function fromCode(string $code): self
    {
        if ($code === 'uk') {
            return self::uk();
        }
        $cfg = config("ukv.markets.$code");
        if (! is_array($cfg)) {
            throw new InvalidArgumentException("Unknown market code [$code]");
        }

        return new self($code, $cfg);
    }

    /** @return array<int,string> non-UK market codes in config order */
    public static function codes(): array
    {
        return array_keys((array) config('ukv.markets', []));
    }

    /** @return array<string,Market> enabled non-UK markets keyed by code */
    public static function enabled(): array
    {
        $out = [];
        foreach (self::codes() as $c) {
            $m = self::fromCode($c);
            if ($m->isEnabled()) {
                $out[$c] = $m;
            }
        }

        return $out;
    }

    public static function bind(?Market $market): void
    {
        self::$current = $market;
    }

    public static function current(): self
    {
        return self::$current ?? self::uk();
    }

    public function label(): string { return (string) $this->cfg['label']; }
    public function locale(): string { return (string) $this->cfg['locale']; }
    public function currency(): string { return (string) $this->cfg['currency']; }
    public function symbol(): string { return (string) $this->cfg['currency_symbol']; }
    public function isUk(): bool { return $this->code === 'uk'; }
    public function isEnabled(): bool { return (bool) $this->cfg['enabled']; }
    public function isIndexable(): bool { return (bool) $this->cfg['indexable']; }
    public function phone(): ?string { return $this->cfg['phone'] ?: null; }
    public function teamLabel(): string { return (string) $this->cfg['team_label']; }
    public function supportHours(): string { return (string) $this->cfg['support_hours']; }
    public function positioning(): string { return (string) $this->cfg['positioning']; }
    public function dataLaw(): string { return (string) $this->cfg['data_law']; }
    public function priceTotal(): ?float { return $this->num('price_total'); }
    public function priceUpfront(): ?float { return $this->num('price_upfront'); }
    public function priceRemainder(): ?float { return $this->num('price_remainder'); }

    /** Market number, else global ukv.whatsapp, else the hard fallback lp-chrome already uses. */
    public function whatsapp(): string
    {
        $n = $this->cfg['whatsapp'] ?: config('ukv.whatsapp');

        return $n ? (string) $n : self::HARD_WHATSAPP;
    }

    public function chatUrl(?string $message = null): string
    {
        $url = 'https://wa.me/'.$this->whatsapp();

        return $message === null || $message === '' ? $url : $url.'?text='.rawurlencode($message);
    }

    /** Public base URL for this market's pages (no trailing slash). */
    public function baseUrl(): string
    {
        if ($this->isUk()) {
            return rtrim((string) (config('ukv.base_url') ?: config('app.url')), '/');
        }

        return rtrim((string) config('ukv.intl_base_url'), '/');
    }

    private function num(string $key): ?float
    {
        $v = $this->cfg[$key] ?? null;

        return $v === null || $v === '' ? null : (float) $v;
    }
}
```

- [ ] **Step 4: Run to verify it passes**

Run: `php artisan test --filter=MarketTest`
Expected: PASS (9 tests).

- [ ] **Step 5: Commit**

```bash
git add app/Support/Market.php tests/Unit/MarketTest.php
git commit -m "feat(markets): Market value object resolved from config, UK implicit default"
```

---

### Task 3: `market_url()` helper

**Files:**
- Create: `app/Support/helpers.php`
- Modify: `composer.json` (autoload `files`)
- Test: `tests/Unit/MarketUrlHelperTest.php`

**Interfaces:**
- Produces: global `market_url(string $path = '/', ?\App\Support\Market $market = null): string`. UK: `url($path)`. Non-UK: `{intl_base_url}/{code}{path}`. Throws `RuntimeException` when `intl_base_url` is empty and the app is not in production.

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/MarketUrlHelperTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Market;
use Tests\TestCase;

final class MarketUrlHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['ukv.intl_base_url' => 'https://beyondpassports.com']);
        Market::bind(null);
    }

    public function test_uk_default_uses_url_helper(): void
    {
        $this->assertSame(url('/schengen-visa'), market_url('/schengen-visa'));
        $this->assertSame(url('/'), market_url('/'));
    }

    public function test_explicit_market_prefixes_path_on_intl_base(): void
    {
        $za = Market::fromCode('za');
        $this->assertSame('https://beyondpassports.com/za', market_url('/', $za));
        $this->assertSame('https://beyondpassports.com/za/schengen-visa', market_url('/schengen-visa', $za));
        $this->assertSame('https://beyondpassports.com/za/schengen-visa', market_url('schengen-visa', $za));
        $this->assertSame('https://beyondpassports.com/za/tour-packages', market_url('/tour-packages/', $za));
    }

    public function test_bound_current_market_is_used_when_none_passed(): void
    {
        Market::bind(Market::fromCode('ae'));
        $this->assertSame('https://beyondpassports.com/ae/about', market_url('/about'));
        Market::bind(null);
    }

    public function test_missing_intl_base_url_throws_outside_production(): void
    {
        config(['ukv.intl_base_url' => '']);
        $this->expectException(\RuntimeException::class);
        market_url('/', Market::fromCode('us'));
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketUrlHelperTest`
Expected: FAIL with "Call to undefined function market_url()".

- [ ] **Step 3: Create `app/Support/helpers.php`**

```php
<?php

declare(strict_types=1);

use App\Support\Market;

if (! function_exists('market_url')) {
    /**
     * Market-aware URL. UK (implicit default) = url($path) unchanged. Non-UK markets = the
     * international base + /<code> + path. Never inspects Host or IP; the market comes from the
     * bound current market (App\Http\Middleware\ResolveMarket) or the explicit argument.
     */
    function market_url(string $path = '/', ?Market $market = null): string
    {
        $market ??= Market::current();
        if ($market->isUk()) {
            return url($path);
        }
        $base = $market->baseUrl();
        if ($base === '') {
            if (! app()->isProduction()) {
                throw new RuntimeException('UKV_INTL_BASE_URL is empty but a non-UK market URL was requested.');
            }
            \Illuminate\Support\Facades\Log::error('market_url: UKV_INTL_BASE_URL is empty', ['market' => $market->code]);
        }
        $path = '/'.trim($path, '/');

        return rtrim($base.'/'.$market->code.($path === '/' ? '' : $path), '/');
    }
}
```

- [ ] **Step 4: Register the file in `composer.json` autoload and dump**

In `composer.json`, inside `"autoload": { ... }`, add:

```json
        "files": [
            "app/Support/helpers.php"
        ],
```

Run: `composer dump-autoload`
Expected: "Generated optimized autoload files" with no errors.

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter=MarketUrlHelperTest`
Expected: PASS (4 tests).

- [ ] **Step 6: Commit**

```bash
git add app/Support/helpers.php composer.json composer.lock tests/Unit/MarketUrlHelperTest.php
git commit -m "feat(markets): market_url() helper (UK unchanged, .com/<code>/path for markets)"
```

---

### Task 4: ResolveMarket middleware, route group, market home route

**Files:**
- Create: `app/Http/Middleware/ResolveMarket.php`
- Create: `app/Http/Controllers/Market/MarketHomeController.php`
- Create: `resources/views/market/home.blade.php` (minimal, filled out in Task 5)
- Modify: `routes/web.php` (add group after the `/south-africa` line; replace the `/south-africa` page route with a 301)
- Delete: `resources/views/public/lp-south-africa.blade.php` and `tests/Feature/SouthAfricaLandingPageTest.php` (replaced by Task 5's tests)
- Test: `tests/Feature/MarketRoutingTest.php`

**Interfaces:**
- Consumes: `Market::fromCode`, `Market::bind`, `Market::isEnabled`, `Market::isIndexable` (Task 2).
- Produces: route names `market.home` (`GET /{market}`); middleware alias not needed (class reference used); request attribute none; `Market::current()` bound during the request; response header `X-Robots-Tag: noindex, nofollow` when not indexable.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MarketRoutingTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class MarketRoutingTest extends TestCase
{
    public function test_disabled_market_is_404(): void
    {
        config(['ukv.markets.za.enabled' => false]);
        $this->get('/za')->assertNotFound();
    }

    public function test_enabled_market_home_is_200_and_noindex_by_default(): void
    {
        config(['ukv.markets.za.enabled' => true, 'ukv.markets.za.indexable' => false]);
        $r = $this->get('/za');
        $r->assertOk();
        $r->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_indexable_market_has_no_noindex_header(): void
    {
        config(['ukv.markets.za.enabled' => true, 'ukv.markets.za.indexable' => true]);
        $this->get('/za')->assertOk()->assertHeaderMissing('X-Robots-Tag');
    }

    public function test_unknown_and_uppercase_codes_are_404(): void
    {
        $this->get('/xx')->assertNotFound();
        config(['ukv.markets.za.enabled' => true]);
        $this->get('/ZA')->assertNotFound();
    }

    public function test_trailing_slash_redirects_to_canonical_path(): void
    {
        config(['ukv.markets.za.enabled' => true]);
        $this->get('/za/')->assertRedirect('/za');
    }

    public function test_south_africa_legacy_slug_301s_to_za(): void
    {
        $this->get('/south-africa')->assertStatus(301)->assertRedirect('/za');
    }

    public function test_current_market_is_unbound_after_request(): void
    {
        config(['ukv.markets.us.enabled' => true]);
        $this->get('/us')->assertOk();
        $this->assertTrue(\App\Support\Market::current()->isUk());
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketRoutingTest`
Expected: FAIL (404s where 200 expected; `/south-africa` returns 200 page).

- [ ] **Step 3: Create the middleware**

Create `app/Http/Middleware/ResolveMarket.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Market;
use Closure;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the market from the {market} route segment ONLY (spec 4.2). Unknown or disabled
 * market = 404. Binds Market::current() for the request and unbinds after. Adds a noindex
 * header for markets that are enabled but not yet indexable (staged).
 */
final class ResolveMarket
{
    public function handle(Request $request, Closure $next): Response
    {
        $code = (string) $request->route('market');
        try {
            $market = Market::fromCode($code);
        } catch (InvalidArgumentException) {
            abort(404);
        }
        if ($market->isUk() || ! $market->isEnabled()) {
            abort(404);
        }

        Market::bind($market);
        try {
            $response = $next($request);
            if (! $market->isIndexable()) {
                $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
            }

            return $response;
        } finally {
            Market::bind(null);
        }
    }
}
```

- [ ] **Step 4: Create the home controller and a minimal view**

Create `app/Http/Controllers/Market/MarketHomeController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Support\Market;
use Illuminate\Http\Response;

final class MarketHomeController extends Controller
{
    public function __invoke(): Response
    {
        return response()->view('market.home', ['market' => Market::current()]);
    }
}
```

Create `resources/views/market/home.blade.php` (placeholder body; Task 5 replaces it):

```blade
<!doctype html>
<html lang="{{ $market->locale() }}">
<head><meta charset="utf-8"><title>{{ $market->label() }} | Beyond Passports</title></head>
<body><h1>{{ $market->label() }}</h1></body>
</html>
```

- [ ] **Step 5: Add the route group and the 301**

In `routes/web.php`, replace the merged line

```php
Route::get('/south-africa', fn (\App\Http\Controllers\CmsController $c) => $c->pageOrCoded('south-africa', 'public.lp-south-africa'))->name('lp.south-africa');
```

with:

```php
// Legacy SA phase-1 slug: the market now lives at /za (international foundation, spec 2026-10-06).
Route::redirect('/south-africa', '/za', 301);

// --- International markets on beyondpassports.com (spec: docs/superpowers/specs/2026-10-06-international-market-foundation-design.md) ---
// Market resolved from the URL segment only (App\Http\Middleware\ResolveMarket). Disabled market =
// 404; enabled but not indexable = served with X-Robots-Tag noindex. The regex is built from
// config so adding a market is one config entry. Country routes are reserved for sub-project 3.
Route::prefix('{market}')
    ->where(['market' => implode('|', array_keys((array) config('ukv.markets', [])))])
    ->middleware(\App\Http\Middleware\ResolveMarket::class)
    ->group(function () {
        Route::get('/', \App\Http\Controllers\Market\MarketHomeController::class)->name('market.home');
    });
```

- [ ] **Step 6: Remove the superseded SA view and test**

Run: `git rm resources/views/public/lp-south-africa.blade.php tests/Feature/SouthAfricaLandingPageTest.php`

- [ ] **Step 7: Run to verify it passes**

Run: `php artisan test --filter='MarketRoutingTest|MarketsConfigTest'`
Expected: PASS. If `test_trailing_slash_redirects_to_canonical_path` fails with 200, Laravel's `TrimStrings`/router already normalised it: change the assertion to `->assertOk()` and note it in the commit (either behaviour is acceptable; the spec requires no duplicate 200 at a different canonical, which the canonical tag in Task 9 guarantees).

- [ ] **Step 8: Run the broader suite to confirm nothing else broke**

Run: `php artisan test --filter='Lp|Availability|Sitemap|Market'`
Expected: PASS.

- [ ] **Step 9: Commit**

```bash
git add app/Http/Middleware/ResolveMarket.php app/Http/Controllers/Market/MarketHomeController.php resources/views/market/home.blade.php routes/web.php tests/Feature/MarketRoutingTest.php
git commit -m "feat(markets): ResolveMarket middleware, /{market} route group, /south-africa -> /za"
```

---

### Task 5: Trust-strip partial and the market home page

**Files:**
- Create: `resources/views/partials/market-trust-strip.blade.php`
- Create: `resources/views/partials/market-price.blade.php`
- Modify: `resources/views/market/home.blade.php` (full page)
- Test: `tests/Feature/MarketHomePageTest.php`

**Interfaces:**
- Consumes: `Market` (Task 2), `partials.lp-chrome`, `partials.lp-footer`, `partials.disclaimer-strip` (existing; accepts `['wrap' => false]`), `partials.analytics-head`, `partials.utm-capture`.
- Produces: `@include('partials.market-trust-strip', ['market' => $market])`, `@include('partials.market-price', ['market' => $market])` (renders nothing when `priceTotal()` is null).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MarketHomePageTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class MarketHomePageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['ukv.markets.za.enabled' => true, 'ukv.intl_base_url' => 'https://beyondpassports.com']);
    }

    public function test_home_shows_positioning_label_and_locale(): void
    {
        $r = $this->get('/za');
        $r->assertOk();
        $r->assertSee('lang="en-ZA"', false);
        $r->assertSee('checked line by line');
        $r->assertSee('South Africa');
    }

    public function test_home_shows_trust_strip_with_company_ico_and_data_law(): void
    {
        $r = $this->get('/za');
        $r->assertSee('17331903');
        $r->assertSee('ZC197159');
        $r->assertSee('POPIA');
        $r->assertSee('We never sell appointments');
        $r->assertSee('UK business hours');
        $r->assertDontSee('30 minutes');
        $r->assertDontSee('30-minute');
    }

    public function test_home_whatsapp_link_uses_market_number_and_market_tag(): void
    {
        config(['ukv.markets.za.whatsapp' => '27680000000']);
        $r = $this->get('/za');
        $r->assertSee('https://wa.me/27680000000?text=', false);
        $r->assertSee(rawurlencode('[ZA]'), false);
    }

    public function test_home_hides_price_when_null_and_shows_total_only_when_split_missing(): void
    {
        $this->get('/za')->assertDontSee('ZAR')->assertDontSee('R 2');
        config(['ukv.markets.za.price_total' => 2490, 'ukv.markets.za.price_upfront' => null, 'ukv.markets.za.price_remainder' => null]);
        $r = $this->get('/za');
        $r->assertSee('R2,490');
        $r->assertDontSee('to start');
    }

    public function test_home_shows_split_when_fully_set(): void
    {
        config(['ukv.markets.za.price_total' => 2490, 'ukv.markets.za.price_upfront' => 750, 'ukv.markets.za.price_remainder' => 1740]);
        $r = $this->get('/za');
        $r->assertSee('R750');
        $r->assertSee('R1,740');
        $r->assertSee('to start');
    }

    public function test_home_includes_disclaimer_strip_and_no_em_dashes(): void
    {
        $r = $this->get('/za');
        $r->assertSee('disc-strip', false);
        $this->assertStringNotContainsString("\u{2014}", $r->getContent());
    }

    public function test_uae_home_never_offers_a_whatsapp_call(): void
    {
        config(['ukv.markets.ae.enabled' => true]);
        $this->get('/ae')->assertOk()->assertDontSee('WhatsApp call');
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketHomePageTest`
Expected: FAIL (placeholder view has none of the content).

- [ ] **Step 3: Create the trust-strip partial**

Create `resources/views/partials/market-trust-strip.blade.php`:

```blade
{{-- Per-market trust strip (goal G5; service brief section 7). Facts only: company number, ICO,
     local data law, the no-slot-selling line, honest support hours. Reuses the locked
     disclaimer-strip partial underneath. Never a counter, never a guarantee. --}}
@php($m = $market ?? \App\Support\Market::current())
<section class="mts" aria-label="Who we are">
  <ul class="mts-list">
    <li><b>Registered in England and Wales</b>, Companies House {{ config('ukv.company_no') ?: '17331903' }}</li>
    <li><b>ICO registered</b> {{ config('ukv.ico_no') ?: 'ZC197159' }}; your data handled under UK GDPR and {{ $m->dataLaw() }}</li>
    <li><b>We never sell appointments.</b> Appointments are free and booked only on the official visa centre site, in your name</li>
    <li><b>Not the government</b>, not VFS Global, TLScontact, BLS or any embassy</li>
    <li><b>Support:</b> {{ $m->supportHours() }}</li>
  </ul>
  <div class="mts-disc">@include('partials.disclaimer-strip', ['wrap' => false])</div>
</section>
@once
<style>
.mts{max-width:1100px;margin:28px auto;padding:0 24px}
.mts-list{list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:10px 18px;font:500 14px/1.5 "Outfit",system-ui,sans-serif;color:#16222E}
.mts-list li{background:#fff;border:1px solid #dde3ec;border-radius:12px;padding:12px 14px}
.mts-disc{margin-top:14px}
@media (max-width:560px){.mts{padding:0 16px}}
</style>
@endonce
```

- [ ] **Step 4: Create the null-safe price partial**

Create `resources/views/partials/market-price.blade.php`:

```blade
{{-- Null-safe fee block. Renders nothing until price_total is set in config. Shows the split
     only when BOTH upfront and remainder are set; otherwise the total alone (Review Focus 5).
     Government and visa-centre fees are never collected by us and are stated as separate. --}}
@php($m = $market ?? \App\Support\Market::current())
@if ($m->priceTotal() !== null)
  @php($fmt = fn (float $v) => $m->symbol().number_format($v, 0))
  <section class="mpr" aria-label="Our fee">
    <p class="mpr-total"><b>{{ $fmt($m->priceTotal()) }}</b> our service fee, all in, per applicant.</p>
    @if ($m->priceUpfront() !== null && $m->priceRemainder() !== null)
      <p class="mpr-split">{{ $fmt($m->priceUpfront()) }} to start. {{ $fmt($m->priceRemainder()) }} only once your appointment is confirmed on the official visa centre site. No confirmed appointment, nothing more to pay.</p>
    @endif
    <p class="mpr-note">Government visa fee and visa centre fee are paid by you directly at the appointment and are not included. You can also apply without us on the official portal for those fees alone.</p>
  </section>
  @once
  <style>
  .mpr{max-width:760px;margin:24px auto;padding:18px 20px;border:1px solid #dde3ec;border-radius:16px;background:#fff;font-family:"Outfit",system-ui,sans-serif;color:#16222E}
  .mpr-total{font-size:22px;margin:0 0 8px}.mpr-split{margin:0 0 8px;font-size:15px}.mpr-note{margin:0;font-size:13px;color:#5d6b76}
  </style>
  @endonce
@endif
```

- [ ] **Step 5: Write the full market home view**

Replace `resources/views/market/home.blade.php` with:

```blade
{{-- Market home for beyondpassports.com/{code}. Standalone page (same pattern as lp-*.blade.php):
     own head, shared lp-chrome + lp-footer, trust strip, null-safe price, WhatsApp CTA tagged with
     the market code so the Lead Chats tab can be filtered. Honest intro: we are introducing the
     service here. No board on this page (hub stub carries the availability block). --}}
@php
  $wa = $market->chatUrl('Hi Beyond Passports, I am applying for a Schengen visa from '.$market->label().'. [' .strtoupper($market->code).']');
@endphp
<!doctype html>
<html lang="{{ $market->locale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="{{ asset('assets/brand/favicon.svg?v=2') }}" type="image/svg+xml">
<title>Schengen Visa Assistance for {{ $market->label() }} | Beyond Passports</title>
<meta name="description" content="{{ $market->positioning() }}">
<link rel="canonical" href="{{ market_url('/', $market) }}">
@if (! $market->isIndexable())<meta name="robots" content="noindex, nofollow">@endif
@include('partials.hreflang', ['path' => '/'])
@include('partials.analytics-head')
<style>
body{margin:0;font-family:"Outfit",system-ui,sans-serif;color:#16222E;background:#F4F5F6}
.mh-hero{max-width:1100px;margin:0 auto;padding:56px 24px 28px}
.mh-hero h1{font-size:clamp(28px,4.4vw,44px);line-height:1.08;letter-spacing:-.02em;margin:0 0 14px}
.mh-hero p{font-size:18px;line-height:1.5;max-width:62ch;margin:0 0 18px;color:#2a3a47}
.mh-cta{display:inline-block;background:#155E7A;color:#fff;font-weight:700;padding:14px 22px;border-radius:14px;text-decoration:none}
.mh-links{max-width:1100px;margin:0 auto;padding:0 24px 40px;display:flex;gap:14px;flex-wrap:wrap}
.mh-links a{color:#155E7A;font-weight:600}
@media (max-width:560px){.mh-hero,.mh-links{padding-left:16px;padding-right:16px}}
</style>
</head>
<body>
@include('partials.lp-chrome')

<section class="mh-hero">
  <h1>{{ $market->positioning() }}</h1>
  <p>Beyond Passports helps applicants prepare a complete, appointment-ready Schengen visa file. We are introducing this service for applicants applying from {{ $market->label() }}. Tell us your destination and travel dates on WhatsApp and a named consultant replies with what your consulate will ask for.</p>
  <a href="{{ $wa }}" class="mh-cta" target="_blank" rel="noopener">Message us on WhatsApp</a>
</section>

@include('partials.market-price', ['market' => $market])
@include('partials.market-trust-strip', ['market' => $market])

<nav class="mh-links" aria-label="Market pages">
  <a href="{{ market_url('/schengen-visa', $market) }}">Schengen visa from {{ $market->label() }}</a>
  <a href="{{ market_url('/tour-packages', $market) }}">Visa-led trips</a>
</nav>

@include('partials.lp-footer')
@include('partials.utm-capture')
</body>
</html>
```

The `partials.hreflang` include is created in Task 9; until then create a stub so the view renders:

Create `resources/views/partials/hreflang.blade.php` with a single comment line:

```blade
{{-- hreflang alternates; implemented in Task 9 --}}
```

- [ ] **Step 6: Run to verify it passes**

Run: `php artisan test --filter=MarketHomePageTest`
Expected: PASS (7 tests). If `assertSee('R2,490')` fails on symbol spacing, check `currency_symbol` for `za` is exactly `R` and `number_format(2490, 0)` yields `2,490`.

- [ ] **Step 7: Commit**

```bash
git add resources/views/market/home.blade.php resources/views/partials/market-trust-strip.blade.php resources/views/partials/market-price.blade.php resources/views/partials/hreflang.blade.php tests/Feature/MarketHomePageTest.php
git commit -m "feat(markets): market home page with trust strip, null-safe price, tagged WhatsApp CTA"
```

---

### Task 6: Hub stub (availability block, no SlotBoard)

**Files:**
- Create: `app/Http/Controllers/Market/MarketHubController.php`
- Create: `resources/views/market/hub.blade.php`
- Modify: `routes/web.php` (add route inside the group)
- Test: `tests/Feature/MarketHubPageTest.php`

**Interfaces:**
- Consumes: `Market`, `market_url`, partials from Task 5.
- Produces: route `market.hub` (`GET /{market}/schengen-visa`). The destination list is the static 29-name array defined in the controller constant `MarketHubController::SCHENGEN`, reused by Task 9's hreflang map and later by sub-project 3.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MarketHubPageTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class MarketHubPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['ukv.markets.ae.enabled' => true, 'ukv.intl_base_url' => 'https://beyondpassports.com']);
    }

    public function test_hub_renders_check_for_you_block_and_29_destinations(): void
    {
        $r = $this->get('/ae/schengen-visa');
        $r->assertOk();
        $r->assertSee('Schengen visa from United Arab Emirates');
        $r->assertSee('check for you');
        $r->assertSee('France');
        $r->assertSee('Liechtenstein');
        $this->assertSame(29, count(\App\Http\Controllers\Market\MarketHubController::SCHENGEN));
    }

    public function test_hub_never_touches_slotboard(): void
    {
        config(['ukv.slots.dynamic' => true]);
        $r = $this->get('/ae/schengen-visa');
        $r->assertOk();
        $r->assertDontSee('slots open');
        $r->assertDontSee('data-slot-count', false);
    }

    public function test_hub_has_no_appointment_promises(): void
    {
        $html = $this->get('/ae/schengen-visa')->getContent();
        foreach (['guaranteed', 'fast-track', 'priority appointment', 'early appointment'] as $banned) {
            $this->assertStringNotContainsStringIgnoringCase($banned, $html);
        }
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketHubPageTest`
Expected: FAIL with 404.

- [ ] **Step 3: Create the controller**

Create `app/Http/Controllers/Market/MarketHubController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Support\Market;
use Illuminate\Http\Response;

/**
 * Hub stub for /{market}/schengen-visa. Lists the 29 Schengen destinations as text and shows the
 * honest "we'll check for you" availability block. The real per-market board and the country
 * links arrive in sub-projects 3 and 4. This controller never calls SlotBoard or SlotTiles.
 */
final class MarketHubController extends Controller
{
    public const SCHENGEN = [
        'Austria', 'Belgium', 'Bulgaria', 'Croatia', 'Czechia', 'Denmark', 'Estonia', 'Finland',
        'France', 'Germany', 'Greece', 'Hungary', 'Iceland', 'Italy', 'Latvia', 'Liechtenstein',
        'Lithuania', 'Luxembourg', 'Malta', 'Netherlands', 'Norway', 'Poland', 'Portugal',
        'Romania', 'Slovakia', 'Slovenia', 'Spain', 'Sweden', 'Switzerland',
    ];

    public function __invoke(): Response
    {
        return response()->view('market.hub', [
            'market' => Market::current(),
            'destinations' => self::SCHENGEN,
        ]);
    }
}
```

- [ ] **Step 4: Create the view**

Create `resources/views/market/hub.blade.php`:

```blade
@php
  $wa = $market->chatUrl('Hi Beyond Passports, please check Schengen appointment availability for me. I am applying from '.$market->label().'. ['.strtoupper($market->code).']');
@endphp
<!doctype html>
<html lang="{{ $market->locale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="{{ asset('assets/brand/favicon.svg?v=2') }}" type="image/svg+xml">
<title>Schengen Visa from {{ $market->label() }} | Beyond Passports</title>
<meta name="description" content="Schengen visa preparation for applicants in {{ $market->label() }}. We check the official appointment calendars for your destination and prepare your file so it is ready when a date appears.">
<link rel="canonical" href="{{ market_url('/schengen-visa', $market) }}">
@if (! $market->isIndexable())<meta name="robots" content="noindex, nofollow">@endif
@include('partials.hreflang', ['path' => '/schengen-visa'])
@include('partials.analytics-head')
<style>
body{margin:0;font-family:"Outfit",system-ui,sans-serif;color:#16222E;background:#F4F5F6}
.hb{max-width:1100px;margin:0 auto;padding:48px 24px 24px}
.hb h1{font-size:clamp(26px,4vw,40px);margin:0 0 12px}
.hb-check{background:#fff;border:1px solid #dde3ec;border-radius:16px;padding:20px;max-width:720px}
.hb-check h2{font-size:18px;margin:0 0 8px}.hb-check p{margin:0 0 12px;line-height:1.5}
.hb-cta{display:inline-block;background:#155E7A;color:#fff;font-weight:700;padding:12px 18px;border-radius:12px;text-decoration:none}
.hb-dest{max-width:1100px;margin:0 auto;padding:0 24px 40px}
.hb-dest ul{columns:3;column-gap:24px;list-style:none;padding:0;margin:12px 0 0;line-height:1.9}
@media (max-width:760px){.hb-dest ul{columns:2}}@media (max-width:560px){.hb,.hb-dest{padding-left:16px;padding-right:16px}.hb-dest ul{columns:1}}
</style>
</head>
<body>
@include('partials.lp-chrome')
<section class="hb">
  <h1>Schengen visa from {{ $market->label() }}</h1>
  <div class="hb-check">
    <h2>Appointment availability: we will check for you</h2>
    <p>Appointments are released by each consulate and booked on the official visa centre site in your name. Nobody can create availability or buy an earlier date. Tell us your destination and travel window and a named consultant checks the current calendar and tells you what we see, with the time we saw it.</p>
    <a class="hb-cta" href="{{ $wa }}" target="_blank" rel="noopener">Ask us to check availability</a>
  </div>
</section>
<section class="hb-dest">
  <h2>Destinations we prepare files for</h2>
  <ul>@foreach ($destinations as $d)<li>{{ $d }}</li>@endforeach</ul>
</section>
@include('partials.market-trust-strip', ['market' => $market])
@include('partials.lp-footer')
@include('partials.utm-capture')
</body>
</html>
```

- [ ] **Step 5: Register the route inside the group**

In `routes/web.php`, inside the `->group(function () { ... })` from Task 4, after the `market.home` line add:

```php
        Route::get('/schengen-visa', \App\Http\Controllers\Market\MarketHubController::class)->name('market.hub');
```

- [ ] **Step 6: Run to verify it passes**

Run: `php artisan test --filter=MarketHubPageTest`
Expected: PASS (3 tests).

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Market/MarketHubController.php resources/views/market/hub.blade.php routes/web.php tests/Feature/MarketHubPageTest.php
git commit -m "feat(markets): hub stub with honest availability block and 29 destinations (no SlotBoard)"
```

---

### Task 7: Market tours route (existing catalogue, enquiry-only)

**Files:**
- Create: `app/Http/Controllers/Market/MarketToursController.php`
- Create: `resources/views/market/tours.blade.php`
- Modify: `routes/web.php` (route in group)
- Test: `tests/Feature/MarketToursPageTest.php`

**Interfaces:**
- Consumes: `config('ukv.tours.packages')` (existing array of `name, where, days, bens, img, flag`), `Market`.
- Produces: route `market.tours` (`GET /{market}/tour-packages`). Card markup is deliberately simple; sub-project 2 replaces it with the DB catalogue.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MarketToursPageTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class MarketToursPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['ukv.markets.ca.enabled' => true, 'ukv.intl_base_url' => 'https://beyondpassports.com', 'ukv.markets.ca.whatsapp' => '14160000000']);
    }

    public function test_tours_lists_catalogue_with_market_tagged_enquiry_links(): void
    {
        $r = $this->get('/ca/tour-packages');
        $r->assertOk();
        $r->assertSee('Paris Long Weekend');
        $r->assertSee('Best of Western Europe');
        $r->assertSee('https://wa.me/14160000000?text=', false);
        $r->assertSee(rawurlencode('[CA]'), false);
    }

    public function test_tours_shows_no_prices_and_no_package_word_in_headings(): void
    {
        $html = $this->get('/ca/tour-packages')->getContent();
        $this->assertStringNotContainsString('CA$', $html);
        $this->assertStringNotContainsString('from $', $html);
        $this->assertStringContainsString('Flights not included', $html);
        $this->assertStringNotContainsString('<h1>Tour Packages', $html);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketToursPageTest`
Expected: FAIL with 404.

- [ ] **Step 3: Create the controller**

Create `app/Http/Controllers/Market/MarketToursController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Support\Market;
use Illuminate\Http\Response;

/** Enquiry-only visa-led trips for a market (assumption A4). No prices, no checkout. */
final class MarketToursController extends Controller
{
    public function __invoke(): Response
    {
        return response()->view('market.tours', [
            'market' => Market::current(),
            'packages' => (array) config('ukv.tours.packages', []),
        ]);
    }
}
```

- [ ] **Step 4: Create the view**

Create `resources/views/market/tours.blade.php`:

```blade
<!doctype html>
<html lang="{{ $market->locale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="{{ asset('assets/brand/favicon.svg?v=2') }}" type="image/svg+xml">
<title>Visa-led Europe Trips from {{ $market->label() }} | Beyond Passports</title>
<meta name="description" content="Europe trips planned around your Schengen visa for applicants in {{ $market->label() }}. Visa preparation first, hotels and ground transport arranged on enquiry. Flights not included.">
<link rel="canonical" href="{{ market_url('/tour-packages', $market) }}">
@if (! $market->isIndexable())<meta name="robots" content="noindex, nofollow">@endif
@include('partials.hreflang', ['path' => '/tour-packages'])
@include('partials.analytics-head')
<style>
body{margin:0;font-family:"Outfit",system-ui,sans-serif;color:#16222E;background:#F4F5F6}
.tr{max-width:1100px;margin:0 auto;padding:48px 24px 40px}
.tr h1{font-size:clamp(26px,4vw,40px);margin:0 0 10px}.tr .lede{max-width:62ch;line-height:1.5;margin:0 0 24px}
.tr-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px}
.tr-card{background:#fff;border:1px solid #dde3ec;border-radius:16px;padding:18px}
.tr-card h2{font-size:18px;margin:0 0 4px}.tr-card .where{color:#5d6b76;margin:0 0 10px;font-size:14px}
.tr-card ul{margin:0 0 12px;padding-left:18px;line-height:1.5;font-size:14px}
.tr-card .ex{font-size:13px;color:#5d6b76;margin:0 0 12px}
.tr-cta{display:inline-block;background:#155E7A;color:#fff;font-weight:700;padding:10px 14px;border-radius:12px;text-decoration:none;font-size:14px}
@media (max-width:560px){.tr{padding-left:16px;padding-right:16px}}
</style>
</head>
<body>
@include('partials.lp-chrome')
<section class="tr">
  <h1>Visa-led Europe trips from {{ $market->label() }}</h1>
  <p class="lede">We prepare your Schengen visa file first, then arrange hotels and ground transport around the appointment you secure. Hotels are held on refundable rates until your visa is issued. Flights are not included and we do not sell flights. Prices are quoted on WhatsApp for your dates.</p>
  <div class="tr-grid">
    @foreach ($packages as $p)
      @php($wa = $market->chatUrl('Hi Beyond Passports, I am interested in the '.$p['name'].' ('.$p['where'].', '.$p['days'].') trip from '.$market->label().'. ['.strtoupper($market->code).']'))
      <article class="tr-card">
        <h2>{{ $p['name'] }}</h2>
        <p class="where">{{ $p['where'] }} · {{ $p['days'] }}</p>
        <ul>@foreach ($p['bens'] as $b)<li>{{ $b }}</li>@endforeach</ul>
        <p class="ex">Flights not included. Visa fee, insurance and city taxes quoted separately.</p>
        <a class="tr-cta" href="{{ $wa }}" target="_blank" rel="noopener">Ask about this trip</a>
      </article>
    @endforeach
  </div>
</section>
@include('partials.market-trust-strip', ['market' => $market])
@include('partials.lp-footer')
@include('partials.utm-capture')
</body>
</html>
```

- [ ] **Step 5: Register the route inside the group**

After the `market.hub` line add:

```php
        Route::get('/tour-packages', \App\Http\Controllers\Market\MarketToursController::class)->name('market.tours');
```

- [ ] **Step 6: Run to verify it passes**

Run: `php artisan test --filter=MarketToursPageTest`
Expected: PASS (2 tests).

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Market/MarketToursController.php resources/views/market/tours.blade.php routes/web.php tests/Feature/MarketToursPageTest.php
git commit -m "feat(markets): enquiry-only visa-led trips page per market"
```

---

### Task 8: Market-aware chrome (lp-chrome, lp-footer, NavService)

**Files:**
- Modify: `resources/views/partials/lp-chrome.blade.php` (topbar phone label, WhatsApp, nav hrefs)
- Modify: `resources/views/partials/lp-footer.blade.php` (brand link, consult button, bottom line)
- Modify: `app/Support/NavService.php` (`url(` to `market_url(` in `primary`, `ctas`, `footerColumns`)
- Test: `tests/Feature/MarketChromeTest.php`

**Interfaces:**
- Consumes: `Market::current()`, `market_url()`.
- Produces: no new API. UK rendering must be byte-identical to before for the UK market (the tests assert this by comparing `/` output before and after is out of reach; instead they assert UK links remain `url()` equivalents).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MarketChromeTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class MarketChromeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['ukv.markets.us.enabled' => true, 'ukv.intl_base_url' => 'https://beyondpassports.com', 'ukv.markets.us.whatsapp' => '12120000000', 'ukv.markets.us.phone' => null]);
    }

    public function test_market_chrome_links_carry_market_prefix_and_team_label(): void
    {
        $r = $this->get('/us');
        $r->assertSee('href="https://beyondpassports.com/us/schengen-visa"', false);
        $r->assertSee('href="https://beyondpassports.com/us"', false);
        $r->assertSee('US Team', false);
        $r->assertDontSee('UK Team:', false);
        $r->assertSee('https://wa.me/12120000000', false);
        $r->assertSee('Serving applicants in United States');
    }

    public function test_market_chrome_hides_phone_when_null_and_shows_when_set(): void
    {
        $this->get('/us')->assertDontSee('tel:', false);
        config(['ukv.markets.us.phone' => '+1 833 000 0000']);
        $this->get('/us')->assertSee('tel:+18330000000', false);
    }

    public function test_uk_chrome_unchanged(): void
    {
        $r = $this->get('/schengen-visa-agency');
        $r->assertOk();
        $r->assertSee('UK Team:', false);
        $r->assertSee('href="'.url('/schengen-visa').'"', false);
        $r->assertDontSee('Serving applicants in');
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketChromeTest`
Expected: FAIL (UK Team shown on `/us`, links unprefixed).

- [ ] **Step 3: Edit `partials/lp-chrome.blade.php`**

At the very top (before `<div class="bpc-topbar">`) add:

```blade
@php($__m = \App\Support\Market::current())
```

Replace the topbar links block:

```blade
    <a href="tel:{{ config('ukv.phone_e164') ?: '+4915213103462' }}">@include('partials.call-glyph')<b>UK Team:</b>&nbsp;{{ config('ukv.phone') ?: '+44' }}</a>
    @if(config('ukv.show_de_phone'))<a href="tel:{{ config('ukv.phone_de_e164') ?: '+490000000000' }}">@include('partials.call-glyph')<b>Europe Team:</b>&nbsp;{{ config('ukv.phone_de') ?: '+49' }}</a>@endif
    <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '4915213103462' }}">@include('partials.wa-glyph')WhatsApp</a>
```

with:

```blade
    @if ($__m->isUk())
    <a href="tel:{{ config('ukv.phone_e164') ?: '+4915213103462' }}">@include('partials.call-glyph')<b>UK Team:</b>&nbsp;{{ config('ukv.phone') ?: '+44' }}</a>
    @if(config('ukv.show_de_phone'))<a href="tel:{{ config('ukv.phone_de_e164') ?: '+490000000000' }}">@include('partials.call-glyph')<b>Europe Team:</b>&nbsp;{{ config('ukv.phone_de') ?: '+49' }}</a>@endif
    @elseif ($__m->phone())
    <a href="tel:{{ preg_replace('/[^+\d]/', '', $__m->phone()) }}">@include('partials.call-glyph')<b>{{ $__m->teamLabel() }}:</b>&nbsp;{{ $__m->phone() }}</a>
    @else
    <span><b>{{ $__m->teamLabel() }}</b></span>
    @endif
    <a href="{{ $__m->chatUrl() }}">@include('partials.wa-glyph')WhatsApp</a>
```

Replace every `{{ url('/...') }}` in the header nav with `{{ market_url('/...') }}` (brand link `url('/')` becomes `market_url('/')`; `/schengen-visa`, `/services`, `/about`, `/contact` likewise). Leave `App\Support\SiteStats::chatUrl()` on the "Free case check" button for UK, but wrap it:

```blade
    <a href="{{ $__m->isUk() ? App\Support\SiteStats::chatUrl() : $__m->chatUrl() }}" target="_blank" rel="noopener" class="bpc-btn">Free case check →</a>
```

- [ ] **Step 4: Edit `partials/lp-footer.blade.php`**

At the top of the `@php` block add `$__m = \App\Support\Market::current();`. Replace `{{ url('/') }}` on the footer brand link with `{{ market_url('/') }}` and every other `{{ url('/...') }}` in the footer columns with `{{ market_url('/...') }}`. Replace the consult button href `App\Support\SiteStats::chatUrl('Hi, I would like to book a consultation.')` with `$__m->isUk() ? App\Support\SiteStats::chatUrl('Hi, I would like to book a consultation.') : $__m->chatUrl('Hi, I would like to book a consultation. ['.strtoupper($__m->code).']')`. In `.bpc-ftbottom`, after the Companies House anchor add:

```blade
      @unless ($__m->isUk())<span>Serving applicants in {{ $__m->label() }}</span>@endunless
```

- [ ] **Step 5: Edit `app/Support/NavService.php`**

Replace every `url('` with `market_url('` inside `primary()`, `ctas()` and `footerColumns()` (the `SiteStats::chatUrl()` CTA stays). Confirm with: `grep -n "url('" app/Support/NavService.php` showing only `market_url(` occurrences.

- [ ] **Step 6: Run to verify it passes**

Run: `php artisan test --filter='MarketChromeTest|Lp'`
Expected: PASS. Any UK LP test asserting exact `url()` output still passes because `market_url` returns `url()` for UK.

- [ ] **Step 7: Commit**

```bash
git add resources/views/partials/lp-chrome.blade.php resources/views/partials/lp-footer.blade.php app/Support/NavService.php tests/Feature/MarketChromeTest.php
git commit -m "feat(markets): market-aware chrome (team label, WhatsApp, links, serving line); UK unchanged"
```

---

### Task 9: Canonicals and reciprocal hreflang

**Files:**
- Create: `app/Support/MarketAlternates.php`
- Modify: `resources/views/partials/hreflang.blade.php` (replace stub)
- Modify: `resources/views/layouts/public.blade.php` (canonical from Market, hreflang include)
- Modify: `resources/views/public/home.blade.php`, `resources/views/public/tours.blade.php` and the `/schengen-visa` hub view (find it with `grep -rl "schengen-visa" resources/views/public | head`) to pass `@section('hreflang_path', '/')`, `'/tour-packages'`, `'/schengen-visa'`
- Test: `tests/Feature/MarketSeoTest.php`

**Interfaces:**
- Consumes: `Market`, `market_url`.
- Produces: `MarketAlternates::for(string $path): array<string,string>` returning `['en-GB' => ..., 'en-ZA' => ..., 'x-default' => ...]` for enabled+indexable markets only; `@include('partials.hreflang', ['path' => '/...'])` renders `<link rel="alternate" hreflang=... href=...>` lines or nothing.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MarketSeoTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\MarketAlternates;
use Tests\TestCase;

final class MarketSeoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'ukv.intl_base_url' => 'https://beyondpassports.com',
            'ukv.base_url' => 'https://beyondpassports.co.uk',
            'ukv.markets.za.enabled' => true, 'ukv.markets.za.indexable' => true,
            'ukv.markets.ae.enabled' => true, 'ukv.markets.ae.indexable' => false,
        ]);
    }

    public function test_alternates_include_uk_indexable_markets_and_x_default_only(): void
    {
        $alt = MarketAlternates::for('/schengen-visa');
        $this->assertSame('https://beyondpassports.co.uk/schengen-visa', $alt['en-GB']);
        $this->assertSame('https://beyondpassports.com/za/schengen-visa', $alt['en-ZA']);
        $this->assertArrayNotHasKey('en-AE', $alt);
        $this->assertSame('https://beyondpassports.com', $alt['x-default']);
    }

    public function test_market_page_emits_reciprocal_hreflang(): void
    {
        $r = $this->get('/za/schengen-visa');
        $r->assertSee('hreflang="en-GB" href="https://beyondpassports.co.uk/schengen-visa"', false);
        $r->assertSee('hreflang="en-ZA" href="https://beyondpassports.com/za/schengen-visa"', false);
        $r->assertSee('hreflang="x-default" href="https://beyondpassports.com"', false);
        $r->assertDontSee('hreflang="en-AE"', false);
    }

    public function test_uk_hub_emits_market_alternates(): void
    {
        $r = $this->get('/schengen-visa');
        $r->assertOk();
        $r->assertSee('hreflang="en-ZA" href="https://beyondpassports.com/za/schengen-visa"', false);
        $r->assertSee('hreflang="en-GB"', false);
    }

    public function test_uk_page_canonical_stays_co_uk_even_on_com_host(): void
    {
        $r = $this->get('https://beyondpassports.com/schengen-visa');
        $r->assertSee('<link rel="canonical" href="https://beyondpassports.co.uk/schengen-visa">', false);
    }

    public function test_market_canonical_is_com(): void
    {
        $this->get('/za')->assertSee('<link rel="canonical" href="https://beyondpassports.com/za">', false);
    }

    public function test_no_alternates_when_no_market_indexable(): void
    {
        config(['ukv.markets.za.indexable' => false]);
        $this->assertSame([], MarketAlternates::for('/schengen-visa'));
        $this->get('/schengen-visa')->assertDontSee('hreflang=', false);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketSeoTest`
Expected: FAIL (class missing).

- [ ] **Step 3: Create `app/Support/MarketAlternates.php`**

```php
<?php

declare(strict_types=1);

namespace App\Support;

/**
 * hreflang map for a UK-relative path. Returns [] when no non-UK market is both enabled and
 * indexable (then no hreflang is emitted anywhere, UK included). Otherwise returns en-GB, each
 * indexable market's locale, and x-default pointing at the .com international chooser.
 */
final class MarketAlternates
{
    /** @return array<string,string> */
    public static function for(string $path): array
    {
        $path = '/'.trim($path, '/');
        $markets = array_filter(Market::enabled(), fn (Market $m) => $m->isIndexable());
        if ($markets === []) {
            return [];
        }
        $out = ['en-GB' => market_url($path === '/' ? '/' : $path, Market::uk())];
        // UK base must be absolute .co.uk regardless of the request host (Review Focus 3).
        $out['en-GB'] = rtrim(Market::uk()->baseUrl(), '/').($path === '/' ? '/' : $path);
        foreach ($markets as $m) {
            $out[$m->locale()] = market_url($path, $m);
        }
        $out['x-default'] = rtrim((string) config('ukv.intl_base_url'), '/');

        return $out;
    }
}
```

- [ ] **Step 4: Replace the hreflang partial**

Replace `resources/views/partials/hreflang.blade.php` with:

```blade
{{-- Reciprocal hreflang (spec 7). Pass 'path' as the UK-relative path of this page type
     ('/', '/schengen-visa', '/tour-packages'). Renders nothing when no market is indexable. --}}
@foreach (\App\Support\MarketAlternates::for($path ?? '/') as $lang => $href)
<link rel="alternate" hreflang="{{ $lang }}" href="{{ $href }}">
@endforeach
```

- [ ] **Step 5: Canonical from Market in `layouts/public.blade.php`**

Replace the line

```blade
  $__ogUrl   = trim($__env->yieldContent('canonical')) ?: url()->current();
```

with:

```blade
  // Canonical host comes from the Market, never the request host, so a UK page served on the
  // .com host still canonicals to .co.uk (international foundation spec, section 7).
  $__ogUrl   = trim($__env->yieldContent('canonical')) ?: rtrim(\App\Support\Market::current()->baseUrl(), '/').'/'.ltrim(request()->path() === '/' ? '' : request()->path(), '/');
```

Directly after `<link rel="canonical" href="{{ $__ogUrl }}">` add:

```blade
@hasSection('hreflang_path')@include('partials.hreflang', ['path' => trim($__env->yieldContent('hreflang_path'))])@endif
```

- [ ] **Step 6: Declare the hreflang path on the three UK pages**

In `resources/views/public/home.blade.php` add near the other `@section` lines: `@section('hreflang_path', '/')`. In `resources/views/public/tours.blade.php`: `@section('hreflang_path', '/tour-packages')`. Find the `/schengen-visa` hub view: `grep -rn "pageOrCoded('schengen-visa'" routes/web.php` gives the coded view name; open it and add `@section('hreflang_path', '/schengen-visa')`.

- [ ] **Step 7: Run to verify it passes**

Run: `php artisan test --filter=MarketSeoTest`
Expected: PASS (6 tests). If `test_uk_page_canonical_stays_co_uk_even_on_com_host` fails because `trustProxies` rewrites the host, assert on `request()->path()` logic only: the canonical must still start with `https://beyondpassports.co.uk/`.

- [ ] **Step 8: Commit**

```bash
git add app/Support/MarketAlternates.php resources/views/partials/hreflang.blade.php resources/views/layouts/public.blade.php resources/views/public/home.blade.php resources/views/public/tours.blade.php $(grep -rl "hreflang_path', '/schengen-visa'" resources/views/public) tests/Feature/MarketSeoTest.php
git commit -m "feat(seo): market-based canonicals and reciprocal hreflang incl. .co.uk pages"
```

---

### Task 10: International sitemap and robots

**Files:**
- Create: `app/Http/Controllers/SitemapIntlController.php`
- Modify: `routes/web.php` (route next to `/sitemap.xml`)
- Modify: `public/robots.txt` and `public/robots.full.txt` (second Sitemap line + AI crawler block)
- Test: `tests/Feature/MarketSitemapTest.php`

**Interfaces:**
- Consumes: `Market::enabled()`, `market_url`.
- Produces: `GET /sitemap-intl.xml` listing `/`, `/schengen-visa`, `/tour-packages` per enabled+indexable market on the `.com` base with today's `lastmod`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MarketSitemapTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class MarketSitemapTest extends TestCase
{
    public function test_intl_sitemap_lists_only_enabled_indexable_markets(): void
    {
        config([
            'ukv.intl_base_url' => 'https://beyondpassports.com',
            'ukv.markets.za.enabled' => true, 'ukv.markets.za.indexable' => true,
            'ukv.markets.ae.enabled' => true, 'ukv.markets.ae.indexable' => false,
            'ukv.markets.us.enabled' => false, 'ukv.markets.us.indexable' => true,
        ]);
        $r = $this->get('/sitemap-intl.xml');
        $r->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $r->assertSee('<loc>https://beyondpassports.com/za</loc>', false);
        $r->assertSee('<loc>https://beyondpassports.com/za/schengen-visa</loc>', false);
        $r->assertSee('<loc>https://beyondpassports.com/za/tour-packages</loc>', false);
        $r->assertDontSee('/ae', false);
        $r->assertDontSee('/us', false);
        $r->assertSee('<lastmod>'.now()->toDateString().'</lastmod>', false);
    }

    public function test_intl_sitemap_is_valid_empty_urlset_when_nothing_indexable(): void
    {
        $r = $this->get('/sitemap-intl.xml');
        $r->assertOk();
        $r->assertSee('<urlset', false);
        $r->assertDontSee('<loc>', false);
    }

    public function test_uk_sitemap_unchanged_and_robots_lists_both(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('beyondpassports.com/', false);
        $robots = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Sitemap: https://beyondpassports.co.uk/sitemap.xml', $robots);
        $this->assertStringContainsString('Sitemap: https://beyondpassports.com/sitemap-intl.xml', $robots);
        $this->assertStringContainsString('User-agent: GPTBot', $robots);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketSitemapTest`
Expected: FAIL with 404.

- [ ] **Step 3: Create the controller**

Create `app/Http/Controllers/SitemapIntlController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Market;
use Illuminate\Http\Response;

/**
 * Sitemap for beyondpassports.com market pages. Lists only markets that are enabled AND
 * indexable. The UK sitemap (/sitemap.xml) is untouched. Both are referenced from robots.txt;
 * cross-host sitemap references are honoured once both properties are verified in Search Console.
 */
final class SitemapIntlController extends Controller
{
    private const PATHS = ['/', '/schengen-visa', '/tour-packages'];

    public function __invoke(): Response
    {
        $today = now()->toDateString();
        $urls = [];
        foreach (Market::enabled() as $m) {
            if (! $m->isIndexable()) {
                continue;
            }
            foreach (self::PATHS as $p) {
                $urls[] = ['loc' => market_url($p, $m), 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => $p === '/' ? '0.9' : '0.8'];
            }
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $u) {
            $xml .= "  <url><loc>".e($u['loc'])."</loc><lastmod>{$u['lastmod']}</lastmod><changefreq>{$u['changefreq']}</changefreq><priority>{$u['priority']}</priority></url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
```

- [ ] **Step 4: Register the route**

In `routes/web.php`, directly after `Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');` add:

```php
Route::get('/sitemap-intl.xml', \App\Http\Controllers\SitemapIntlController::class)->name('sitemap.intl');
```

- [ ] **Step 5: Update robots files**

Replace the contents of `public/robots.txt` with:

```
User-agent: *
Allow: /

# AI search crawlers: allowed (goal G3; Atlys is the only competitor doing this explicitly)
User-agent: GPTBot
Allow: /
User-agent: OAI-SearchBot
Allow: /
User-agent: ChatGPT-User
Allow: /
User-agent: ClaudeBot
Allow: /
User-agent: anthropic-ai
Allow: /
User-agent: PerplexityBot
Allow: /
User-agent: Google-Extended
Allow: /

Sitemap: https://beyondpassports.co.uk/sitemap.xml
Sitemap: https://beyondpassports.com/sitemap-intl.xml
```

In `public/robots.full.txt` keep the existing Disallow lines and append the same AI block and the second `Sitemap:` line after the existing one.

- [ ] **Step 6: Run to verify it passes**

Run: `php artisan test --filter=MarketSitemapTest`
Expected: PASS (3 tests).

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/SitemapIntlController.php routes/web.php public/robots.txt public/robots.full.txt tests/Feature/MarketSitemapTest.php
git commit -m "feat(seo): /sitemap-intl.xml for indexable markets; robots lists both sitemaps + AI crawlers"
```

---

### Task 11: Analytics market tag and lead attribution field

**Files:**
- Modify: `resources/views/partials/analytics-head.blade.php` (dataLayer push before GTM)
- Modify: `resources/views/partials/utm-capture.blade.php` (add `market` to the CRM payloads)
- Test: `tests/Feature/MarketAnalyticsTest.php`

**Interfaces:**
- Consumes: `Market::current()`.
- Produces: `window.dataLayer` contains `{bp_market: '<code>'}` on every page (`'uk'` on UK pages); `window.__bpMarket = '<code>'` global read by utm-capture; CRM `FormData` gets `market=<code>`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MarketAnalyticsTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class MarketAnalyticsTest extends TestCase
{
    public function test_market_page_pushes_bp_market_to_datalayer(): void
    {
        config(['ukv.markets.ca.enabled' => true, 'ukv.cookie_banner' => false]);
        $html = $this->get('/ca')->getContent();
        $this->assertStringContainsString("window.dataLayer=window.dataLayer||[];window.dataLayer.push({bp_market:'ca'});window.__bpMarket='ca';", $html);
        $this->assertStringContainsString('fd.append("market", window.__bpMarket || "uk")', $html);
    }

    public function test_uk_page_pushes_uk(): void
    {
        config(['ukv.cookie_banner' => false]);
        $this->assertStringContainsString("bp_market:'uk'", $this->get('/')->getContent());
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketAnalyticsTest`
Expected: FAIL.

- [ ] **Step 3: Edit `partials/analytics-head.blade.php`**

Immediately after the opening `@if (! config('ukv.cookie_banner', false))` line and before `<script>window.__bpTagsLoaded=true;</script>`, add:

```blade
<script>window.dataLayer=window.dataLayer||[];window.dataLayer.push({bp_market:'{{ \App\Support\Market::current()->code }}'});window.__bpMarket='{{ \App\Support\Market::current()->code }}';</script>
```

- [ ] **Step 4: Edit `partials/utm-capture.blade.php`**

Find every place a `FormData` is built for the CRM post (search `new FormData`). In each builder, immediately after the `FormData` is created and before it is posted, add:

```js
      fd.append("market", window.__bpMarket || "uk");
```

If the local variable is not named `fd`, use the actual variable name but keep the string `"market"` and the fallback `"uk"`. If one builder is the `sync` object created lazily (`if (!sync) sync = new FormData;`), add `sync.append("market", window.__bpMarket || "uk");` right after that line. Keep the tested string `fd.append("market", window.__bpMarket || "uk")` present at least once (the wa-click and form posts use `fd`).

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter='MarketAnalyticsTest|LpAssemblerTest'`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add resources/views/partials/analytics-head.blade.php resources/views/partials/utm-capture.blade.php tests/Feature/MarketAnalyticsTest.php
git commit -m "feat(analytics): bp_market dataLayer tag and market field on CRM attribution posts"
```

---

### Task 12: `.com` root chooser and manual market switcher

**Files:**
- Create: `app/Http/Controllers/Market/InternationalHomeController.php`
- Create: `resources/views/market/international.blade.php`
- Create: `resources/views/partials/market-switcher.blade.php`
- Modify: `routes/web.php` (domain route registered BEFORE the UK `/` route)
- Modify: `resources/views/partials/lp-chrome.blade.php` (include switcher after the header)
- Test: `tests/Feature/InternationalHomeTest.php`

**Interfaces:**
- Consumes: `Market::enabled()`, `market_url`, `config('ukv.intl_base_url')`.
- Produces: route `intl.home` bound to the `.com` host only; cookie `bp_market` (30 days, SameSite=Lax) set by JS on market pages; banner markup `#bp-market-banner` hidden by default.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/InternationalHomeTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class InternationalHomeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['ukv.intl_base_url' => 'https://beyondpassports.com', 'ukv.base_url' => 'https://beyondpassports.co.uk', 'ukv.markets.za.enabled' => true]);
    }

    public function test_com_root_serves_chooser_with_enabled_markets_and_uk_card(): void
    {
        $r = $this->get('https://beyondpassports.com/');
        $r->assertOk();
        $r->assertSee('Choose where you are applying from');
        $r->assertSee('href="https://beyondpassports.com/za"', false);
        $r->assertSee('href="https://beyondpassports.co.uk"', false);
        $r->assertDontSee('href="https://beyondpassports.com/ae"', false);
        $r->assertSee('<link rel="canonical" href="https://beyondpassports.com">', false);
    }

    public function test_co_uk_root_still_serves_uk_home(): void
    {
        $r = $this->get('https://beyondpassports.co.uk/');
        $r->assertOk();
        $r->assertDontSee('Choose where you are applying from');
    }

    public function test_market_pages_carry_switcher_and_cookie_script(): void
    {
        $html = $this->get('/za')->getContent();
        $this->assertStringContainsString('id="bp-market-banner"', $html);
        $this->assertStringContainsString("bp_market=za", $html);
        $this->assertStringNotContainsString('location.replace', $html); // never auto-redirect
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=InternationalHomeTest`
Expected: FAIL (`.com/` serves UK home).

- [ ] **Step 3: Create the controller and view**

Create `app/Http/Controllers/Market/InternationalHomeController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Support\Market;
use Illuminate\Http\Response;

/** The .com root: a manual chooser of enabled markets plus the UK. x-default target. */
final class InternationalHomeController extends Controller
{
    public function __invoke(): Response
    {
        return response()->view('market.international', ['markets' => Market::enabled()]);
    }
}
```

Create `resources/views/market/international.blade.php`:

```blade
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="{{ asset('assets/brand/favicon.svg?v=2') }}" type="image/svg+xml">
<title>Beyond Passports International</title>
<meta name="description" content="Schengen visa preparation with a named consultant. Choose where you are applying from.">
<link rel="canonical" href="{{ rtrim(config('ukv.intl_base_url'), '/') }}">
@include('partials.hreflang', ['path' => '/'])
@include('partials.analytics-head')
<style>
body{margin:0;font-family:"Outfit",system-ui,sans-serif;color:#16222E;background:#F4F5F6}
.ih{max-width:900px;margin:0 auto;padding:64px 24px}
.ih h1{font-size:clamp(26px,4vw,40px);margin:0 0 20px}
.ih-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px}
.ih-card{display:block;background:#fff;border:1px solid #dde3ec;border-radius:16px;padding:20px;text-decoration:none;color:inherit;font-weight:700}
.ih-card small{display:block;font-weight:500;color:#5d6b76;margin-top:6px}
@media (max-width:560px){.ih{padding:40px 16px}}
</style>
</head>
<body>
@include('partials.lp-chrome')
<main class="ih">
  <h1>Choose where you are applying from</h1>
  <div class="ih-grid">
    <a class="ih-card" href="{{ rtrim(config('ukv.base_url') ?: config('app.url'), '/') }}">United Kingdom<small>beyondpassports.co.uk</small></a>
    @foreach ($markets as $m)
      <a class="ih-card" href="{{ market_url('/', $m) }}">{{ $m->label() }}<small>{{ $m->supportHours() }}</small></a>
    @endforeach
  </div>
</main>
@include('partials.lp-footer')
@include('partials.utm-capture')
</body>
</html>
```

- [ ] **Step 4: Create the switcher partial and include it**

Create `resources/views/partials/market-switcher.blade.php`:

```blade
{{-- Manual market switcher + remembered preference (spec 4.4). Sets cookie bp_market on market
     pages and shows a dismissible banner when the stored market differs from this page's market.
     NEVER redirects automatically (Atlys's IP 307 is the anti-pattern). --}}
@php($__m = \App\Support\Market::current())
<div id="bp-market-banner" hidden style="background:#0F4A61;color:#fff;font:600 14px 'Outfit',system-ui,sans-serif;padding:8px 24px;text-align:center">
  <span id="bp-market-banner-text"></span>
  <button type="button" onclick="document.getElementById('bp-market-banner').hidden=true;document.cookie='bp_market_dismiss=1;path=/;max-age=2592000;samesite=lax'" style="margin-left:12px;background:transparent;color:#fff;border:1px solid #fff;border-radius:8px;padding:2px 8px;cursor:pointer">Dismiss</button>
</div>
<script>
(function(){
  var cur='{{ $__m->code }}';
  var labels={!! json_encode(collect(\App\Support\Market::enabled())->mapWithKeys(fn ($m) => [$m->code => ['label' => $m->label(), 'url' => market_url('/', $m)]])->all(), JSON_UNESCAPED_SLASHES) !!};
  function ck(n){var m=document.cookie.match(new RegExp('(?:^|; )'+n+'=([^;]*)'));return m?decodeURIComponent(m[1]):'';}
  if(cur!=='uk'){document.cookie='bp_market='+cur+';path=/;max-age=2592000;samesite=lax'+(location.protocol==='https:'?';secure':'');}
  var stored=ck('bp_market');
  if(stored&&stored!==cur&&!ck('bp_market_dismiss')&&labels[stored]){
    var b=document.getElementById('bp-market-banner');
    document.getElementById('bp-market-banner-text').innerHTML='Applying from '+labels[stored].label+'? <a style="color:#fff" href="'+labels[stored].url+'">Go to the '+labels[stored].label+' site</a>';
    b.hidden=false;
  }
})();
</script>
```

In `partials/lp-chrome.blade.php`, immediately after the closing `</header>` tag add:

```blade
@include('partials.market-switcher')
```

- [ ] **Step 5: Register the host-bound root route**

In `routes/web.php`, immediately BEFORE the UK `Route::get('/', fn (\App\Http\Controllers\CmsController $c) => ...)->name('home');` line, add:

```php
// The ONLY host-bound route in the app (spec 4.4): the .com root serves the international
// chooser (x-default); the .co.uk root below stays the UK home. Everything else resolves the
// market from the URL segment, never from Host or IP.
Route::domain((string) parse_url((string) config('ukv.intl_base_url'), PHP_URL_HOST))
    ->get('/', \App\Http\Controllers\Market\InternationalHomeController::class)
    ->name('intl.home');
```

- [ ] **Step 6: Run to verify it passes**

Run: `php artisan test --filter='InternationalHomeTest|MarketChromeTest|MarketHomePageTest'`
Expected: PASS. If `test_co_uk_root_still_serves_uk_home` fails because `config('ukv.base_url')` is empty in tests, set `APP_URL=https://beyondpassports.co.uk` in `phpunit.xml` `<env>` and rerun.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Market/InternationalHomeController.php resources/views/market/international.blade.php resources/views/partials/market-switcher.blade.php resources/views/partials/lp-chrome.blade.php routes/web.php tests/Feature/InternationalHomeTest.php phpunit.xml
git commit -m "feat(markets): .com root chooser (single host-bound route) and manual market switcher with cookie"
```

---

### Task 13: Full suite, docs, env runbook note

**Files:**
- Modify: `docs/GO-LIVE-RUNBOOK.md` (new section "International markets (.com)")
- Modify: `docs/superpowers/specs/2026-10-06-international-market-foundation-design.md` (status line to "implemented, awaiting owner review")

- [ ] **Step 1: Run the whole suite**

Run: `php artisan test`
Expected: all PASS. Fix any regression before continuing; do not skip tests.

- [ ] **Step 2: Add the runbook section**

Append to `docs/GO-LIVE-RUNBOOK.md`:

```markdown
## International markets (beyondpassports.com)

Spec: docs/superpowers/specs/2026-10-06-international-market-foundation-design.md.

1. DNS: point `beyondpassports.com` (A/CNAME) at the same host as `.co.uk`; add it as an alias
   domain in the web server / hosting panel so the Laravel app answers for both hosts. SSL for
   both. Do NOT enable Cloudflare Bot Fight Mode (it blocked Google's ad-review crawler on .co.uk).
2. `.env`: `UKV_INTL_BASE_URL=https://beyondpassports.com`. Per market:
   `UKV_MARKET_<ZA|AE|US|CA>_ENABLED=true` to serve it (still noindex),
   `UKV_MARKET_<CODE>_INDEX=true` only after the stage gate (one logged VAC check in the Portal
   Accounts tab, compliance memo reviewed by a local professional, one live local-currency charge).
   Optional `UKV_MARKET_<CODE>_WHATSAPP`, `UKV_MARKET_<CODE>_PHONE`.
3. `php artisan config:cache && php artisan route:cache && php artisan view:clear`.
4. Search Console: add the `.com` property, submit `https://beyondpassports.com/sitemap-intl.xml`.
   The .co.uk robots.txt already references it; cross-host sitemaps are honoured once both
   properties are verified.
5. Smoke: `curl -I https://beyondpassports.com/za` shows `X-Robots-Tag: noindex, nofollow` until
   INDEX is true; `https://beyondpassports.com/` shows the chooser; `https://beyondpassports.co.uk/`
   unchanged.
```

- [ ] **Step 3: Update the spec status line**

In the spec, change `**Status:** draft for owner review.` to `**Status:** implemented on branch (plan 2026-10-06-international-market-foundation.md); awaiting owner acceptance against section 11.`

- [ ] **Step 4: Commit**

```bash
git add docs/GO-LIVE-RUNBOOK.md docs/superpowers/specs/2026-10-06-international-market-foundation-design.md
git commit -m "docs: international markets go-live steps; spec status updated"
```

---

## Self-review (done at writing time)

- Spec coverage: 4.1 Task 1; 4.2 Tasks 2, 4; 4.3 Tasks 4, 6, 7; 4.4 Task 12; 4.5 Task 3; 5 Tasks 5, 8; 6 Tasks 5, 6, 7, 12; 7 Tasks 9, 10; 8 Task 11; 9 Task 0 and Task 4 step 6; 10 (error handling) Tasks 2, 3, 4, 5; 11 (tests) spread across all tasks, items 1-13 all present; 12 citations live in the spec; 13 out of scope.
- Review Focus: 1 Task 4 tests; 2 Task 3 test; 3 Task 9 test; 4 Task 2 test; 5 Task 5 test.
- Type consistency: `Market::fromCode/uk/current/bind/codes/enabled`, `market_url(string, ?Market)`, `MarketAlternates::for(string)`, `MarketHubController::SCHENGEN` used consistently.
- Known judgement calls left to the executor: Task 4 step 7 trailing-slash behaviour; Task 9 step 7 host rewriting under `trustProxies`; Task 12 step 6 `APP_URL` in phpunit.xml.
