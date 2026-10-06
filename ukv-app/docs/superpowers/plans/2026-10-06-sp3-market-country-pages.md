# SP3 Market x Destination Pages Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** serve `beyondpassports.com/{market}/schengen-visa/{country}` for 4 markets x 29 destinations from one verified `market_destination_profiles` row per cell, with the full section stack, schema set, reciprocal hreflang, sitemap entries, a Markdown twin and a generated `/llms.txt`; every unverified cell renders an honest noindex fallback.

**Architecture:** a `MarketDestinationProfile` Eloquent model (table + Filament resource + idempotent seeder) is the single source of truth per market x destination. A model-level saving guard enforces the verification gate and the copy rules so no UI path can publish an unverified figure. `MarketCountryController` renders `market.country` when the row is live, otherwise `market.country-fallback`. `MarketCountryMarkdown` renders the `.md` twin and the word count from the same row. `MarketAlternates::forDestination()` extends the foundation's hreflang map; `LpAssembler` gains a head-injection argument so the static UK gold pages reciprocate. `SitemapIntlController`, `MarketHubController` and a new `LlmsTxtController` all read `MarketDestinationProfile::live()`.

**Tech Stack:** PHP 8.2+, Laravel 12, Blade, Filament 3.2, PHPUnit via `php artisan test` (sqlite in-memory, `RefreshDatabase`), foundation classes `App\Support\Market`, `market_url()`, `App\Support\MarketAlternates`, `App\Http\Controllers\Market\MarketHubController`, `App\Http\Controllers\SitemapIntlController`, partials `market-trust-strip`, `market-price`, `hreflang`, `lp-chrome`, `lp-footer`, `disclaimer-strip`, `analytics-head`, `utm-capture`.

**Spec:** `ukv-app/docs/superpowers/specs/2026-10-06-sp3-market-country-pages-design.md`. Decision basis: `ukv-app/docs/product-goals-2026-10.md`, `ukv-app/docs/superpowers/research/2026-10-06-README-decision-basis.md`.

All paths below are relative to `ukv-app/`. Run every command from `ukv-app/`.

## Global Constraints

- The international market foundation plan must be merged first (Task 0 verifies). Do not re-implement any foundation class; extend it.
- No figure, centre, operator or rule reaches a public page unless the row is `verified` and `published`. The fallback never prints data (spec 4.4, 6.14).
- Never call `App\Support\SlotBoard` or `App\Support\SlotTiles`, and never include `partials.lp-flow`, on a `.com` country page (spec B2, 14.14).
- Prices stay null in config; `partials.market-price` is the only place a service fee renders; `Offer` schema only when `Market::priceTotal()` is non-null (spec 7).
- No em dashes and nothing matched by `App\Support\CopyRules` in any Blade copy, seed data or test fixture. The model guard rejects violations; tests scan every rendered surface (spec 4.5, 11).
- Every fee printed carries its `verified_at` date; `lastmod` and `dateModified` come from `reviewed_at`, never `now()` (spec 7, 9).
- Always include `partials.disclaimer-strip` through `partials.market-trust-strip` or the partial itself, never bare markup (memory disclaimer-strip-partial).
- Keep `ukv_`/`ukv.`/`UKV_` identifiers; display copy says Beyond Passports.
- No `AggregateRating` anywhere (spec 7).
- Existing suites stay green: `php artisan test --filter='Market|LpAssembler|Availability|Sitemap|AdminPanelSmoke'`.
- Commit after every task. Never push; the owner pushes.

## Review Focus

1. Route collision between `/{market}/schengen-visa/{country}.md` and `/{market}/schengen-visa/{country}`: the `.md` route must win for `italy.md`, and the HTML route's regex must not match `italy.md`. The `.md` route must 404 for a non-live row (Task 11 tests).
2. A live profile in a market that is enabled but not indexable: the page serves 200 with `X-Robots-Tag: noindex, nofollow` (middleware) and is absent from `sitemap-intl.xml`, hreflang and `llms.txt` (Tasks 9, 10, 12 tests).
3. hreflang reciprocity with static UK pages: `/schengen-visa/italy` on `.co.uk` must emit `en-CA` when CA x Italy is live and CA is indexable, and `forDestination('austria')` must not emit `en-GB` (Task 9 tests).
4. The verification gate lives in the model, not only in Filament: `verified = true` with any empty reviewer/date/source field throws `LogicException`; a seed or a tinker call cannot bypass it (Task 2 tests). CopyRules violations in DB content are rejected the same way.
5. Local government fee null on a live row: the fee table prints the EUR row only, never a blank local cell or a placeholder (Task 7 test `test_fee_table_handles_missing_local_government_fee`).

---

### Task 0: Verify the foundation is in place

**Files:**
- None modified.

- [ ] **Step 1: Confirm the foundation classes exist**

Run: `ls app/Support/Market.php app/Support/MarketAlternates.php app/Http/Middleware/ResolveMarket.php app/Http/Controllers/Market/MarketHubController.php app/Http/Controllers/SitemapIntlController.php resources/views/partials/market-trust-strip.blade.php resources/views/partials/market-price.blade.php resources/views/partials/hreflang.blade.php`
Expected: all eight paths listed, no "No such file" line. If any is missing, stop: merge the foundation plan branch first.

- [ ] **Step 2: Run the foundation suites**

Run: `php artisan test --filter='MarketsConfigTest|MarketTest|MarketUrlHelperTest|MarketRoutingTest|MarketHubPageTest|MarketSeoTest|MarketSitemapTest'`
Expected: all PASS.

- [ ] **Step 3: Confirm a clean tree**

Run: `git status --short | grep -v '^??'`
Expected: no modified tracked files (untracked `??` entries are fine).

---

### Task 1: CopyRules guard

**Files:**
- Create: `app/Support/CopyRules.php`
- Test: `tests/Unit/CopyRulesTest.php`

**Interfaces:**
- Produces: `App\Support\CopyRules::violations(string $text): array<int,string>` (empty when clean), `CopyRules::assertClean(string $text, string $context): void` (throws `LogicException`).

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/CopyRulesTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\CopyRules;
use Tests\TestCase;

final class CopyRulesTest extends TestCase
{
    public function test_clean_text_has_no_violations(): void
    {
        $this->assertSame([], CopyRules::violations('Appointments are free and booked only on the official site, in your name.'));
    }

    public function test_em_dash_is_flagged(): void
    {
        $this->assertSame(['em dash'], CopyRules::violations("We watch \u{2014} you book."));
    }

    public function test_banned_patterns_are_flagged_case_insensitively(): void
    {
        $v = CopyRules::violations('Guaranteed slot. Fast-Track visa. Priority Appointment. VIP lounge. Replies in 30 minutes. France visa for Indians.');
        foreach (['guarantee', 'fast-track', 'priority appointment', 'vip', '30 minutes', 'for indians'] as $b) {
            $this->assertContains($b, $v);
        }
    }

    public function test_vip_is_a_whole_word(): void
    {
        $this->assertSame([], CopyRules::violations('The vipassana retreat is not relevant.'));
    }

    public function test_vat_inclusive_claim_is_flagged(): void
    {
        $this->assertContains('vat claim', CopyRules::violations('AED 649 incl. 5% VAT per applicant'));
        $this->assertContains('vat claim', CopyRules::violations('price including VAT'));
    }

    public function test_assert_clean_throws_with_context(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('faqs');
        CopyRules::assertClean('A guaranteed outcome', 'faqs');
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=CopyRulesTest`
Expected: FAIL with "Class App\Support\CopyRules not found".

- [ ] **Step 3: Create `app/Support/CopyRules.php`**

```php
<?php

declare(strict_types=1);

namespace App\Support;

use LogicException;

/**
 * Copy rules for every public surface on beyondpassports.com (spec 4.5, 11, 17). Applied by the
 * MarketDestinationProfile saving guard to DB content and by tests to rendered pages. The list
 * comes from product-goals "Excluded on purpose", the competitor-swot "patterns BP must not copy",
 * the foundation's no-30-minute rule on .com and the UAE licensing memo (no VAT-inclusive claim
 * until UAE VAT-registered).
 */
final class CopyRules
{
    /** @var array<string,string> label => regex (case-insensitive) */
    private const BANNED = [
        'guarantee'            => '/guarantee/i',
        'fast-track'           => '/fast[- ]track/i',
        'priority appointment' => '/priority appointment/i',
        'early appointment'    => '/early appointment/i',
        'vip'                  => '/\bvip\b/i',
        '30 minutes'           => '/30[- ]minute(s)?\b/i',
        'for indians'          => '/for indians/i',
        'get visa'             => '/\bget visa\b/i',
        'on time delivery'     => '/on[- ]time (delivery|guarantee)/i',
        'vat claim'            => '/(incl(uding|\.)?|inclusive of)\s*(5\s*%\s*)?vat/i',
    ];

    /** @return array<int,string> */
    public static function violations(string $text): array
    {
        $out = [];
        if (str_contains($text, "\u{2014}")) {
            $out[] = 'em dash';
        }
        foreach (self::BANNED as $label => $re) {
            if (preg_match($re, $text) === 1) {
                $out[] = $label;
            }
        }

        return $out;
    }

    public static function assertClean(string $text, string $context): void
    {
        $v = self::violations($text);
        if ($v !== []) {
            throw new LogicException("Copy rules violated in {$context}: ".implode(', ', $v));
        }
    }
}
```

- [ ] **Step 4: Run to verify it passes**

Run: `php artisan test --filter=CopyRulesTest`
Expected: PASS (6 tests).

- [ ] **Step 5: Commit**

```bash
git add app/Support/CopyRules.php tests/Unit/CopyRulesTest.php
git commit -m "feat(intl): CopyRules guard (em dash, banned appointment/guarantee wording, VAT claim)"
```

---

### Task 2: Destination registry, migration and model with verification guard

**Files:**
- Create: `app/Support/SchengenDestinations.php`
- Create: `database/migrations/2026_10_06_000001_create_market_destination_profiles_table.php`
- Create: `app/Models/MarketDestinationProfile.php`
- Modify: `app/Http/Controllers/Market/MarketHubController.php` (`SCHENGEN` aliases the registry)
- Test: `tests/Unit/SchengenDestinationsTest.php`, `tests/Feature/MarketCountryProfileModelTest.php`

**Interfaces:**
- Produces: `SchengenDestinations::NAMES` (29), `::ISO` (name => iso2), `::slugs()`, `::slug(string $name)`, `::nameFromSlug(string)`, `::isoFromSlug(string)`, `::routePattern()`.
- Produces: `App\Models\MarketDestinationProfile` with constants `OPERATORS`, `BOOKING_MODES`, `OPERATOR_LABELS`; scopes `live()`, `forMarket(string)`; methods `isLive()`, `operatorLabel()`, `portalLabel()`, `bookingStepText()`, `copyText()`, `path()`; static `findFor(string $market, string $slug): ?self`.

- [ ] **Step 1: Write the failing tests**

Create `tests/Unit/SchengenDestinationsTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Market\MarketHubController;
use App\Support\SchengenDestinations;
use Tests\TestCase;

final class SchengenDestinationsTest extends TestCase
{
    public function test_registry_has_29_entries_and_matches_hub_constant(): void
    {
        $this->assertCount(29, SchengenDestinations::NAMES);
        $this->assertCount(29, SchengenDestinations::ISO);
        $this->assertSame(SchengenDestinations::NAMES, MarketHubController::SCHENGEN);
    }

    public function test_slug_round_trip(): void
    {
        $this->assertSame('czechia', SchengenDestinations::slug('Czechia'));
        $this->assertSame('Italy', SchengenDestinations::nameFromSlug('italy'));
        $this->assertSame('it', SchengenDestinations::isoFromSlug('italy'));
        $this->assertNull(SchengenDestinations::nameFromSlug('narnia'));
        $this->assertContains('netherlands', SchengenDestinations::slugs());
    }

    public function test_route_pattern_is_pipe_joined_slugs_without_dots(): void
    {
        $p = SchengenDestinations::routePattern();
        $this->assertStringStartsWith('austria|belgium|', $p);
        $this->assertStringNotContainsString('.', $p);
        $this->assertSame(28, substr_count($p, '|'));
    }
}
```

Create `tests/Feature/MarketCountryProfileModelTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\MarketDestinationProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MarketCountryProfileModelTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string,mixed> */
    private function base(array $over = []): array
    {
        return array_merge([
            'market' => 'ca', 'destination_slug' => 'italy', 'destination_name' => 'Italy', 'destination_iso' => 'it',
            'operator' => 'embassy_direct', 'portal_name' => 'Prenot@mi', 'booking_mode' => 'calendar',
            'centres' => [['city' => 'Toronto', 'name' => 'Consulate General of Italy', 'serves' => 'Ontario']],
            'jurisdiction_note' => 'By province.', 'status_rule' => 'PR card valid three months after return.',
            'processing_text' => 'Usually within 15 days', 'appointment_copy' => 'Free and personal.', 'intro' => 'Intro.',
            'govt_fee_local_adult' => 146, 'govt_fee_local_currency' => 'CAD', 'govt_fee_verified_at' => '2026-10-06', 'govt_fee_source_url' => 'https://constoronto.esteri.it/',
            'vac_fee_amount' => 0, 'vac_fee_currency' => 'CAD', 'vac_fee_note' => 'No centre fee.', 'vac_fee_verified_at' => '2026-10-06', 'vac_fee_source_url' => 'https://prenotami.esteri.it/',
            'documents' => [['document' => 'Passport', 'detail' => 'Valid.', 'common_mistake' => 'Expired.']],
            'faqs' => [['q' => 'Q?', 'a' => 'A.']],
            'source_urls' => [['label' => 'Consulate', 'url' => 'https://constoronto.esteri.it/']],
            'reviewed_by' => 'Usama Toheed', 'reviewed_at' => '2026-10-06 18:00:00',
            'verified' => true, 'published' => true,
        ], $over);
    }

    public function test_valid_verified_profile_saves_and_is_live(): void
    {
        $p = MarketDestinationProfile::create($this->base());
        $this->assertTrue($p->fresh()->isLive());
        $this->assertSame(1, MarketDestinationProfile::live()->count());
        $this->assertSame('the consulate directly', $p->operatorLabel());
        $this->assertSame('/schengen-visa/italy', $p->path());
        $this->assertNotNull(MarketDestinationProfile::findFor('ca', 'italy'));
        $this->assertNull(MarketDestinationProfile::findFor('za', 'italy'));
    }

    public function test_verified_without_reviewer_or_dates_is_rejected(): void
    {
        foreach (['reviewed_by', 'reviewed_at', 'govt_fee_verified_at', 'vac_fee_verified_at', 'vac_fee_source_url'] as $k) {
            try {
                MarketDestinationProfile::create($this->base([$k => null, 'destination_slug' => 'spain', 'destination_name' => 'Spain', 'destination_iso' => 'es']));
                $this->fail("expected LogicException when $k is empty");
            } catch (\LogicException $e) {
                $this->assertStringContainsString($k, $e->getMessage());
            }
        }
        $this->assertSame(0, MarketDestinationProfile::count());
    }

    public function test_unverified_profile_may_omit_reviewer(): void
    {
        $p = MarketDestinationProfile::create($this->base(['verified' => false, 'published' => false, 'reviewed_by' => null, 'reviewed_at' => null]));
        $this->assertFalse($p->isLive());
    }

    public function test_copy_rule_violation_in_json_content_is_rejected(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('guarantee');
        MarketDestinationProfile::create($this->base(['faqs' => [['q' => 'Is it guaranteed?', 'a' => 'No.']]]));
    }

    public function test_unknown_operator_or_booking_mode_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MarketDestinationProfile::create($this->base(['operator' => 'carrier-pigeon']));
    }

    public function test_booking_step_text_varies_by_mode(): void
    {
        $cal = new MarketDestinationProfile($this->base());
        $email = new MarketDestinationProfile($this->base(['booking_mode' => 'email_queue']));
        $this->assertStringContainsString('Prenot@mi', $cal->bookingStepText());
        $this->assertStringContainsString('email', $email->bookingStepText());
        $this->assertNotSame($cal->bookingStepText(), $email->bookingStepText());
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter='SchengenDestinationsTest|MarketCountryProfileModelTest'`
Expected: FAIL (classes missing).

- [ ] **Step 3: Create `app/Support/SchengenDestinations.php`**

```php
<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

/**
 * The 29 Schengen destinations: names, ISO-2 codes and URL slugs. Single registry for the market
 * hub, the /{market}/schengen-visa/{country} routes, sitemap-intl, llms.txt and hreflang (spec 5.1).
 */
final class SchengenDestinations
{
    /** @var array<string,string> name => iso2, alphabetical */
    public const ISO = [
        'Austria' => 'at', 'Belgium' => 'be', 'Bulgaria' => 'bg', 'Croatia' => 'hr', 'Czechia' => 'cz',
        'Denmark' => 'dk', 'Estonia' => 'ee', 'Finland' => 'fi', 'France' => 'fr', 'Germany' => 'de',
        'Greece' => 'gr', 'Hungary' => 'hu', 'Iceland' => 'is', 'Italy' => 'it', 'Latvia' => 'lv',
        'Liechtenstein' => 'li', 'Lithuania' => 'lt', 'Luxembourg' => 'lu', 'Malta' => 'mt', 'Netherlands' => 'nl',
        'Norway' => 'no', 'Poland' => 'pl', 'Portugal' => 'pt', 'Romania' => 'ro', 'Slovakia' => 'sk',
        'Slovenia' => 'si', 'Spain' => 'es', 'Sweden' => 'se', 'Switzerland' => 'ch',
    ];

    /** @var array<int,string> */
    public const NAMES = [
        'Austria', 'Belgium', 'Bulgaria', 'Croatia', 'Czechia', 'Denmark', 'Estonia', 'Finland',
        'France', 'Germany', 'Greece', 'Hungary', 'Iceland', 'Italy', 'Latvia', 'Liechtenstein',
        'Lithuania', 'Luxembourg', 'Malta', 'Netherlands', 'Norway', 'Poland', 'Portugal',
        'Romania', 'Slovakia', 'Slovenia', 'Spain', 'Sweden', 'Switzerland',
    ];

    public static function slug(string $name): string
    {
        return Str::slug($name);
    }

    /** @return array<int,string> */
    public static function slugs(): array
    {
        return array_map([self::class, 'slug'], self::NAMES);
    }

    public static function nameFromSlug(string $slug): ?string
    {
        foreach (self::NAMES as $n) {
            if (self::slug($n) === $slug) {
                return $n;
            }
        }

        return null;
    }

    public static function isoFromSlug(string $slug): ?string
    {
        $name = self::nameFromSlug($slug);

        return $name === null ? null : self::ISO[$name];
    }

    /** Route constraint for {country}: lower-case slugs only, never a dot, so "italy.md" cannot match. */
    public static function routePattern(): string
    {
        return implode('|', self::slugs());
    }
}
```

- [ ] **Step 4: Point the hub constant at the registry**

In `app/Http/Controllers/Market/MarketHubController.php` replace the `public const SCHENGEN = [ ... ];` block with:

```php
    /** Alias of the shared registry so the hub, routes and sitemap can never drift (spec 5.1). */
    public const SCHENGEN = \App\Support\SchengenDestinations::NAMES;
```

- [ ] **Step 5: Create the migration**

Create `database/migrations/2026_10_06_000001_create_market_destination_profiles_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per source market x Schengen destination (4 x 29 = 116 cells). Drives the data-driven
 * country pages on beyondpassports.com (spec docs/superpowers/specs/2026-10-06-sp3-market-country-pages-design.md,
 * section 4). `verified` + `published` together gate every public surface; unverified cells render
 * the honest fallback and never a figure. Every fee carries its own verified_at + source URL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_destination_profiles', function (Blueprint $t) {
            $t->id();
            $t->string('market', 2);
            $t->string('destination_slug', 40);
            $t->string('destination_name', 60);
            $t->string('destination_iso', 2);
            $t->string('operator', 20);        // vfs|tlscontact|bls|capago|gvcw|embassy_direct|email_queue
            $t->string('portal_name', 80)->nullable();
            $t->string('booking_mode', 20);    // calendar|waitlist|allocation|embassy_direct|email_queue
            $t->json('centres')->nullable();
            $t->text('jurisdiction_note')->nullable();
            $t->text('status_rule')->nullable();
            $t->string('processing_text', 160)->nullable();
            $t->text('appointment_copy')->nullable();
            $t->text('intro')->nullable();
            $t->unsignedSmallInteger('govt_fee_eur_adult')->default(90);
            $t->unsignedSmallInteger('govt_fee_eur_child')->default(45);
            $t->decimal('govt_fee_local_adult', 10, 2)->nullable();
            $t->string('govt_fee_local_currency', 3)->nullable();
            $t->date('govt_fee_verified_at')->nullable();
            $t->string('govt_fee_source_url', 300)->nullable();
            $t->decimal('vac_fee_amount', 10, 2)->nullable();
            $t->string('vac_fee_currency', 3)->nullable();
            $t->string('vac_fee_note', 240)->nullable();
            $t->date('vac_fee_verified_at')->nullable();
            $t->string('vac_fee_source_url', 300)->nullable();
            $t->json('documents')->nullable();
            $t->json('faqs')->nullable();
            $t->json('source_urls')->nullable();
            $t->string('reviewed_by', 80)->nullable();
            $t->timestamp('reviewed_at')->nullable();
            $t->boolean('verified')->default(false);
            $t->boolean('published')->default(false);
            $t->timestamps();
            $t->unique(['market', 'destination_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_destination_profiles');
    }
};
```

- [ ] **Step 6: Create `app/Models/MarketDestinationProfile.php`**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\CopyRules;
use App\Support\SchengenDestinations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use LogicException;

/**
 * Market x destination profile (spec 4). The saving guard is the publication gate: a row cannot be
 * marked verified without a named reviewer, review date, fee verification dates and a VAC source
 * URL, and no text field may break CopyRules. Public surfaces read only live() rows.
 */
final class MarketDestinationProfile extends Model
{
    public const OPERATORS = ['vfs', 'tlscontact', 'bls', 'capago', 'gvcw', 'embassy_direct', 'email_queue'];

    public const BOOKING_MODES = ['calendar', 'waitlist', 'allocation', 'embassy_direct', 'email_queue'];

    public const OPERATOR_LABELS = [
        'vfs' => 'VFS Global',
        'tlscontact' => 'TLScontact',
        'bls' => 'BLS International',
        'capago' => 'Capago',
        'gvcw' => 'Global Visa Center World',
        'embassy_direct' => 'the consulate directly',
        'email_queue' => 'the consulate by email',
    ];

    private const REQUIRED_WHEN_VERIFIED = ['reviewed_by', 'reviewed_at', 'govt_fee_verified_at', 'vac_fee_verified_at', 'vac_fee_source_url'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'centres' => 'array',
            'documents' => 'array',
            'faqs' => 'array',
            'source_urls' => 'array',
            'govt_fee_local_adult' => 'decimal:2',
            'vac_fee_amount' => 'decimal:2',
            'govt_fee_verified_at' => 'date',
            'vac_fee_verified_at' => 'date',
            'reviewed_at' => 'datetime',
            'verified' => 'boolean',
            'published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $p): void {
            if (! in_array($p->operator, self::OPERATORS, true)) {
                throw new InvalidArgumentException("Unknown operator [{$p->operator}]");
            }
            if (! in_array($p->booking_mode, self::BOOKING_MODES, true)) {
                throw new InvalidArgumentException("Unknown booking_mode [{$p->booking_mode}]");
            }
            if (SchengenDestinations::nameFromSlug((string) $p->destination_slug) === null) {
                throw new InvalidArgumentException("Unknown destination slug [{$p->destination_slug}]");
            }
            if ($p->verified) {
                foreach (self::REQUIRED_WHEN_VERIFIED as $k) {
                    if (blank($p->{$k})) {
                        throw new LogicException("Cannot mark {$p->market}/{$p->destination_slug} verified: {$k} is empty");
                    }
                }
            }
            CopyRules::assertClean($p->copyText(), "{$p->market}/{$p->destination_slug}");
        });
    }

    public function scopeLive(Builder $q): Builder
    {
        return $q->where('verified', true)->where('published', true);
    }

    public function scopeForMarket(Builder $q, string $market): Builder
    {
        return $q->where('market', $market);
    }

    public static function findFor(string $market, string $slug): ?self
    {
        return self::query()->where('market', $market)->where('destination_slug', $slug)->first();
    }

    public function isLive(): bool
    {
        return $this->verified && $this->published;
    }

    public function operatorLabel(): string
    {
        return self::OPERATOR_LABELS[$this->operator] ?? $this->operator;
    }

    /** Display name of the booking system: portal_name, else the operator label. */
    public function portalLabel(): string
    {
        return $this->portal_name ?: $this->operatorLabel();
    }

    /** UK-relative path of this page; prefix with market_url(). */
    public function path(): string
    {
        return '/schengen-visa/'.$this->destination_slug;
    }

    /** Step 4 of the process, by booking mode (spec 4.3, 6.6). */
    public function bookingStepText(): string
    {
        $portal = $this->portalLabel();

        return match ($this->booking_mode) {
            'calendar' => "We watch the {$portal} calendar with you. Dates are released by the consulate, not by us or by anyone else. When one appears, you book it on your own account while we stay on WhatsApp.",
            'waitlist' => "We register you on the official {$portal} waitlist and finish the file so it is ready when the alert arrives. Most waitlists give you 24 hours to book, in your own name.",
            'allocation' => "This consulate allocates appointments automatically after the online form is lodged. We lodge it with you and watch for the offer email; you confirm and pay the centre fee inside its deadline.",
            'embassy_direct' => "This consulate books directly, with no visa centre in between. We tell you the release pattern we see on {$portal} and coach the check; you book in your own name.",
            'email_queue' => "This consulate books by email in a strict format; one wrong line and the request is rejected automatically. We draft the email with you, you send it from your own address, and we track the reply.",
        };
    }

    /** Every human-readable field joined, for the copy-rules guard and the word count. */
    public function copyText(): string
    {
        $parts = [
            (string) $this->jurisdiction_note, (string) $this->status_rule, (string) $this->processing_text,
            (string) $this->appointment_copy, (string) $this->intro, (string) $this->vac_fee_note, (string) $this->portal_name,
        ];
        foreach ((array) $this->centres as $c) {
            $parts[] = implode(' ', array_map('strval', $c));
        }
        foreach ((array) $this->documents as $d) {
            $parts[] = implode(' ', array_map('strval', $d));
        }
        foreach ((array) $this->faqs as $f) {
            $parts[] = implode(' ', array_map('strval', $f));
        }
        foreach ((array) $this->source_urls as $s) {
            $parts[] = (string) ($s['label'] ?? '');
        }

        return implode("\n", $parts);
    }
}
```

- [ ] **Step 7: Run to verify it passes**

Run: `php artisan test --filter='SchengenDestinationsTest|MarketCountryProfileModelTest|MarketHubPageTest'`
Expected: PASS. If `test_unknown_operator_or_booking_mode_is_rejected` reports `LogicException` instead of `InvalidArgumentException`, the enum checks are after the verified checks: move them first (they are first in the code above).

- [ ] **Step 8: Commit**

```bash
git add app/Support/SchengenDestinations.php app/Models/MarketDestinationProfile.php database/migrations/2026_10_06_000001_create_market_destination_profiles_table.php app/Http/Controllers/Market/MarketHubController.php tests/Unit/SchengenDestinationsTest.php tests/Feature/MarketCountryProfileModelTest.php
git commit -m "feat(intl): market_destination_profiles table, model with verification + copy guards, destination registry"
```

---

### Task 3: Market qualification tiles and the availability seam

**Files:**
- Create: `app/Support/MarketQualification.php`
- Create: `app/Support/MarketAvailability.php`
- Test: `tests/Unit/MarketQualificationTest.php`

**Interfaces:**
- Produces: `MarketQualification::tiles(Market $m): array<int,array{label:string,verdict:string,note:string}>` (verdict in `green|amber|red`); `MarketQualification::etiasSplit(Market $m): ?string`.
- Produces: `MarketAvailability::for(Market $m, string $slug): ?array` returning null in SP3 (SP4 fills it with `{earliest_seen, last_checked_at, checked_by, official_url}`).

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/MarketQualificationTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\CopyRules;
use App\Support\Market;
use App\Support\MarketAvailability;
use App\Support\MarketQualification;
use Tests\TestCase;

final class MarketQualificationTest extends TestCase
{
    public function test_tile_counts_per_market(): void
    {
        $this->assertCount(4, MarketQualification::tiles(Market::fromCode('za')));
        $this->assertCount(4, MarketQualification::tiles(Market::fromCode('ae')));
        $this->assertCount(8, MarketQualification::tiles(Market::fromCode('us')));
        $this->assertCount(8, MarketQualification::tiles(Market::fromCode('ca')));
        $this->assertSame([], MarketQualification::tiles(Market::uk()));
    }

    public function test_tiles_are_typed_and_clean(): void
    {
        foreach (['za', 'ae', 'us', 'ca'] as $c) {
            foreach (MarketQualification::tiles(Market::fromCode($c)) as $t) {
                $this->assertContains($t['verdict'], ['green', 'amber', 'red']);
                $this->assertSame([], CopyRules::violations($t['label'].' '.$t['note']));
            }
        }
    }

    public function test_etias_split_only_for_us_and_ca(): void
    {
        $this->assertStringContainsString('ETIAS', (string) MarketQualification::etiasSplit(Market::fromCode('us')));
        $this->assertStringContainsString('ETIAS', (string) MarketQualification::etiasSplit(Market::fromCode('ca')));
        $this->assertNull(MarketQualification::etiasSplit(Market::fromCode('za')));
    }

    public function test_availability_seam_returns_null_until_sp4(): void
    {
        $this->assertNull(MarketAvailability::for(Market::fromCode('ca'), 'italy'));
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketQualificationTest`
Expected: FAIL (classes missing).

- [ ] **Step 3: Create `app/Support/MarketQualification.php`**

```php
<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Market-level status tiles (spec 4.7): the local equivalent of the UK "time left on your e-visa"
 * step, from the competitive service brief section 4 and the market deep dives. Destination-specific
 * exceptions live in MarketDestinationProfile::status_rule, not here.
 */
final class MarketQualification
{
    /** @return array<int,array{label:string,verdict:string,note:string}> */
    public static function tiles(Market $m): array
    {
        return match ($m->code) {
            'za' => [
                ['label' => 'South African passport', 'verdict' => 'green', 'note' => 'Apply with your ID and passport. Your province decides which centre takes your file.'],
                ['label' => 'Other passport, living in South Africa', 'verdict' => 'amber', 'note' => 'You need proof of legal residence (permit or visa) valid beyond your return. Bring the original.'],
                ['label' => 'Travelling with a child', 'verdict' => 'amber', 'note' => 'Unabridged birth certificate, a Home Affairs parental consent affidavit under six months old and both parents\' signatures where the consulate asks for them.'],
                ['label' => 'Bank statements not stamped', 'verdict' => 'red', 'note' => 'Most consulates here want three months of statements stamped by the bank and under a month old. Get the stamp before you book.'],
            ],
            'ae' => [
                ['label' => 'Residence visa valid 3 months beyond the visa you want', 'verdict' => 'green', 'note' => 'Germany states this rule in writing; the others apply it in practice.'],
                ['label' => 'Residence visa expiring sooner', 'verdict' => 'amber', 'note' => 'Renew first, or travel inside the window the consulate will accept. We check the dates with you.'],
                ['label' => 'Emirate of your residence visa', 'verdict' => 'amber', 'note' => 'Dubai and the Northern Emirates go to Dubai; Abu Dhabi and Al Ain go to Abu Dhabi. It follows the visa, not where you sleep.'],
                ['label' => 'NOC or salary certificate without stamp, signature and leave dates', 'verdict' => 'red', 'note' => 'The five fields consulates look for: title, salary, approved leave dates, return confirmation, company stamp.'],
            ],
            'us' => [
                ['label' => 'Green card valid 3+ months after you return', 'verdict' => 'green', 'note' => 'Conditional card expiring inside the window reads amber: talk to us first.'],
                ['label' => 'Visa stamp valid 3+ months plus I-797', 'verdict' => 'green', 'note' => 'Bring both.'],
                ['label' => 'Stamp expired, I-797 valid', 'verdict' => 'amber', 'note' => 'Refused by Spain, Italy and Austria; Germany and the Netherlands have accepted it. Destination matters.'],
                ['label' => 'F-1 with I-20 travel signature under 12 months', 'verdict' => 'green', 'note' => 'Older signature: get it renewed before you apply.'],
                ['label' => 'OPT or STEM OPT pending (receipt only)', 'verdict' => 'red', 'note' => 'Consulates have refused on this alone. Wait for the approved EAD.'],
                ['label' => 'Pending I-485 with Advance Parole in hand', 'verdict' => 'amber', 'note' => 'Italy accepts the AP document; Switzerland lists I-512; Spain sends EAD-only holders home to apply.'],
                ['label' => 'EAD only, I-797C only, or B-1/B-2', 'verdict' => 'red', 'note' => 'Consulates say: apply in your home country.'],
                ['label' => 'State ID does not match your consulate\'s jurisdiction', 'verdict' => 'amber', 'note' => 'Bring a state-issued ID and recent bills for the address in that jurisdiction.'],
            ],
            'ca' => [
                ['label' => 'PR card valid 3+ months after you return', 'verdict' => 'green', 'note' => 'Spain counts 90 days, Portugal 60; everyone else three months.'],
                ['label' => 'PR card expiring inside the window or renewal pending', 'verdict' => 'amber', 'note' => 'Some posts accept proof of onward plans; we check your consulate\'s line before you book.'],
                ['label' => 'e-COPR, no card yet', 'verdict' => 'red', 'note' => 'No published checklist accepts a COPR alone.'],
                ['label' => 'Work permit (including PGWP) valid 3+ months', 'verdict' => 'green', 'note' => 'Sooner than that reads amber.'],
                ['label' => 'Study permit plus current enrolment', 'verdict' => 'green', 'note' => 'Bring the enrolment letter for the current term.'],
                ['label' => 'Implied or maintained status', 'verdict' => 'red', 'note' => 'VFS and Belgium refuse it as proof of residence. Wait for the new permit.'],
                ['label' => 'Visitor record or super visa', 'verdict' => 'red', 'note' => 'Not accepted, with one exception: Italy in Vancouver takes a visitor record valid six months or more.'],
                ['label' => 'Your province', 'verdict' => 'amber', 'note' => 'West to Vancouver (Edmonton for some posts), Quebec and Atlantic to Montreal, Ontario and Manitoba to Toronto, Ottawa has its own routing for some countries.'],
            ],
            default => [],
        };
    }

    /** The passport split that sits above the tiles in the US and Canada (US+CA deep dive, section 3). */
    public static function etiasSplit(Market $m): ?string
    {
        return match ($m->code) {
            'us' => 'US passport holders do not need a visa: from late 2026 they need ETIAS, an online authorisation of EUR 20 that is not a visa and is not live yet. This page is for everyone else living in the United States on a visa, green card or permit.',
            'ca' => 'Canadian passport holders do not need a visa: from late 2026 they need ETIAS, an online authorisation of EUR 20 that is not a visa and is not live yet. This page is for permanent residents and permit holders living in Canada on another passport.',
            default => null,
        };
    }
}
```

- [ ] **Step 4: Create `app/Support/MarketAvailability.php`**

```php
<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Availability seam for .com country pages (spec 6.10). SP3 returns null, which renders the honest
 * "we will check for you" block. SP4 replaces the body with a snapshot lookup returning
 * ['earliest_seen' => 'Y-m-d'|null, 'last_checked_at' => 'Y-m-d H:i', 'checked_by' => 'name', 'official_url' => '...'].
 * Never calls SlotBoard or SlotTiles (the simulated UK pool is off on .com).
 */
final class MarketAvailability
{
    /** @return array{earliest_seen:?string,last_checked_at:string,checked_by:string,official_url:string}|null */
    public static function for(Market $market, string $destinationSlug): ?array
    {
        return null;
    }
}
```

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter=MarketQualificationTest`
Expected: PASS (4 tests).

- [ ] **Step 6: Commit**

```bash
git add app/Support/MarketQualification.php app/Support/MarketAvailability.php tests/Unit/MarketQualificationTest.php
git commit -m "feat(intl): market qualification tiles and the SP4 availability seam"
```

---

### Task 4: Seed the first two verified profiles (Canada x Italy, South Africa x Spain)

**Files:**
- Create: `database/seeders/MarketDestinationProfileSeeder.php`
- Modify: `database/seeders/ProductionSeeder.php`
- Test: `tests/Feature/MarketCountrySeederTest.php`

**Interfaces:**
- Produces: two live rows reachable via `MarketDestinationProfile::findFor('ca', 'italy')` and `findFor('za', 'spain')`; idempotent.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MarketCountrySeederTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\MarketDestinationProfile;
use Database\Seeders\MarketDestinationProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MarketCountrySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_is_idempotent_and_rows_are_live(): void
    {
        $this->seed(MarketDestinationProfileSeeder::class);
        $this->seed(MarketDestinationProfileSeeder::class);
        $this->assertSame(2, MarketDestinationProfile::count());

        $it = MarketDestinationProfile::findFor('ca', 'italy');
        $es = MarketDestinationProfile::findFor('za', 'spain');
        $this->assertTrue($it->isLive());
        $this->assertTrue($es->isLive());
        $this->assertSame('embassy_direct', $it->operator);
        $this->assertSame('Prenot@mi', $it->portal_name);
        $this->assertSame('146.00', $it->govt_fee_local_adult);
        $this->assertSame('bls', $es->operator);
        $this->assertSame('1775.00', $es->govt_fee_local_adult);
        $this->assertSame('333.00', $es->vac_fee_amount);
        $this->assertGreaterThanOrEqual(10, count($it->documents));
        $this->assertGreaterThanOrEqual(10, count($it->faqs));
        $this->assertGreaterThanOrEqual(10, count($es->documents));
        $this->assertGreaterThanOrEqual(10, count($es->faqs));
        $this->assertStringContainsString('it is a scam', $it->appointment_copy);
        $this->assertStringContainsString('intermediaries', $es->appointment_copy);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketCountrySeederTest`
Expected: FAIL (seeder class missing).

- [ ] **Step 3: Create the seeder**

Create `database/seeders/MarketDestinationProfileSeeder.php`:

```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MarketDestinationProfile;
use Illuminate\Database\Seeder;

/**
 * Verified market x destination profiles (spec 4.6). Idempotent: updateOrCreate on (market, slug).
 * Data source: docs/superpowers/research/2026-10-06-deepdive-market-usa-canada.md (Canada table and
 * recommendations) and 2026-10-06-deepdive-market-south-africa.md (section 2, 3, 7). Every figure
 * carries the date it was read from the source. Greece is never seeded until the manual check.
 */
final class MarketDestinationProfileSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([$this->canadaItaly(), $this->southAfricaSpain()] as $row) {
            MarketDestinationProfile::updateOrCreate(
                ['market' => $row['market'], 'destination_slug' => $row['destination_slug']],
                $row,
            );
        }
    }

    /** @return array<string,mixed> */
    private function canadaItaly(): array
    {
        return [
            'market' => 'ca', 'destination_slug' => 'italy', 'destination_name' => 'Italy', 'destination_iso' => 'it',
            'operator' => 'embassy_direct', 'portal_name' => 'Prenot@mi', 'booking_mode' => 'calendar',
            'centres' => [
                ['city' => 'Toronto', 'name' => 'Consulate General of Italy, Toronto', 'serves' => 'Ontario, Manitoba, Northwest Territories'],
                ['city' => 'Montreal', 'name' => 'Consulate General of Italy, Montreal', 'serves' => 'Quebec, the Atlantic provinces, Nunavut'],
                ['city' => 'Vancouver', 'name' => 'Consulate General of Italy, Vancouver', 'serves' => 'British Columbia, Yukon'],
                ['city' => 'Edmonton', 'name' => 'Consular branch, Edmonton', 'serves' => 'Alberta, Saskatchewan'],
            ],
            'jurisdiction_note' => 'Italy takes Schengen applications directly at its consulates in Canada; there is no visa centre in between. Your consulate is fixed by the province you live in: Toronto for Ontario, Manitoba and the Northwest Territories; Montreal for Quebec, the Atlantic provinces and Nunavut; Vancouver for British Columbia and Yukon; the Edmonton branch for Alberta and Saskatchewan. A file lodged at the wrong consulate is turned away at the counter, so we confirm your post before you book anything.',
            'status_rule' => 'Permanent resident card, or a Canadian study or work permit, valid for at least three months after your re-entry to Canada. The Vancouver consulate also accepts a visitor record valid for six months or more; Toronto and Montreal do not. Implied or maintained status is not accepted as proof of residence. If your permit expires before the trip ends, the consulate may ask for proof that you will not be returning to Canada.',
            'processing_text' => 'Usually within 15 days once lodged; up to 45 days in busy periods',
            'appointment_copy' => 'Appointments at the Italian consulates in Canada are free, personal and booked only on Prenot@mi in your own name. New dates are released daily, around 6pm Toronto time, on an eight week rolling calendar, which is why so many people describe checking at the same time every evening. The Toronto consulate publishes this warning: "If someone asks you for money claiming that they can book an appointment for you, it is a scam." We agree with it. We never log in to your Prenot@mi account and we never book for you. What we do: make sure your file is complete before you start checking, so that the first date you see is one you can take; coach the daily check so your account is not restricted for excessive attempts; and stay on WhatsApp while you book. We then confirm the booking details with you three to ten days before the date.',
            'intro' => 'If you live in Canada on a permanent resident card, a work permit or a study permit and you hold a passport that needs a Schengen visa, Italy is one of the destinations where you deal with the consulate directly. There is no VFS or BLS centre; the appointment is booked on Prenot@mi and the file is lodged in person at the consulate for your province. This page sets out, for applicants in Canada, the exact fee, the four consular posts, the residence rule each one applies, the documents the checklist asks for and the mistakes we see most often. Everything on it was read from the consulate\'s own pages on the date shown at the bottom, and a named person is responsible for it.',
            'govt_fee_eur_adult' => 90, 'govt_fee_eur_child' => 45,
            'govt_fee_local_adult' => 146.00, 'govt_fee_local_currency' => 'CAD', 'govt_fee_verified_at' => '2026-10-06',
            'govt_fee_source_url' => 'https://constoronto.esteri.it/',
            'vac_fee_amount' => 0, 'vac_fee_currency' => 'CAD',
            'vac_fee_note' => 'No visa centre fee. Italy in Canada takes applications directly at the consulate; the only cost is the government fee, paid at lodging.',
            'vac_fee_verified_at' => '2026-10-06', 'vac_fee_source_url' => 'https://prenotami.esteri.it/',
            'documents' => [
                ['document' => 'Proof of status in Canada', 'detail' => 'Permanent resident card, or a study or work permit, valid at least three months after you re-enter Canada. Original plus a copy.', 'common_mistake' => 'Bringing an expired PR card with a renewal receipt. The receipt is not accepted; if your card expires inside the window, talk to us before you book.'],
                ['document' => 'Passport', 'detail' => 'Valid at least three months beyond your return, issued within the last ten years, with two blank pages. Older passports with previous Schengen visas help.', 'common_mistake' => 'Leaving the old passport with your travel history at home.'],
                ['document' => 'Application form', 'detail' => 'The harmonised Schengen short-stay form, completed in full and signed in the places marked.', 'common_mistake' => 'Purpose, dates or main destination that do not match the itinerary and the bookings.'],
                ['document' => 'Photograph', 'detail' => 'One recent 35 x 45 mm photo, white background, neutral expression, taken within the last six months.', 'common_mistake' => 'Home-printed photos or a picture reused from an older application.'],
                ['document' => 'Bank statements', 'detail' => 'The last three months for an account in your name at a Canadian bank, showing salary in and regular spending.', 'common_mistake' => 'One large deposit shortly before applying with no explanation. Stability over three months reads better than a single big balance.'],
                ['document' => 'Employment letter and pay slips', 'detail' => 'On company letterhead: position, start date, salary and the exact dates of approved leave, plus your last three pay slips.', 'common_mistake' => 'A letter that confirms you work there but says nothing about approved leave.'],
                ['document' => 'Students: enrolment letter', 'detail' => 'Current enrolment letter from the institution together with the study permit.', 'common_mistake' => 'An admission letter from a previous year instead of proof of the current term.'],
                ['document' => 'Travel bookings and accommodation', 'detail' => 'Round-trip booking and accommodation covering every night in the Schengen area, in your name.', 'common_mistake' => 'Hotel reservations that leave a night uncovered, or a booking that lapses before the appointment.'],
                ['document' => 'Travel medical insurance', 'detail' => 'Minimum EUR 30,000, valid in all Schengen states for the whole trip, including repatriation. Insurance cover is the first thing the Italian consulates check.', 'common_mistake' => 'A credit card insurance summary instead of a policy certificate that states the cover and the territory.'],
                ['document' => 'Cover letter and itinerary', 'detail' => 'A one-page letter explaining purpose, dates and ties to Canada, plus a day-by-day itinerary matched to the bookings.', 'common_mistake' => 'Adding a Notice of Assessment or T4 "just in case". Neither is on the consulate checklist, and unrequested papers invite questions.'],
            ],
            'faqs' => [
                ['q' => 'Do I need a Schengen visa or ETIAS?', 'a' => 'If you travel on a Canadian passport you do not need a visa; from late 2026 you will need ETIAS, an online authorisation of EUR 20 that is not a visa and is not live yet. If you live in Canada on another passport that needs a visa, you need a Schengen visa from the Italian consulate for your province, and this page is for you.'],
                ['q' => 'Which consulate handles my province?', 'a' => 'Toronto for Ontario, Manitoba and the Northwest Territories; Montreal for Quebec, the Atlantic provinces and Nunavut; Vancouver for British Columbia and Yukon; the Edmonton branch for Alberta and Saskatchewan. The post follows your address, not where it is easier to get a date.'],
                ['q' => 'Can you book my Prenot@mi appointment for me?', 'a' => 'No. The Toronto consulate says in writing that anyone asking for money to book an appointment is running a scam, and it cancels bookings it considers suspicious. Appointments are free and personal. We make sure your file is ready, coach the daily check and stay with you on WhatsApp while you book on your own account.'],
                ['q' => 'When do new dates appear?', 'a' => 'Applicants report new dates around 6pm Toronto time, released daily on an eight week rolling calendar. Check once a day at that time rather than refreshing all day; accounts are restricted for excessive attempts.'],
                ['q' => 'My PR card expires two months after I come back. Can I apply?', 'a' => 'The rule is three months of validity after re-entry. Some posts accept proof of onward plans or a renewal in progress, others do not. Send us your dates before you book and we will tell you what your consulate accepts.'],
                ['q' => 'I am on implied status while my permit is renewed. Can I apply?', 'a' => 'Not in practice. Implied or maintained status is not accepted as proof of residence by VFS-run posts or by Belgium, and Italy asks for a valid permit. Wait for the new permit, then apply.'],
                ['q' => 'Do I need a Notice of Assessment or T4?', 'a' => 'No. Neither appears on any official Italian checklist for Canada. Three months of bank statements and an employment letter with approved leave are what the consulate asks for.'],
                ['q' => 'Do I have to buy flights before applying?', 'a' => 'The consulate wants evidence of your travel plan. We review real bookings; choose refundable or hold options where you can. We never create reservations for visa purposes and we will not accept fake ones.'],
                ['q' => 'What does the whole thing cost?', 'a' => 'The government fee is CAD 146.00 per adult, CAD 73 for children aged six to eleven and nothing under six, paid at the consulate when you lodge. There is no visa centre fee for Italy in Canada. Insurance is bought by you from any insurer that meets the EUR 30,000 rule. Our fee is for preparation and review and is never collected on the consulate\'s behalf; you can apply without us for the government fee alone.'],
                ['q' => 'I was refused before. What happens now?', 'a' => 'A refusal is recorded for five years and later applications are read against it. Send us the refusal letter. We read the ground cited, tell you honestly whether a new application is sensible now, and rebuild the file around the gap. Our fee is for the work, not the decision; if a refusal letter cites an error that was ours, we redo the file for your re-application at no charge.'],
            ],
            'source_urls' => [
                ['label' => 'Consulate General of Italy in Toronto, visa pages', 'url' => 'https://constoronto.esteri.it/'],
                ['label' => 'Prenot@mi appointment portal', 'url' => 'https://prenotami.esteri.it/'],
                ['label' => 'European Commission, Schengen visa policy and fees', 'url' => 'https://home-affairs.ec.europa.eu/policies/schengen-borders-and-visa/visa-policy_en'],
            ],
            'reviewed_by' => 'Usama Toheed', 'reviewed_at' => '2026-10-06 18:00:00',
            'verified' => true, 'published' => true,
        ];
    }

    /** @return array<string,mixed> */
    private function southAfricaSpain(): array
    {
        return [
            'market' => 'za', 'destination_slug' => 'spain', 'destination_name' => 'Spain', 'destination_iso' => 'es',
            'operator' => 'bls', 'portal_name' => 'BLS Spain Visa portal', 'booking_mode' => 'calendar',
            'centres' => [
                ['city' => 'Centurion (Pretoria)', 'name' => 'BLS International, Centurion', 'serves' => 'Applicants under the Embassy of Spain in Pretoria'],
                ['city' => 'Cape Town', 'name' => 'BLS International, Cape Town', 'serves' => 'Applicants under the Consulate General of Spain in Cape Town'],
            ],
            'jurisdiction_note' => 'Spain runs two posts in South Africa: the Embassy in Pretoria, served by the BLS centre in Centurion, and the Consulate General in Cape Town, served by the BLS centre in Cape Town. Which post you fall under depends on your province of residence, and BLS will not take a file that belongs to the other post. We confirm your post with you before you book. A Durban centre is not confirmed on the official site; do not plan around one.',
            'status_rule' => 'South African passport holders apply with their ID and passport. Holders of other passports must show proof of legal residence in South Africa (a permit or visa) valid beyond the planned return. If you are reusing fingerprints taken within the last 59 months, BLS still asks for a copy of your last Schengen visa sticker.',
            'processing_text' => '15 calendar days from lodging; up to 45 in peak periods',
            'appointment_copy' => 'Spain in South Africa books through the BLS portal, where the login is tied to one applicant, or by phone on +27 10 500 2032. Walk-ins have not been accepted since June 2017. BLS states that applications must be booked directly by the applicant, without intermediaries, so we never book for you and never use your login. What we do: get your file complete first, tell you when dates typically appear, and sit with you on WhatsApp while you book. An appointment is only useful inside the window Spain allows: no earlier than 180 days and no later than 15 days before you travel. Nobody can buy an earlier date; anyone who says otherwise is selling you a risk.',
            'intro' => 'Spain is the cheapest of the main Schengen destinations to apply for from South Africa and one of the busiest, which is why dates at the BLS centres in Centurion and Cape Town disappear quickly before Easter, the winter school holidays and December. This page gives applicants in South Africa the exact rand figures BLS charges, the two posts and who falls under each, the stamped-statement and leave-letter rules that cause most trouble at the counter, the minor pack for children, and straight answers to the questions we are asked every week. Every figure was read from the official BLS site on the date shown at the bottom.',
            'govt_fee_eur_adult' => 90, 'govt_fee_eur_child' => 45,
            'govt_fee_local_adult' => 1775.00, 'govt_fee_local_currency' => 'ZAR', 'govt_fee_verified_at' => '2026-10-06',
            'govt_fee_source_url' => 'https://southafrica.blsspainvisa.com/',
            'vac_fee_amount' => 333.00, 'vac_fee_currency' => 'ZAR',
            'vac_fee_note' => 'BLS service fee per applicant, card only, paid at the centre. A child aged six to eleven pays a visa fee of R887 plus the same R333 service fee.',
            'vac_fee_verified_at' => '2026-10-06', 'vac_fee_source_url' => 'https://southafrica.blsspainvisa.com/',
            'documents' => [
                ['document' => 'South African ID, or proof of legal residence', 'detail' => 'SA citizens: ID and passport. Other passports: permit or visa valid beyond your return, original and copy.', 'common_mistake' => 'Foreign passport holders arriving without the permit; the counter will not lodge the file.'],
                ['document' => 'Passport', 'detail' => 'Valid at least three months after your return, issued within ten years, two blank pages. Bring previous passports with Schengen visas.', 'common_mistake' => 'Fewer than two blank pages.'],
                ['document' => 'Application form', 'detail' => 'The Schengen short-stay form, completed and signed; main destination stated as Spain.', 'common_mistake' => 'Signing in the wrong box or leaving the main destination blank.'],
                ['document' => 'Photographs', 'detail' => 'Two recent 35 x 45 mm photos, white background, no glasses, taken within six months.', 'common_mistake' => 'Dark backgrounds or photos older than six months.'],
                ['document' => 'Bank statements', 'detail' => 'The last three months, stamped by the bank on each page, dated within the last month, showing salary in and regular spending.', 'common_mistake' => 'Statements printed from the banking app without a bank stamp.'],
                ['document' => 'Employer letter and pay slips', 'detail' => 'Original on letterhead: position held, period of employment, exact dates of approved leave and salary, plus three pay slips.', 'common_mistake' => 'A letter without the approved leave dates.'],
                ['document' => 'Self-employed pack', 'detail' => 'CIPC registration, three months of business statements stamped by the bank, and an accountant letter.', 'common_mistake' => 'Personal statements only, with no evidence the business exists.'],
                ['document' => 'Confirmed flights and accommodation', 'detail' => 'Return flights and accommodation covering every night, in your name. Spain expects confirmed bookings before lodging.', 'common_mistake' => 'Reservations that lapse before the appointment date.'],
                ['document' => 'Travel medical insurance', 'detail' => 'Minimum EUR 30,000, valid across the Schengen area for the whole trip, including repatriation.', 'common_mistake' => 'A policy that excludes repatriation or covers Spain only.'],
                ['document' => 'Children: minor pack', 'detail' => 'Unabridged birth certificate, a Home Affairs parental consent affidavit signed before a commissioner of oaths within the last six months, and certified ID copies of both parents.', 'common_mistake' => 'An affidavit older than six months, or no copy of the absent parent\'s ID.'],
            ],
            'faqs' => [
                ['q' => 'Which BLS centre do I use?', 'a' => 'It depends on which Spanish post your province falls under: the Embassy in Pretoria (BLS Centurion) or the Consulate General in Cape Town (BLS Cape Town). We confirm this with you before you book, because the centre will not lodge a file that belongs to the other post.'],
                ['q' => 'Can you get me an earlier appointment?', 'a' => 'No one can. BLS releases dates set by the consulate and states that applications must be booked by the applicant without intermediaries. We make sure your file is ready, tell you when dates typically appear and stay on WhatsApp while you book in your own account.'],
                ['q' => 'Do my bank statements need a stamp?', 'a' => 'Yes. Take the last three months to a branch and have every page stamped; the statements should be dated within the last month. Statements printed from the app are the single most common reason files are sent back at the counter.'],
                ['q' => 'How much money do I need to show?', 'a' => 'Spain does not publish one figure for South Africa. As a working rule, budget around EUR 100 for each day of your stay, and show stable balances over three months rather than one large deposit just before applying.'],
                ['q' => 'How long will BLS keep my passport?', 'a' => 'Processing is 15 calendar days from lodging and can stretch to 45 in peak periods. BLS offers courier return where serviceable; otherwise you collect.'],
                ['q' => 'What does it cost in rand?', 'a' => 'The visa fee is R1,775 for an adult and R887 for a child aged six to eleven; children under six pay no visa fee. The BLS service fee is R333 per applicant. Both are paid by card at the centre. Our fee is separate, is for preparation and review only, and you can apply without us for the government and centre fees alone.'],
                ['q' => 'My child is travelling with one parent. What extra documents?', 'a' => 'An unabridged birth certificate, a Home Affairs parental consent affidavit signed before a commissioner of oaths within the last six months, and certified copies of both parents\' IDs. South African exit rules require the same documents at the airport, so you will need them regardless.'],
                ['q' => 'I was refused before. Should I apply again?', 'a' => 'Usually yes, once the ground cited in the refusal letter is addressed. About one in eighteen South African applications was refused in 2024, mostly for incomplete files, unexplained finances or doubts about return. Send us the letter and we will read the ground back to you honestly before any work starts.'],
                ['q' => 'Do I have to travel to Centurion?', 'a' => 'Only if your province falls under the Embassy in Pretoria. Western, Eastern and Northern Cape applicants use BLS Cape Town. There is no confirmed BLS centre in Durban, so KwaZulu-Natal applicants should plan for Centurion.'],
                ['q' => 'Why do you not promise an approval?', 'a' => 'Because the decision belongs to the consulate alone and no agency can change that. What we can do is make sure the file gives the consulate no reason to refuse, and we will tell you plainly if your case is weak before you pay the government fee.'],
            ],
            'source_urls' => [
                ['label' => 'BLS International, Spain visa South Africa', 'url' => 'https://southafrica.blsspainvisa.com/'],
                ['label' => 'Department of Home Affairs, travelling with children', 'url' => 'https://www.dha.gov.za/'],
                ['label' => 'European Commission, Schengen visa policy and fees', 'url' => 'https://home-affairs.ec.europa.eu/policies/schengen-borders-and-visa/visa-policy_en'],
            ],
            'reviewed_by' => 'Usama Toheed', 'reviewed_at' => '2026-10-06 18:30:00',
            'verified' => true, 'published' => true,
        ];
    }
}
```

- [ ] **Step 4: Register in `ProductionSeeder`**

In `database/seeders/ProductionSeeder.php`, after the `TurkeyGoldGuidesSeeder::class,` line add:

```php
            MarketDestinationProfileSeeder::class, // verified market x destination profiles for .com (SP3); idempotent
```

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter='MarketCountrySeederTest|MarketCountryProfileModelTest'`
Expected: PASS. If the seeder throws `LogicException: Copy rules violated`, the message names the pattern; fix the wording in the seed, never the rule.

- [ ] **Step 6: Commit**

```bash
git add database/seeders/MarketDestinationProfileSeeder.php database/seeders/ProductionSeeder.php tests/Feature/MarketCountrySeederTest.php
git commit -m "feat(intl): seed verified profiles Canada x Italy (Prenot@mi) and South Africa x Spain (BLS)"
```

---

### Task 5: Controller, routes and the fallback page

**Files:**
- Create: `app/Http/Controllers/Market/MarketCountryController.php`
- Create: `resources/views/market/country-fallback.blade.php`
- Create: `resources/views/market/country.blade.php` (minimal; Task 7 fills it)
- Modify: `routes/web.php` (two routes inside the `/{market}` group)
- Test: `tests/Feature/MarketCountryPageTest.php`, `tests/Feature/Concerns/MakesMarketProfiles.php`

**Interfaces:**
- Produces: route `market.country` (`GET /{market}/schengen-visa/{country}`); test trait `MakesMarketProfiles::liveProfile(string $market, string $slug, array $over = []): MarketDestinationProfile`.

- [ ] **Step 1: Write the test helper and the failing test**

Create `tests/Feature/Concerns/MakesMarketProfiles.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Concerns;

use App\Models\MarketDestinationProfile;
use App\Support\SchengenDestinations;

trait MakesMarketProfiles
{
    protected function liveProfile(string $market, string $slug, array $over = []): MarketDestinationProfile
    {
        $name = SchengenDestinations::nameFromSlug($slug);
        $faqs = [];
        for ($i = 1; $i <= 10; $i++) {
            $faqs[] = ['q' => "Question {$i} about {$name}?", 'a' => "Answer {$i}: the consulate decides, the appointment is free, we prepare the file."];
        }
        $docs = [];
        for ($i = 1; $i <= 10; $i++) {
            $docs[] = ['document' => "Document {$i}", 'detail' => 'What the consulate wants to see for this item.', 'common_mistake' => 'The mistake we see most often with it.'];
        }

        return MarketDestinationProfile::create(array_merge([
            'market' => $market, 'destination_slug' => $slug, 'destination_name' => $name, 'destination_iso' => SchengenDestinations::isoFromSlug($slug),
            'operator' => 'vfs', 'portal_name' => 'VFS Global portal', 'booking_mode' => 'calendar',
            'centres' => [['city' => 'Capital', 'name' => 'VFS Global centre', 'serves' => 'Everyone']],
            'jurisdiction_note' => 'One centre serves the whole market for this destination.',
            'status_rule' => 'Residence status valid for at least three months after you return.',
            'processing_text' => 'Usually within 15 days once lodged; up to 45 in busy periods',
            'appointment_copy' => 'Appointments are free and booked only on the official site, in your name. We watch the calendar with you.',
            'intro' => 'A plain introduction for applicants in this market applying for this destination.',
            'govt_fee_local_adult' => 100, 'govt_fee_local_currency' => 'USD', 'govt_fee_verified_at' => '2026-10-06', 'govt_fee_source_url' => 'https://example.gov/fees',
            'vac_fee_amount' => 40, 'vac_fee_currency' => 'USD', 'vac_fee_note' => 'Service fee per applicant.', 'vac_fee_verified_at' => '2026-10-06', 'vac_fee_source_url' => 'https://example.gov/vac',
            'documents' => $docs, 'faqs' => $faqs,
            'source_urls' => [['label' => 'Official source', 'url' => 'https://example.gov/']],
            'reviewed_by' => 'Test Reviewer', 'reviewed_at' => '2026-10-01 09:00:00',
            'verified' => true, 'published' => true,
        ], $over));
    }
}
```

Create `tests/Feature/MarketCountryPageTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\MakesMarketProfiles;
use Tests\TestCase;

final class MarketCountryPageTest extends TestCase
{
    use MakesMarketProfiles;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['ukv.markets.ca.enabled' => true, 'ukv.markets.ca.indexable' => true, 'ukv.intl_base_url' => 'https://beyondpassports.com', 'ukv.base_url' => 'https://beyondpassports.co.uk']);
    }

    public function test_live_profile_renders_page_with_market_h1(): void
    {
        $this->liveProfile('ca', 'italy');
        $r = $this->get('/ca/schengen-visa/italy');
        $r->assertOk();
        $r->assertSee('<h1>Schengen visa for Italy from Canada</h1>', false);
        $r->assertSee('lang="en-CA"', false);
        $r->assertSee('<link rel="canonical" href="https://beyondpassports.com/ca/schengen-visa/italy">', false);
        $r->assertHeaderMissing('X-Robots-Tag');
    }

    public function test_missing_or_unverified_profile_renders_noindex_fallback_without_data(): void
    {
        $r = $this->get('/ca/schengen-visa/austria');
        $r->assertOk();
        $r->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $r->assertSee('<h1>Schengen visa for Austria from Canada</h1>', false);
        $r->assertSee('check for you');
        $r->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        $r->assertDontSee('EUR 90');
        $r->assertDontSee('VFS Global');

        $this->liveProfile('ca', 'spain', ['verified' => false, 'published' => false, 'reviewed_by' => null, 'reviewed_at' => null]);
        $r2 = $this->get('/ca/schengen-visa/spain');
        $r2->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $r2->assertDontSee('USD');
    }

    public function test_verified_but_unpublished_renders_fallback(): void
    {
        $this->liveProfile('ca', 'italy', ['published' => false]);
        $this->get('/ca/schengen-visa/italy')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertDontSee('USD');
    }

    public function test_unknown_slug_and_disabled_market_404(): void
    {
        $this->get('/ca/schengen-visa/narnia')->assertNotFound();
        config(['ukv.markets.za.enabled' => false]);
        $this->get('/za/schengen-visa/spain')->assertNotFound();
    }

    public function test_staged_market_serves_live_page_with_noindex(): void
    {
        config(['ukv.markets.ca.indexable' => false]);
        $this->liveProfile('ca', 'italy');
        $this->get('/ca/schengen-visa/italy')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertSee('Schengen visa for Italy from Canada');
    }

    public function test_country_pages_never_include_slotboard_or_lp_flow(): void
    {
        config(['ukv.slots.dynamic' => true]);
        $this->liveProfile('ca', 'italy');
        $html = $this->get('/ca/schengen-visa/italy')->getContent();
        $this->assertStringNotContainsString('data-slot-count', $html);
        $this->assertStringNotContainsString('id=mov', $html);
        $this->assertStringNotContainsString('BP_FLOW_DEST', $html);
        $fallback = $this->get('/ca/schengen-visa/austria')->getContent();
        $this->assertStringNotContainsString('id=mov', $fallback);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketCountryPageTest`
Expected: FAIL with 404 on `/ca/schengen-visa/italy`.

- [ ] **Step 3: Create the controller**

Create `app/Http/Controllers/Market/MarketCountryController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Models\MarketDestinationProfile;
use App\Support\Market;
use App\Support\MarketAlternates;
use App\Support\MarketAvailability;
use App\Support\MarketQualification;
use App\Support\SchengenDestinations;
use Illuminate\Http\Response;

/**
 * /{market}/schengen-visa/{country} (spec 5, 6). Renders the full data-driven page only when the
 * profile is verified AND published; every other cell renders the honest noindex fallback with no
 * figures. Never touches SlotBoard, SlotTiles or the UK lp-flow modal.
 */
final class MarketCountryController extends Controller
{
    public function __invoke(string $market, string $country): Response
    {
        $m = Market::current();
        $name = SchengenDestinations::nameFromSlug($country);
        abort_if($name === null, 404);

        $profile = MarketDestinationProfile::findFor($m->code, $country);
        if ($profile === null || ! $profile->isLive()) {
            return response()
                ->view('market.country-fallback', ['market' => $m, 'destination' => $name, 'slug' => $country])
                ->header('X-Robots-Tag', 'noindex, nofollow');
        }

        $siblings = MarketDestinationProfile::live()->forMarket($m->code)
            ->where('destination_slug', '!=', $country)->orderBy('destination_name')->get(['destination_slug', 'destination_name']);
        $otherMarkets = MarketDestinationProfile::live()->where('destination_slug', $country)
            ->where('market', '!=', $m->code)->pluck('market')->all();

        return response()->view('market.country', [
            'market' => $m,
            'profile' => $profile,
            'destination' => $name,
            'tiles' => MarketQualification::tiles($m),
            'etias' => MarketQualification::etiasSplit($m),
            'availability' => MarketAvailability::for($m, $country),
            'alternates' => MarketAlternates::forDestination($country),
            'siblings' => $siblings,
            'otherMarkets' => $otherMarkets,
        ]);
    }
}
```

`MarketAlternates::forDestination()` is created in Task 9. Until then add a temporary stub method to `app/Support/MarketAlternates.php` so this task runs:

```php
    /** @return array<string,string> Replaced in SP3 Task 9. */
    public static function forDestination(string $slug): array
    {
        return [];
    }
```

- [ ] **Step 4: Create the fallback view**

Create `resources/views/market/country-fallback.blade.php`:

```blade
{{-- Honest fallback for a market x destination cell that is not yet verified + published (spec 6.14).
     Same chrome and H1 as the real page, the "we will check for you" block, no operator, centre, fee
     or rule. Noindex by meta and header; excluded from sitemap, hreflang, hub links and llms.txt. --}}
@php
  $wa = $market->chatUrl('Hi Beyond Passports, I am applying for a Schengen visa for '.$destination.' from '.$market->label().'. Please check availability and tell me what my consulate asks for. ['.strtoupper($market->code).']');
@endphp
<!doctype html>
<html lang="{{ $market->locale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="{{ asset('assets/brand/favicon.svg?v=2') }}" type="image/svg+xml">
<title>Schengen Visa for {{ $destination }} from {{ $market->label() }} | Beyond Passports</title>
<meta name="description" content="Schengen visa preparation for {{ $destination }} for applicants in {{ $market->label() }}. We check the official appointment calendar for you and prepare your file to your consulate's checklist.">
<meta name="robots" content="noindex, nofollow">
<link rel="canonical" href="{{ market_url('/schengen-visa/'.$slug, $market) }}">
@include('partials.analytics-head')
<style>
body{margin:0;font-family:"Outfit",system-ui,sans-serif;color:#16222E;background:#F4F5F6}
.cf{max-width:1100px;margin:0 auto;padding:48px 24px 32px}
.cf h1{font-size:clamp(26px,4vw,40px);margin:0 0 12px}
.cf-check{background:#fff;border:1px solid #dde3ec;border-radius:16px;padding:20px;max-width:720px}
.cf-check h2{font-size:18px;margin:0 0 8px}.cf-check p{margin:0 0 12px;line-height:1.5}
.cf-cta{display:inline-block;background:#155E7A;color:#fff;font-weight:700;padding:12px 18px;border-radius:12px;text-decoration:none}
.cf-back{display:block;margin-top:18px;color:#155E7A;font-weight:600}
@media (max-width:560px){.cf{padding-left:16px;padding-right:16px}}
</style>
</head>
<body>
@include('partials.lp-chrome')
<section class="cf">
  <h1>Schengen visa for {{ $destination }} from {{ $market->label() }}</h1>
  <div class="cf-check">
    <h2>We will check for you</h2>
    <p>We publish the operator, the centres, the fees and the document rules for each destination only after a named person has verified them against the consulate's own pages. The {{ $destination }} page for applicants in {{ $market->label() }} has not been through that check yet, so there are no figures here on purpose.</p>
    <p>Tell us your destination, dates and city on WhatsApp. A named consultant checks the current calendar, confirms the fees and tells you what your consulate asks for, with the time we checked.</p>
    <a class="cf-cta" href="{{ $wa }}" target="_blank" rel="noopener">Ask us to check {{ $destination }}</a>
  </div>
  <a class="cf-back" href="{{ market_url('/schengen-visa', $market) }}">All Schengen destinations from {{ $market->label() }}</a>
</section>
@include('partials.market-trust-strip', ['market' => $market])
@include('partials.lp-footer')
@include('partials.utm-capture')
</body>
</html>
```

- [ ] **Step 5: Create a minimal `market/country.blade.php` (Task 7 replaces it)**

```blade
<!doctype html>
<html lang="{{ $market->locale() }}">
<head>
<meta charset="utf-8">
<title>Schengen Visa for {{ $destination }} from {{ $market->label() }} | Beyond Passports</title>
<link rel="canonical" href="{{ market_url($profile->path(), $market) }}">
@include('partials.analytics-head')
</head>
<body>
<h1>Schengen visa for {{ $destination }} from {{ $market->label() }}</h1>
<p>{{ $profile->operatorLabel() }}</p>
@include('partials.utm-capture')
</body>
</html>
```

- [ ] **Step 6: Register the routes inside the market group**

In `routes/web.php`, inside the `->group(function () { ... })` created by the foundation, after the `market.tours` line add:

```php
        // SP3 data-driven country pages (spec docs/superpowers/specs/2026-10-06-sp3-market-country-pages-design.md).
        // The Markdown twin is registered first; the HTML route's regex has no dot so "italy.md" can never match it.
        Route::get('/schengen-visa/{country}.md', \App\Http\Controllers\Market\MarketCountryMarkdownController::class)
            ->where('country', \App\Support\SchengenDestinations::routePattern())->name('market.country.md');
        Route::get('/schengen-visa/{country}', \App\Http\Controllers\Market\MarketCountryController::class)
            ->where('country', \App\Support\SchengenDestinations::routePattern())->name('market.country');
```

The Markdown controller is created in Task 11. Until then create a placeholder `app/Http/Controllers/Market/MarketCountryMarkdownController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;

/** Replaced in SP3 Task 11. */
final class MarketCountryMarkdownController extends Controller
{
    public function __invoke(string $market, string $country)
    {
        abort(404);
    }
}
```

- [ ] **Step 7: Run to verify it passes**

Run: `php artisan test --filter='MarketCountryPageTest|MarketHubPageTest|MarketRoutingTest'`
Expected: PASS. If `/ca/schengen-visa/italy` still 404s, run `php artisan route:list --path=schengen-visa` and confirm both routes appear under the `{market}` prefix; if the `.md` route fails to register, replace its URI with `'/schengen-visa/{country_md}'` and `->where('country_md', '('.\App\Support\SchengenDestinations::routePattern().')\.md')`, then strip the suffix in the controller with `substr($country, 0, -3)`.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/Market/MarketCountryController.php app/Http/Controllers/Market/MarketCountryMarkdownController.php app/Support/MarketAlternates.php resources/views/market/country-fallback.blade.php resources/views/market/country.blade.php routes/web.php tests/Feature/MarketCountryPageTest.php tests/Feature/Concerns/MakesMarketProfiles.php
git commit -m "feat(intl): /{market}/schengen-visa/{country} controller, routes and noindex fallback"
```

---

### Task 6: Fee table partial and availability partial

**Files:**
- Create: `resources/views/partials/market-fee-table.blade.php`
- Create: `resources/views/partials/market-availability.blade.php`
- Test: `tests/Feature/MarketCountryFeeTableTest.php`

**Interfaces:**
- Produces: `@include('partials.market-fee-table', ['market' => $market, 'profile' => $profile])`, `@include('partials.market-availability', ['market' => $market, 'destination' => $name, 'availability' => $availability, 'profile' => $profile])`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MarketCountryFeeTableTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\Feature\Concerns\MakesMarketProfiles;
use Tests\TestCase;

final class MarketCountryFeeTableTest extends TestCase
{
    use MakesMarketProfiles;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['ukv.markets.za.enabled' => true, 'ukv.intl_base_url' => 'https://beyondpassports.com']);
    }

    private function render(array $over = []): string
    {
        $p = $this->liveProfile('za', 'spain', array_merge(['operator' => 'bls', 'portal_name' => 'BLS Spain Visa portal', 'govt_fee_local_adult' => 1775, 'govt_fee_local_currency' => 'ZAR', 'vac_fee_amount' => 333, 'vac_fee_currency' => 'ZAR'], $over));

        return View::make('partials.market-fee-table', ['market' => \App\Support\Market::fromCode('za'), 'profile' => $p])->render();
    }

    public function test_government_rows_and_vac_row_are_server_rendered_with_dates(): void
    {
        $html = $this->render();
        $this->assertStringContainsString('EUR 90', $html);
        $this->assertStringContainsString('EUR 45', $html);
        $this->assertStringContainsString('Free', $html);
        $this->assertStringContainsString('ZAR 1,775', $html);
        $this->assertStringContainsString('ZAR 333', $html);
        $this->assertStringContainsString('BLS International', $html);
        $this->assertStringContainsString('verified 6 Oct 2026', $html);
        $this->assertStringContainsString('href="https://example.gov/vac"', $html);
        $this->assertStringContainsString('apply without us', $html);
    }

    public function test_fee_table_handles_missing_local_government_fee(): void
    {
        $html = $this->render(['govt_fee_local_adult' => null, 'govt_fee_local_currency' => null]);
        $this->assertStringContainsString('EUR 90', $html);
        $this->assertStringNotContainsString('ZAR 0', $html);
        $this->assertStringNotContainsString('ZAR  ', $html);
    }

    public function test_zero_vac_fee_shows_the_note_not_a_zero(): void
    {
        $html = $this->render(['operator' => 'embassy_direct', 'portal_name' => 'Prenot@mi', 'vac_fee_amount' => 0, 'vac_fee_note' => 'No visa centre fee.']);
        $this->assertStringContainsString('No visa centre fee.', $html);
        $this->assertStringNotContainsString('ZAR 0', $html);
    }

    public function test_service_fee_hidden_when_null_and_shown_when_set(): void
    {
        $this->assertStringNotContainsString('our service fee', $this->render());
        config(['ukv.markets.za.price_total' => 2490, 'ukv.markets.za.price_upfront' => 750, 'ukv.markets.za.price_remainder' => 1740]);
        $html = $this->render(['destination_slug' => 'spain']);
        $this->assertStringContainsString('R2,490', $html);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketCountryFeeTableTest`
Expected: FAIL (view not found).

- [ ] **Step 3: Create the fee table partial**

Create `resources/views/partials/market-fee-table.blade.php`:

```blade
{{-- Server-rendered government + visa-centre fee table (spec 6.4). Every local figure prints the
     date it was verified and links its source. The service fee comes only from partials.market-price
     (null-safe). VisaHQ's fee cells are JS-empty; Atlys and iVisa have no table: this is the edge. --}}
@php
  $fmt = fn (string $cur, $v) => $cur.' '.number_format((float) $v, (float) $v == floor((float) $v) ? 0 : 2);
  $d = fn ($date) => $date ? $date->format('j M Y') : null;
  $hasLocal = $profile->govt_fee_local_adult !== null && $profile->govt_fee_local_currency;
  $hasVac = $profile->vac_fee_amount !== null && (float) $profile->vac_fee_amount > 0 && $profile->vac_fee_currency;
@endphp
<section class="mft" aria-label="What a {{ $profile->destination_name }} visa costs from {{ $market->label() }}">
  <table class="mft-t">
    <thead><tr><th>Item</th><th>Amount</th><th>Paid to</th><th>Notes</th></tr></thead>
    <tbody>
      <tr>
        <td>Government visa fee, adult</td>
        <td><b>EUR {{ $profile->govt_fee_eur_adult }}</b>@if ($hasLocal)<br><small>{{ $fmt($profile->govt_fee_local_currency, $profile->govt_fee_local_adult) }}, verified {{ $d($profile->govt_fee_verified_at) }}</small>@endif</td>
        <td>The consulate, at your appointment</td>
        <td>Set by EU law, the same in every market.@if ($profile->govt_fee_source_url) <a href="{{ $profile->govt_fee_source_url }}" rel="noopener nofollow" target="_blank">Source</a>@endif</td>
      </tr>
      <tr><td>Government visa fee, child 6 to 11</td><td><b>EUR {{ $profile->govt_fee_eur_child }}</b></td><td>The consulate</td><td>Half the adult fee.</td></tr>
      <tr><td>Government visa fee, under 6</td><td><b>Free</b></td><td>The consulate</td><td>No visa fee for children under six.</td></tr>
      <tr>
        <td>Visa centre fee</td>
        <td>@if ($hasVac)<b>{{ $fmt($profile->vac_fee_currency, $profile->vac_fee_amount) }}</b><br><small>verified {{ $d($profile->vac_fee_verified_at) }}</small>@else<b>None</b>@endif</td>
        <td>{{ ucfirst($profile->operatorLabel()) }}</td>
        <td>{{ $profile->vac_fee_note }}@if ($profile->vac_fee_source_url) <a href="{{ $profile->vac_fee_source_url }}" rel="noopener nofollow" target="_blank">Source</a>@endif</td>
      </tr>
    </tbody>
  </table>
  @include('partials.market-price', ['market' => $market])
  <p class="mft-direct">You can apply without us on the official portal for the government and centre fees alone. Our fee, when shown above, is for preparation and review only and is never collected on the consulate's behalf.</p>
</section>
@once
<style>
.mft{max-width:1100px;margin:0 auto;padding:0 24px 8px}
.mft-t{width:100%;border-collapse:collapse;background:#fff;border:1px solid #dde3ec;border-radius:14px;overflow:hidden;font:500 14px/1.5 "Outfit",system-ui,sans-serif}
.mft-t th,.mft-t td{padding:12px 14px;text-align:left;vertical-align:top;border-bottom:1px solid #eef2f6}
.mft-t th{background:#F4F5F6;font-weight:700}.mft-t small{color:#5d6b76}
.mft-direct{font:500 13px/1.5 "Outfit",system-ui,sans-serif;color:#5d6b76;max-width:760px;margin:12px auto 0}
@media (max-width:640px){.mft{padding:0 16px}.mft-t thead{display:none}.mft-t tr{display:block;border-bottom:1px solid #dde3ec}.mft-t td{display:block;border:0;padding:6px 14px}.mft-t td:first-child{font-weight:700;padding-top:12px}}
</style>
@endonce
```

- [ ] **Step 4: Create the availability partial**

Create `resources/views/partials/market-availability.blade.php`:

```blade
{{-- Availability block (spec 6.10). Null snapshot = honest "we will check for you" block. SP4 passes a
     snapshot array and this partial prints earliest date seen + last checked by name. Never SlotBoard. --}}
@php
  $wa = $market->chatUrl('Hi Beyond Passports, please check '.$destination.' appointment availability for me. I am applying from '.$market->label().'. ['.strtoupper($market->code).']');
@endphp
<section class="mav" id="availability" aria-label="Appointment availability">
  <h2>{{ $destination }} appointment availability from {{ $market->label() }}</h2>
  @if (is_array($availability) && ! empty($availability['last_checked_at']))
    <p class="mav-row"><b>Earliest date seen:</b> {{ $availability['earliest_seen'] ?: 'none in the current calendar' }}. <b>Last checked:</b> {{ $availability['last_checked_at'] }} by {{ $availability['checked_by'] }}. Checked by a person, not a bot. Dates move within minutes; a date shown here can be gone before you log in.</p>
    <p class="mav-row">Appointments are free on the official site: <a href="{{ $availability['official_url'] }}" rel="noopener nofollow" target="_blank">{{ $profile->portalLabel() }}</a>.</p>
  @else
    <p class="mav-row">Appointments are released by the consulate and booked on {{ $profile->portalLabel() }} in your name. Nobody can create availability or buy an earlier date. Tell us your travel window and a named consultant checks the current calendar and tells you what we see, with the time we saw it.</p>
  @endif
  <a class="mav-cta" href="{{ $wa }}" target="_blank" rel="noopener">Ask us to check {{ $destination }} dates</a>
</section>
@once
<style>
.mav{max-width:1100px;margin:28px auto;padding:0 24px}
.mav h2{font-size:22px;margin:0 0 10px}.mav-row{max-width:760px;line-height:1.55;margin:0 0 10px;font:500 15px "Outfit",system-ui,sans-serif}
.mav-cta{display:inline-block;background:#155E7A;color:#fff;font-weight:700;padding:12px 18px;border-radius:12px;text-decoration:none}
@media (max-width:560px){.mav{padding:0 16px}}
</style>
@endonce
```

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter=MarketCountryFeeTableTest`
Expected: PASS (4 tests). If `verified 6 Oct 2026` fails on the date format, confirm the `date` cast on `govt_fee_verified_at`/`vac_fee_verified_at` is in the model's `casts()`.

- [ ] **Step 6: Commit**

```bash
git add resources/views/partials/market-fee-table.blade.php resources/views/partials/market-availability.blade.php tests/Feature/MarketCountryFeeTableTest.php
git commit -m "feat(intl): server-rendered fee table with verified dates; availability block with SP4 seam"
```

---

### Task 7: The full country template

**Files:**
- Modify: `resources/views/market/country.blade.php` (replace the minimal file)
- Test: `tests/Feature/MarketCountryContentTest.php`

**Interfaces:**
- Consumes: variables passed by `MarketCountryController` (Task 5), partials from Tasks 6 and the foundation, `partials.market-country-schema` (Task 8; stubbed here).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MarketCountryContentTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\CopyRules;
use Database\Seeders\MarketDestinationProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\MakesMarketProfiles;
use Tests\TestCase;

final class MarketCountryContentTest extends TestCase
{
    use MakesMarketProfiles;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'ukv.markets.ca.enabled' => true, 'ukv.markets.ca.indexable' => true,
            'ukv.markets.za.enabled' => true, 'ukv.markets.za.indexable' => true,
            'ukv.intl_base_url' => 'https://beyondpassports.com', 'ukv.base_url' => 'https://beyondpassports.co.uk',
        ]);
    }

    private static function words(string $html): int
    {
        $html = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', ' ', $html);

        return str_word_count(html_entity_decode(strip_tags($html)));
    }

    public function test_section_stack_is_present_in_order(): void
    {
        $this->liveProfile('ca', 'italy');
        $html = $this->get('/ca/schengen-visa/italy')->getContent();
        $order = [
            '<h1>Schengen visa for Italy from Canada</h1>',
            'Companies House',
            'Who this page is for',
            'What a Italy visa costs',
            'How appointments work',
            'Six steps, and who does what',
            'Common mistake',
            'The pack you receive',
            'What we do and what we do not',
            'appointment availability',
            'Refused before?',
            'Asked every week',
            'How we reviewed this page',
            'disc-strip',
        ];
        $pos = -1;
        foreach ($order as $needle) {
            $next = stripos($html, $needle);
            $this->assertNotFalse($next, "missing section marker: $needle");
            $this->assertGreaterThan($pos, $next, "out of order: $needle");
            $pos = $next;
        }
    }

    public function test_documents_faqs_tiles_and_reviewer_render_from_data(): void
    {
        $this->liveProfile('ca', 'italy');
        $r = $this->get('/ca/schengen-visa/italy');
        $r->assertSee('Document 1')->assertSee('Document 10')->assertSee('The mistake we see most often with it.');
        $r->assertSee('Question 1 about Italy?')->assertSee('Question 10 about Italy?');
        $r->assertSee('PR card valid 3+ months after you return');
        $r->assertSee('ETIAS');
        $r->assertSee('Reviewed by Test Reviewer on 1 Oct 2026');
        $r->assertSee('href="https://example.gov/"', false);
        $r->assertSee('VFS Global portal');
        $r->assertSee(rawurlencode('[CA]'), false);
    }

    public function test_za_page_has_no_etias_split_and_uae_never_offers_a_call(): void
    {
        $this->liveProfile('za', 'spain');
        $this->get('/za/schengen-visa/spain')->assertOk()->assertDontSee('ETIAS');
        config(['ukv.markets.ae.enabled' => true]);
        $this->liveProfile('ae', 'germany');
        $this->get('/ae/schengen-visa/germany')->assertOk()->assertDontSee('WhatsApp call');
    }

    public function test_seeded_pages_pass_copy_rules_and_depth_floor(): void
    {
        $this->seed(MarketDestinationProfileSeeder::class);
        foreach (['/ca/schengen-visa/italy', '/za/schengen-visa/spain'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertSame([], CopyRules::violations($html), "copy rule violation on $path");
            $this->assertGreaterThanOrEqual(2200, self::words($html), "depth floor on $path");
        }
    }

    public function test_fallback_passes_copy_rules(): void
    {
        $html = $this->get('/ca/schengen-visa/austria')->getContent();
        $this->assertSame([], CopyRules::violations($html));
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketCountryContentTest`
Expected: FAIL (section markers missing).

- [ ] **Step 3: Replace `resources/views/market/country.blade.php`**

```blade
{{-- Data-driven market x destination page (spec 6). Standalone page in the foundation pattern. Every
     fact comes from $profile (a verified + published MarketDestinationProfile); market copy comes from
     Market and MarketQualification. No SlotBoard, no SlotTiles, no lp-flow (its tiers are UK-only). --}}
@php
  $code = strtoupper($market->code);
  $waCheck = $market->chatUrl('Hi Beyond Passports, please check '.$destination.' appointment availability for me. I am applying from '.$market->label().'. ['.$code.']');
  $waStart = $market->chatUrl('Hi Beyond Passports, I want to start my Schengen visa file for '.$destination.' from '.$market->label().'. ['.$code.']');
  $waRefused = $market->chatUrl('Hi Beyond Passports, I was refused a Schengen visa before and want to apply for '.$destination.' from '.$market->label().'. Can you review my refusal letter? ['.$code.']');
  $reviewed = $profile->reviewed_at?->format('j M Y');
  $desc = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', (string) $profile->intro)), 150, '');
  $ukSlugs = \App\Support\MarketAlternates::UK_COUNTRY_SLUGS;
@endphp
<!doctype html>
<html lang="{{ $market->locale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="{{ asset('assets/brand/favicon.svg?v=2') }}" type="image/svg+xml">
<title>Schengen Visa for {{ $destination }} from {{ $market->label() }}: {{ $profile->portalLabel() }}, fees, documents | Beyond Passports</title>
<meta name="description" content="{{ $desc }}">
<link rel="canonical" href="{{ market_url($profile->path(), $market) }}">
@if (! $market->isIndexable())<meta name="robots" content="noindex, nofollow">@endif
@include('partials.hreflang', ['alternates' => $alternates])
@include('partials.analytics-head')
@include('partials.market-country-schema', ['market' => $market, 'profile' => $profile, 'destination' => $destination])
<style>
body{margin:0;font-family:"Outfit",system-ui,sans-serif;color:#16222E;background:#F4F5F6}
.wrap{max-width:1100px;margin:0 auto;padding:0 24px}
section.blk{padding:28px 0}
h2{font-size:clamp(20px,3vw,28px);margin:0 0 12px}
p,li{line-height:1.55}
.hero{padding:48px 0 20px}.hero h1{font-size:clamp(28px,4.4vw,44px);line-height:1.08;letter-spacing:-.02em;margin:0 0 12px}
.hero .sub{font-size:18px;max-width:64ch;margin:0 0 18px;color:#2a3a47}
.cta{display:inline-block;background:#155E7A;color:#fff;font-weight:700;padding:14px 22px;border-radius:14px;text-decoration:none;margin:0 10px 10px 0}
.cta.alt{background:#fff;color:#155E7A;border:1.5px solid #155E7A}
.chips{display:flex;gap:10px;flex-wrap:wrap;list-style:none;margin:0;padding:0}
.chips li{background:#fff;border:1px solid #dde3ec;border-radius:999px;padding:8px 14px;font-size:13.5px;font-weight:600}
.chips a{color:inherit;text-decoration:none}
.card{background:#fff;border:1px solid #dde3ec;border-radius:16px;padding:18px 20px}
.tiles{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;list-style:none;margin:14px 0 0;padding:0}
.tiles li{background:#fff;border:1px solid #dde3ec;border-left-width:4px;border-radius:12px;padding:12px 14px;font-size:14px}
.tiles li b{display:block;margin-bottom:4px}
.tiles .green{border-left-color:#166534}.tiles .amber{border-left-color:#b45309}.tiles .red{border-left-color:#b91c1c}
.steps{counter-reset:s;list-style:none;margin:0;padding:0;display:grid;gap:12px}
.steps li{background:#fff;border:1px solid #dde3ec;border-radius:14px;padding:14px 16px 14px 56px;position:relative}
.steps li::before{counter-increment:s;content:counter(s);position:absolute;left:16px;top:14px;width:28px;height:28px;border-radius:50%;background:#155E7A;color:#fff;font-weight:800;display:flex;align-items:center;justify-content:center}
.steps li b{display:block;font-size:13px;letter-spacing:.06em;text-transform:uppercase;color:#5d6b76;margin-bottom:2px}
table.docs{width:100%;border-collapse:collapse;background:#fff;border:1px solid #dde3ec;border-radius:14px;overflow:hidden;font-size:14px}
table.docs th,table.docs td{padding:12px 14px;text-align:left;vertical-align:top;border-bottom:1px solid #eef2f6}
table.docs th{background:#F4F5F6}
.two{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.pack{list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px}
.pack li{background:#fff;border:1px solid #dde3ec;border-radius:12px;padding:12px 14px;font-size:14px}
details{background:#fff;border:1px solid #dde3ec;border-radius:12px;padding:12px 16px;margin-bottom:10px}
summary{font-weight:700;cursor:pointer}
details p{margin:10px 0 0}
.centres{list-style:none;margin:12px 0 0;padding:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:10px}
.centres li{background:#fff;border:1px solid #dde3ec;border-radius:12px;padding:12px 14px;font-size:14px}
.quote{border-left:4px solid #155E7A;padding:8px 14px;margin:14px 0;background:#fff;border-radius:0 12px 12px 0;font-style:italic}
.sib{display:flex;gap:10px;flex-wrap:wrap}.sib a{color:#155E7A;font-weight:600}
.small{font-size:13px;color:#5d6b76}
@media (max-width:760px){.two{grid-template-columns:1fr}}
@media (max-width:640px){.wrap{padding:0 16px}table.docs thead{display:none}table.docs tr{display:block;border-bottom:1px solid #dde3ec}table.docs td{display:block;border:0;padding:6px 14px}table.docs td:first-child{font-weight:700;padding-top:12px}}
</style>
</head>
<body>
@include('partials.lp-chrome')

{{-- 1 Hero --}}
<section class="hero"><div class="wrap">
  <h1>Schengen visa for {{ $destination }} from {{ $market->label() }}</h1>
  <p class="sub">Applications from {{ $market->label() }} go through {{ $profile->operatorLabel() }}@if ($profile->portal_name) using {{ $profile->portal_name }}@endif. Appointments are free and booked in your own name. A named consultant prepares your file to your consulate's checklist and stays on WhatsApp while you book.</p>
  <a class="cta" href="{{ $waCheck }}" target="_blank" rel="noopener">Check {{ $destination }} availability</a>
  <a class="cta alt" href="{{ $waStart }}" target="_blank" rel="noopener">Start my file</a>
</div></section>

{{-- 2 Trust chips --}}
<section class="blk" style="padding-top:8px"><div class="wrap">
  <ul class="chips" aria-label="Who we are">
    <li><a href="https://find-and-update.company-information.service.gov.uk/company/{{ config('ukv.address.company_no') ?: '17331903' }}" target="_blank" rel="noopener">Companies House {{ config('ukv.address.company_no') ?: '17331903' }}</a></li>
    <li>ICO registered {{ config('ukv.compliance.ico_number') ?: 'ZC197159' }}</li>
    <li>Appointments are free. We never sell one.</li>
    <li>Support: {{ $market->supportHours() }}</li>
  </ul>
</div></section>

{{-- 3 Who this page is for --}}
<section class="blk"><div class="wrap">
  <h2>Who this page is for</h2>
  @if ($etias)<p class="card">{{ $etias }}</p>@endif
  <p>{!! nl2br(e($profile->intro)) !!}</p>
  <div class="card"><b>The residence rule {{ $destination }} applies to applicants in {{ $market->label() }}:</b><p style="margin:8px 0 0">{{ $profile->status_rule }}</p></div>
  @if ($tiles !== [])
  <ul class="tiles">
    @foreach ($tiles as $t)<li class="{{ $t['verdict'] }}"><b>{{ $t['label'] }}</b>{{ $t['note'] }}</li>@endforeach
  </ul>
  @endif
</div></section>

{{-- 4 Fee table --}}
<section class="blk"><div class="wrap"><h2>What a {{ $destination }} visa costs from {{ $market->label() }}</h2></div>
  @include('partials.market-fee-table', ['market' => $market, 'profile' => $profile])
</section>

{{-- 5 Appointments --}}
<section class="blk"><div class="wrap">
  <h2>How appointments work for {{ $destination }} in {{ $market->label() }}</h2>
  <p>{!! nl2br(e($profile->appointment_copy)) !!}</p>
  <p><b>Where you apply.</b> {{ $profile->jurisdiction_note }}</p>
  @if (is_array($profile->centres) && $profile->centres !== [])
  <ul class="centres">
    @foreach ($profile->centres as $c)<li><b>{{ $c['city'] ?? '' }}</b><br>{{ $c['name'] ?? '' }}<br><span class="small">Serves: {{ $c['serves'] ?? '' }}</span></li>@endforeach
  </ul>
  @endif
  <p class="small">Processing once lodged: {{ $profile->processing_text }}. The appointment is the bottleneck, not the decision.</p>
</div></section>

{{-- 6 Process --}}
<section class="blk"><div class="wrap">
  <h2>Six steps, and who does what</h2>
  <ol class="steps">
    <li><b>You</b>Message us with your destination, travel dates, the city you live in and your residence status. Nothing is charged for this.</li>
    <li><b>We</b>Route you to the right post for your address, confirm the residence rule applies to you, and build your checklist to that consulate's requirements.</li>
    <li><b>We</b>Prepare the application form for you to check and sign, write the cover letter, and review your real flight, accommodation and insurance documents line by line. We never create reservations for visa purposes.</li>
    <li><b>We and you</b>{{ $profile->bookingStepText() }}</li>
    <li><b>You</b>Attend {{ is_array($profile->centres) && isset($profile->centres[0]['name']) ? $profile->centres[0]['name'] : 'the centre' }} with the appointment-day pack: an ordered file, what to bring, what to say, and a copy of every document.</li>
    <li><b>The consulate, then you</b>The consulate decides, {{ lcfirst($profile->processing_text) }}. Your passport comes back with the decision and you travel.</li>
  </ol>
</div></section>

{{-- 7 Documents table --}}
<section class="blk"><div class="wrap">
  <h2>Documents for {{ $destination }} from {{ $market->label() }}</h2>
  <table class="docs">
    <thead><tr><th>Document</th><th>What the consulate wants</th><th>Common mistake</th></tr></thead>
    <tbody>
      @foreach ((array) $profile->documents as $d)
      <tr><td>{{ $d['document'] ?? '' }}</td><td>{{ $d['detail'] ?? '' }}</td><td>{{ $d['common_mistake'] ?? '' }}</td></tr>
      @endforeach
    </tbody>
  </table>
</div></section>

{{-- 8 Deliverables --}}
<section class="blk"><div class="wrap">
  <h2>The pack you receive</h2>
  <p class="small">Illustrative. The full content is prepared for your case once work starts.</p>
  <ul class="pack">
    <li>Eligibility and consulate-selection note</li>
    <li>Your one-page personal checklist</li>
    <li>Completed application form, for you to check and sign</li>
    <li>Cover letter</li>
    <li>Day-by-day itinerary matched to your real bookings</li>
    <li>Insurance specification sheet: what the policy must say</li>
    <li>Pre-submission audit report with written corrections</li>
    <li>Appointment-day pack</li>
    <li>Tracking note until the decision</li>
  </ul>
</div></section>

{{-- 9 What we do and what we do not --}}
<section class="blk"><div class="wrap two">
  <div class="card">
    <h2>What we do and what we do not</h2>
    <p>We do not sell, hold or block-book appointments. Appointments are free and are booked only on the official {{ $profile->operatorLabel() === 'the consulate directly' || $profile->operatorLabel() === 'the consulate by email' ? 'consulate' : $profile->operatorLabel() }} site, in your name. We do not run bots on your account and we never ask for your login. We cannot create availability, promise a date or speed up the consulate's decision, and nobody else can either. We are not affiliated with VFS Global, TLScontact, BLS International, Capago, Global Visa Center World, any embassy, consulate or government.</p>
    <p>What we do: make sure every document is ready before a date appears, watch availability for your route, tell you the moment it moves, and stay on WhatsApp while you book.</p>
  </div>
  <div class="card">
    <h2>What you pay for</h2>
    <p>What you pay us is for our work, not for the decision. The decision belongs to the consulate alone. The first part of our fee is refunded in full if we tell you after the free check that we cannot take your case; once your checklist, form or letter is started it is not refundable. The second part is due only when an appointment date is confirmed on the official site; if no appointment can be secured before your travel date and you choose to stop, you owe nothing further. Government and visa-centre fees are paid by you directly and are never refunded by the consulate, whatever the outcome. Your statutory rights are not affected.</p>
  </div>
</div></section>

{{-- 10 Availability --}}
@include('partials.market-availability', ['market' => $market, 'destination' => $destination, 'availability' => $availability, 'profile' => $profile])

{{-- 11 Refusal recovery --}}
<section class="blk"><div class="wrap card">
  <h2>Refused before?</h2>
  <p>A Schengen refusal is recorded for five years and every later application is read against it. Send us the refusal letter. We read the ground cited (the common ones are purpose and conditions of stay, means of subsistence, unreliable information, intention to leave, and insurance), tell you honestly whether a new application is sensible now, and rebuild the file around the gap. Our fee is for the work, not the decision; if a refusal letter cites an error that was ours, we redo the file for your re-application at no charge.</p>
  <a class="cta" href="{{ $waRefused }}" target="_blank" rel="noopener">Review my refusal</a>
</div></section>

{{-- 12 FAQ --}}
@if (is_array($profile->faqs) && $profile->faqs !== [])
<section class="blk"><div class="wrap">
  <h2>Asked every week by applicants in {{ $market->label() }}</h2>
  @foreach ($profile->faqs as $f)
  <details><summary>{{ $f['q'] ?? '' }}</summary><p>{{ $f['a'] ?? '' }}</p></details>
  @endforeach
</div></section>
@endif

{{-- 13 How we reviewed this page --}}
<section class="blk"><div class="wrap">
  <h2>How we reviewed this page</h2>
  <p>Reviewed by {{ $profile->reviewed_by }} on {{ $reviewed }}. Every fee, centre and rule above was read from the sources below on that date. If a source changes, the page is corrected and this date moves.</p>
  <ul>
    @foreach ((array) $profile->source_urls as $s)<li><a href="{{ $s['url'] ?? '#' }}" rel="noopener nofollow" target="_blank">{{ $s['label'] ?? ($s['url'] ?? '') }}</a></li>@endforeach
  </ul>
  {{-- SP2 reserves this slot for the visa-led trips block for {{ $destination }}. --}}
</div></section>

{{-- 14 Siblings --}}
@if ($siblings->isNotEmpty() || $otherMarkets !== [] || in_array($profile->destination_slug, $ukSlugs, true))
<section class="blk"><div class="wrap">
  <h2>Related pages</h2>
  @if ($siblings->isNotEmpty())
  <p class="sib"><span>Other destinations from {{ $market->label() }}:</span>@foreach ($siblings as $s)<a href="{{ market_url('/schengen-visa/'.$s->destination_slug, $market) }}">{{ $s->destination_name }}</a>@endforeach</p>
  @endif
  <p class="sib"><span>{{ $destination }} from elsewhere:</span>
    @if (in_array($profile->destination_slug, $ukSlugs, true))<a href="{{ rtrim(\App\Support\Market::uk()->baseUrl(), '/') }}/schengen-visa/{{ $profile->destination_slug }}">United Kingdom</a>@endif
    @foreach ($otherMarkets as $om)@php($mm = \App\Support\Market::fromCode($om))@if ($mm->isEnabled())<a href="{{ market_url('/schengen-visa/'.$profile->destination_slug, $mm) }}">{{ $mm->label() }}</a>@endif @endforeach
  </p>
</div></section>
@endif

{{-- 15 Trust strip (wraps the locked disclaimer strip) --}}
@include('partials.market-trust-strip', ['market' => $market])
@include('partials.lp-footer')
@include('partials.utm-capture')
</body>
</html>
```

Create a stub `resources/views/partials/market-country-schema.blade.php` so the page renders before Task 8:

```blade
{{-- JSON-LD graph; implemented in SP3 Task 8 --}}
```

Add the UK slug constant to `app/Support/MarketAlternates.php` (used by the template; Task 9 uses it too):

```php
    /** UK gold country pages that exist as static files (mirrors the routes/web.php whitelist). */
    public const UK_COUNTRY_SLUGS = ['france', 'spain', 'netherlands', 'germany', 'italy', 'switzerland', 'belgium'];
```

- [ ] **Step 4: Run to verify it passes**

Run: `php artisan test --filter='MarketCountryContentTest|MarketCountryPageTest'`
Expected: PASS. If `test_seeded_pages_pass_copy_rules_and_depth_floor` fails on the floor, the failure message prints the count; extend the seed's FAQ answers (never the template's filler) until both pages exceed 2,200.

- [ ] **Step 5: Commit**

```bash
git add resources/views/market/country.blade.php resources/views/partials/market-country-schema.blade.php app/Support/MarketAlternates.php tests/Feature/MarketCountryContentTest.php
git commit -m "feat(intl): full data-driven market x destination template (15-section stack)"
```

---

### Task 8: JSON-LD schema set

> **Spec 17 addendum (2026-10-06):** the schema test must also assert that the AE `Offer`, when a price is set, carries only `price` and `priceCurrency` (no VAT wording, no `priceSpecification` tax fields) until Beyond Passports is UAE VAT-registered.

**Files:**
- Modify: `resources/views/partials/market-country-schema.blade.php` (replace stub)
- Test: `tests/Feature/MarketCountrySchemaTest.php`

**Interfaces:**
- Produces: one `<script type="application/ld+json">` with an `@graph` of Organization, Service (+ Offer when priced), BreadcrumbList, FAQPage, WebPage.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MarketCountrySchemaTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\MakesMarketProfiles;
use Tests\TestCase;

final class MarketCountrySchemaTest extends TestCase
{
    use MakesMarketProfiles;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['ukv.markets.ca.enabled' => true, 'ukv.markets.ca.indexable' => true, 'ukv.intl_base_url' => 'https://beyondpassports.com']);
        $this->liveProfile('ca', 'italy');
    }

    /** @return array<string,array<string,mixed>> type => node */
    private function graph(): array
    {
        $html = $this->get('/ca/schengen-visa/italy')->getContent();
        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $m);
        $this->assertNotEmpty($m, 'no JSON-LD block');
        $json = json_decode($m[1], true);
        $this->assertIsArray($json['@graph'] ?? null);
        $out = [];
        foreach ($json['@graph'] as $node) {
            $out[$node['@type']] = $node;
        }

        return $out;
    }

    public function test_organization_has_company_and_ico_identifiers(): void
    {
        $g = $this->graph();
        $ids = array_column($g['Organization']['identifier'], 'value');
        $this->assertContains('17331903', $ids);
        $this->assertContains('ZC197159', $ids);
        $this->assertSame('https://beyondpassports.com#org', $g['Organization']['@id']);
    }

    public function test_service_breadcrumb_faq_and_webpage(): void
    {
        $g = $this->graph();
        $this->assertSame('Canada', $g['Service']['areaServed']['name']);
        $this->assertArrayNotHasKey('offers', $g['Service']);
        $this->assertCount(3, $g['BreadcrumbList']['itemListElement']);
        $this->assertSame('https://beyondpassports.com/ca/schengen-visa/italy', $g['BreadcrumbList']['itemListElement'][2]['item']);
        $this->assertCount(10, $g['FAQPage']['mainEntity']);
        $this->assertSame('2026-10-01T09:00:00+00:00', $g['WebPage']['dateModified']);
        $this->assertSame('Test Reviewer', $g['WebPage']['reviewedBy']['name']);
        $this->assertArrayNotHasKey('AggregateRating', $g);
    }

    public function test_offer_appears_only_when_price_is_set(): void
    {
        config(['ukv.markets.ca.price_total' => 249]);
        $g = $this->graph();
        $this->assertSame('249', (string) $g['Service']['offers']['price']);
        $this->assertSame('CAD', $g['Service']['offers']['priceCurrency']);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketCountrySchemaTest`
Expected: FAIL ("no JSON-LD block").

- [ ] **Step 3: Replace the partial**

Replace `resources/views/partials/market-country-schema.blade.php` with:

```blade
{{-- Schema set for a market x destination page (spec 7): Organization with Companies House + ICO
     identifiers, Service (+ Offer only when a price is set), BreadcrumbList, FAQPage, WebPage with
     dateModified = reviewed_at. No AggregateRating until real per-market reviews exist. --}}
@php
  $base = rtrim((string) config('ukv.intl_base_url'), '/');
  $url = market_url($profile->path(), $market);
  $org = [
    '@type' => 'Organization', '@id' => $base.'#org', 'name' => 'Beyond Passports Ltd', 'url' => $base,
    'identifier' => [
      ['@type' => 'PropertyValue', 'propertyID' => 'Companies House', 'value' => (string) (config('ukv.address.company_no') ?: '17331903')],
      ['@type' => 'PropertyValue', 'propertyID' => 'ICO registration', 'value' => (string) (config('ukv.compliance.ico_number') ?: 'ZC197159')],
    ],
    'areaServed' => ['@type' => 'Country', 'name' => $market->label()],
  ];
  $service = [
    '@type' => 'Service', 'name' => 'Schengen visa preparation for '.$destination.' from '.$market->label(),
    'serviceType' => 'Schengen visa document preparation and appointment guidance',
    'provider' => ['@id' => $base.'#org'],
    'areaServed' => ['@type' => 'Country', 'name' => $market->label()],
    'audience' => ['@type' => 'Audience', 'audienceType' => 'Residents of '.$market->label().' applying for a Schengen visa for '.$destination],
    'url' => $url,
  ];
  if ($market->priceTotal() !== null) {
    $service['offers'] = ['@type' => 'Offer', 'price' => number_format($market->priceTotal(), 0, '.', ''), 'priceCurrency' => $market->currency(), 'url' => $url];
  }
  $crumbs = ['@type' => 'BreadcrumbList', 'itemListElement' => [
    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beyond Passports '.$market->label(), 'item' => market_url('/', $market)],
    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Schengen visa from '.$market->label(), 'item' => market_url('/schengen-visa', $market)],
    ['@type' => 'ListItem', 'position' => 3, 'name' => $destination, 'item' => $url],
  ]];
  $faq = ['@type' => 'FAQPage', 'mainEntity' => array_values(array_map(fn ($f) => [
    '@type' => 'Question', 'name' => (string) ($f['q'] ?? ''), 'acceptedAnswer' => ['@type' => 'Answer', 'text' => (string) ($f['a'] ?? '')],
  ], (array) $profile->faqs))];
  $page = [
    '@type' => 'WebPage', 'url' => $url, 'name' => 'Schengen visa for '.$destination.' from '.$market->label(), 'inLanguage' => $market->locale(),
    'dateModified' => $profile->reviewed_at?->toIso8601String(), 'reviewedBy' => ['@type' => 'Person', 'name' => (string) $profile->reviewed_by],
    'isPartOf' => ['@type' => 'WebSite', 'url' => $base, 'publisher' => ['@id' => $base.'#org']],
  ];
  $graph = ['@context' => 'https://schema.org', '@graph' => [$org, $service, $crumbs, $faq, $page]];
@endphp
<script type="application/ld+json">{!! json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
```

- [ ] **Step 4: Run to verify it passes**

Run: `php artisan test --filter=MarketCountrySchemaTest`
Expected: PASS (3 tests). If `dateModified` differs by timezone, `phpunit.xml` or `config/app.php` timezone is not UTC; assert with `Carbon::parse('2026-10-01 09:00:00')->toIso8601String()` instead.

- [ ] **Step 5: Commit**

```bash
git add resources/views/partials/market-country-schema.blade.php tests/Feature/MarketCountrySchemaTest.php
git commit -m "feat(seo): Organization/Service/Breadcrumb/FAQPage/WebPage graph on market country pages"
```

---

### Task 9: hreflang per destination with UK reciprocity

**Files:**
- Modify: `app/Support/MarketAlternates.php` (real `forDestination`)
- Modify: `resources/views/partials/hreflang.blade.php` (accept `alternates`)
- Modify: `app/Support/LpAssembler.php` (`$headExtra`)
- Modify: `routes/web.php` (UK gold route passes hreflang)
- Test: `tests/Feature/MarketCountrySeoTest.php`, extend `tests/Feature/LpAssemblerTest.php`

**Interfaces:**
- Produces: `MarketAlternates::forDestination(string $slug): array<string,string>`; `LpAssembler::inject(string $html, ?array $flowDest = null, string $headExtra = ''): string`.

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/MarketCountrySeoTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\MarketAlternates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\MakesMarketProfiles;
use Tests\TestCase;

final class MarketCountrySeoTest extends TestCase
{
    use MakesMarketProfiles;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'ukv.intl_base_url' => 'https://beyondpassports.com', 'ukv.base_url' => 'https://beyondpassports.co.uk',
            'ukv.markets.ca.enabled' => true, 'ukv.markets.ca.indexable' => true,
            'ukv.markets.za.enabled' => true, 'ukv.markets.za.indexable' => false,
        ]);
    }

    public function test_for_destination_includes_uk_gold_and_live_indexable_markets_only(): void
    {
        $this->liveProfile('ca', 'italy');
        $this->liveProfile('za', 'italy');
        $alt = MarketAlternates::forDestination('italy');
        $this->assertSame('https://beyondpassports.co.uk/schengen-visa/italy', $alt['en-GB']);
        $this->assertSame('https://beyondpassports.com/ca/schengen-visa/italy', $alt['en-CA']);
        $this->assertArrayNotHasKey('en-ZA', $alt);
        $this->assertSame('https://beyondpassports.com', $alt['x-default']);
    }

    public function test_no_en_gb_without_uk_gold_page_and_nothing_when_no_market_is_live(): void
    {
        $this->liveProfile('ca', 'austria');
        $alt = MarketAlternates::forDestination('austria');
        $this->assertArrayNotHasKey('en-GB', $alt);
        $this->assertArrayHasKey('en-CA', $alt);
        $this->assertSame([], MarketAlternates::forDestination('france'));
        $this->liveProfile('ca', 'spain', ['verified' => false, 'published' => false, 'reviewed_by' => null, 'reviewed_at' => null]);
        $this->assertSame([], MarketAlternates::forDestination('spain'));
    }

    public function test_market_page_emits_alternates_in_head(): void
    {
        $this->liveProfile('ca', 'italy');
        $r = $this->get('/ca/schengen-visa/italy');
        $r->assertSee('hreflang="en-GB" href="https://beyondpassports.co.uk/schengen-visa/italy"', false);
        $r->assertSee('hreflang="en-CA" href="https://beyondpassports.com/ca/schengen-visa/italy"', false);
        $r->assertSee('hreflang="x-default" href="https://beyondpassports.com"', false);
    }

    public function test_uk_static_gold_page_reciprocates(): void
    {
        $this->assertFileExists(public_path('lp-italy.html'));
        $this->liveProfile('ca', 'italy');
        $r = $this->get('/schengen-visa/italy');
        $r->assertOk();
        $r->assertSee('hreflang="en-CA" href="https://beyondpassports.com/ca/schengen-visa/italy"', false);
        $r->assertSee('hreflang="en-GB" href="https://beyondpassports.co.uk/schengen-visa/italy"', false);
    }

    public function test_uk_gold_page_emits_nothing_when_no_market_is_live(): void
    {
        $this->get('/schengen-visa/italy')->assertOk()->assertDontSee('hreflang=', false);
    }
}
```

Append to `tests/Feature/LpAssemblerTest.php` inside the class:

```php
    public function test_head_extra_is_injected_after_analytics_head(): void
    {
        $out = LpAssembler::inject($this->doc, null, '<link rel="alternate" hreflang="en-CA" href="https://x/ca">');
        $this->assertStringContainsString('hreflang="en-CA"', $out);
        $this->assertLessThan(strpos($out, '<body>'), strpos($out, 'hreflang="en-CA"'));
    }
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter='MarketCountrySeoTest|LpAssemblerTest'`
Expected: FAIL (`forDestination` returns `[]`; `inject()` rejects a third argument).

- [ ] **Step 3: Implement `forDestination`**

In `app/Support/MarketAlternates.php` replace the stub method with:

```php
    /**
     * hreflang map for a destination page (SP3 spec 8.1). en-GB only when the UK gold page exists for
     * the slug; each enabled + indexable market only when its profile is verified + published;
     * x-default = the .com chooser. Returns [] when no non-UK alternate exists.
     *
     * @return array<string,string>
     */
    public static function forDestination(string $slug): array
    {
        $out = [];
        foreach (Market::enabled() as $m) {
            if (! $m->isIndexable()) {
                continue;
            }
            $live = \App\Models\MarketDestinationProfile::live()->where('market', $m->code)->where('destination_slug', $slug)->exists();
            if ($live) {
                $out[$m->locale()] = market_url('/schengen-visa/'.$slug, $m);
            }
        }
        if ($out === []) {
            return [];
        }
        if (in_array($slug, self::UK_COUNTRY_SLUGS, true)) {
            $out = ['en-GB' => rtrim(Market::uk()->baseUrl(), '/').'/schengen-visa/'.$slug] + $out;
        }
        $out['x-default'] = rtrim((string) config('ukv.intl_base_url'), '/');

        return $out;
    }
```

- [ ] **Step 4: Let the partial accept a precomputed map**

Replace `resources/views/partials/hreflang.blade.php` with:

```blade
{{-- Reciprocal hreflang. Pass 'path' (UK-relative path of this page type) or a precomputed
     'alternates' map (destination pages, SP3). Renders nothing when the map is empty. --}}
@php($__alts = (isset($alternates) && is_array($alternates)) ? $alternates : \App\Support\MarketAlternates::for($path ?? '/'))
@foreach ($__alts as $lang => $href)
<link rel="alternate" hreflang="{{ $lang }}" href="{{ $href }}">
@endforeach
```

- [ ] **Step 5: Extend `LpAssembler::inject`**

In `app/Support/LpAssembler.php` change the signature and the head injection:

```php
    /** Inject site-wide head (+ optional extra head markup, e.g. hreflang), the shared modal flow, and lead attribution. */
    public static function inject(string $html, ?array $flowDest = null, string $headExtra = ''): string
    {
        // 1. analytics/consent head (+ extra head markup) right after <head>
        $head = View::make('partials.analytics-head')->render().$headExtra;
```

Keep the rest of the method unchanged.

- [ ] **Step 6: Make the UK gold route reciprocate**

In `routes/web.php`, in the `/schengen-visa/{country}` closure, replace

```php
    $html = \App\Support\LpAssembler::inject($html, ['dest' => ucfirst($country), 'iso' => $iso]);
```

with:

```php
    // Reciprocal hreflang towards live .com destination pages (SP3 spec 8.3). Empty when none is live.
    $hreflang = \Illuminate\Support\Facades\View::make('partials.hreflang', ['alternates' => \App\Support\MarketAlternates::forDestination($country)])->render();
    $html = \App\Support\LpAssembler::inject($html, ['dest' => ucfirst($country), 'iso' => $iso], $hreflang);
```

- [ ] **Step 7: Run to verify it passes**

Run: `php artisan test --filter='MarketCountrySeoTest|LpAssemblerTest|MarketSeoTest'`
Expected: PASS. If `test_uk_static_gold_page_reciprocates` fails because the static file lacks a literal `<head>` tag (for example `<head lang=...>`), extend `LpAssembler` to search `stripos($html, '<head')` and insert after the closing `>` of that tag.

- [ ] **Step 8: Commit**

```bash
git add app/Support/MarketAlternates.php resources/views/partials/hreflang.blade.php app/Support/LpAssembler.php routes/web.php tests/Feature/MarketCountrySeoTest.php tests/Feature/LpAssemblerTest.php
git commit -m "feat(seo): per-destination hreflang incl. UK gold reciprocity via LpAssembler head injection"
```

---

### Task 10: Sitemap-intl entries and hub links for live profiles

**Files:**
- Modify: `app/Http/Controllers/SitemapIntlController.php`
- Modify: `app/Http/Controllers/Market/MarketHubController.php`, `resources/views/market/hub.blade.php`
- Test: `tests/Feature/MarketCountrySitemapHubTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MarketCountrySitemapHubTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\MakesMarketProfiles;
use Tests\TestCase;

final class MarketCountrySitemapHubTest extends TestCase
{
    use MakesMarketProfiles;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'ukv.intl_base_url' => 'https://beyondpassports.com',
            'ukv.markets.ca.enabled' => true, 'ukv.markets.ca.indexable' => true,
            'ukv.markets.za.enabled' => true, 'ukv.markets.za.indexable' => false,
        ]);
    }

    public function test_sitemap_lists_live_profiles_with_reviewed_lastmod_only_for_indexable_markets(): void
    {
        $this->liveProfile('ca', 'italy');
        $this->liveProfile('ca', 'spain', ['verified' => false, 'published' => false, 'reviewed_by' => null, 'reviewed_at' => null]);
        $this->liveProfile('za', 'italy');
        $r = $this->get('/sitemap-intl.xml')->assertOk();
        $r->assertSee('<loc>https://beyondpassports.com/ca/schengen-visa/italy</loc><lastmod>2026-10-01</lastmod>', false);
        $r->assertDontSee('/ca/schengen-visa/spain', false);
        $r->assertDontSee('/za/schengen-visa/italy', false);
        $r->assertDontSee('.md', false);
    }

    public function test_hub_links_live_destinations_and_lists_the_rest_as_text(): void
    {
        $this->liveProfile('ca', 'italy');
        $r = $this->get('/ca/schengen-visa')->assertOk();
        $r->assertSee('<a href="https://beyondpassports.com/ca/schengen-visa/italy">Italy</a>', false);
        $r->assertSee('<li>Austria</li>', false);
        $r->assertDontSee('href="https://beyondpassports.com/ca/schengen-visa/austria"', false);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketCountrySitemapHubTest`
Expected: FAIL.

- [ ] **Step 3: Extend `SitemapIntlController`**

In `app/Http/Controllers/SitemapIntlController.php`, inside the `foreach (Market::enabled() as $m)` loop, after the `foreach (self::PATHS ...)` block add:

```php
            // SP3: one entry per verified + published destination profile; lastmod is the review date.
            \App\Models\MarketDestinationProfile::live()->forMarket($m->code)->orderBy('destination_slug')->get()
                ->each(function (\App\Models\MarketDestinationProfile $p) use (&$urls, $m): void {
                    $urls[] = [
                        'loc' => market_url($p->path(), $m),
                        'lastmod' => ($p->reviewed_at ?? $p->updated_at)->toDateString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.8',
                    ];
                });
```

- [ ] **Step 4: Hub links**

In `app/Http/Controllers/Market/MarketHubController.php` replace the `__invoke` body with:

```php
        $market = Market::current();

        return response()->view('market.hub', [
            'market' => $market,
            'destinations' => self::SCHENGEN,
            'liveSlugs' => \App\Models\MarketDestinationProfile::live()->forMarket($market->code)->pluck('destination_slug')->all(),
        ]);
```

In `resources/views/market/hub.blade.php` replace the destination `<ul>` line with:

```blade
  <ul>@foreach ($destinations as $d)@php($slug = \App\Support\SchengenDestinations::slug($d))@if (in_array($slug, $liveSlugs, true))<li><a href="{{ market_url('/schengen-visa/'.$slug, $market) }}">{{ $d }}</a></li>@else<li>{{ $d }}</li>@endif @endforeach</ul>
```

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter='MarketCountrySitemapHubTest|MarketSitemapTest|MarketHubPageTest'`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/SitemapIntlController.php app/Http/Controllers/Market/MarketHubController.php resources/views/market/hub.blade.php tests/Feature/MarketCountrySitemapHubTest.php
git commit -m "feat(seo): sitemap-intl lists live destination profiles; hub links live destinations"
```

---

### Task 11: Markdown twin

**Files:**
- Create: `app/Support/MarketCountryMarkdown.php`
- Modify: `app/Http/Controllers/Market/MarketCountryMarkdownController.php` (replace placeholder)
- Test: `tests/Feature/MarketCountryMarkdownTest.php`

**Interfaces:**
- Produces: `MarketCountryMarkdown::render(MarketDestinationProfile $p, Market $m): string`, `MarketCountryMarkdown::wordCount(MarketDestinationProfile $p): int`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MarketCountryMarkdownTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\CopyRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\MakesMarketProfiles;
use Tests\TestCase;

final class MarketCountryMarkdownTest extends TestCase
{
    use MakesMarketProfiles;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['ukv.markets.ca.enabled' => true, 'ukv.markets.ca.indexable' => true, 'ukv.intl_base_url' => 'https://beyondpassports.com']);
    }

    public function test_md_twin_serves_markdown_with_canonical_link_header(): void
    {
        $this->liveProfile('ca', 'italy');
        $r = $this->get('/ca/schengen-visa/italy.md');
        $r->assertOk();
        $r->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
        $r->assertHeader('Link', '<https://beyondpassports.com/ca/schengen-visa/italy>; rel="canonical"');
        $r->assertHeader('X-Robots-Tag', 'noindex');
        $body = $r->getContent();
        $this->assertStringStartsWith('# Schengen visa for Italy from Canada', $body);
        $this->assertStringContainsString('| Government visa fee, adult | EUR 90', $body);
        $this->assertStringContainsString('| Document 1 |', $body);
        $this->assertStringContainsString('## Asked every week', $body);
        $this->assertStringContainsString('Reviewed by Test Reviewer on 1 Oct 2026', $body);
        $this->assertSame([], CopyRules::violations($body));
    }

    public function test_md_twin_404s_when_not_live_or_unknown(): void
    {
        $this->get('/ca/schengen-visa/italy.md')->assertNotFound();
        $this->liveProfile('ca', 'spain', ['published' => false]);
        $this->get('/ca/schengen-visa/spain.md')->assertNotFound();
        $this->get('/ca/schengen-visa/narnia.md')->assertNotFound();
    }

    public function test_html_route_does_not_swallow_md_suffix(): void
    {
        $this->liveProfile('ca', 'italy');
        $this->assertStringContainsString('<!doctype html>', strtolower($this->get('/ca/schengen-visa/italy')->getContent()));
        $this->assertStringNotContainsString('<!doctype', strtolower($this->get('/ca/schengen-visa/italy.md')->getContent()));
    }

    public function test_word_count_counts_profile_prose(): void
    {
        $p = $this->liveProfile('ca', 'italy');
        $this->assertGreaterThan(200, \App\Support\MarketCountryMarkdown::wordCount($p));
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketCountryMarkdownTest`
Expected: FAIL (404 everywhere).

- [ ] **Step 3: Create the renderer**

Create `app/Support/MarketCountryMarkdown.php`:

```php
<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\MarketDestinationProfile;

/**
 * Markdown twin of a market x destination page (spec 10), generated from the same profile row so
 * the two can never disagree. Also supplies the word count shown in the admin table.
 */
final class MarketCountryMarkdown
{
    public static function render(MarketDestinationProfile $p, Market $m): string
    {
        $url = market_url($p->path(), $m);
        $d = $p->destination_name;
        $fmt = fn (?string $cur, $v) => $cur.' '.number_format((float) $v, (float) $v == floor((float) $v) ? 0 : 2);
        $o = [];
        $o[] = "# Schengen visa for {$d} from {$m->label()}";
        $o[] = '';
        $o[] = "Canonical page: {$url}";
        $o[] = "Reviewed by {$p->reviewed_by} on ".$p->reviewed_at?->format('j M Y').'. Beyond Passports Ltd, Companies House 17331903, ICO ZC197159. Not the government, not a visa centre. Appointments are free and we never sell one.';
        $o[] = '';
        $o[] = '## Who this page is for';
        if ($etias = MarketQualification::etiasSplit($m)) {
            $o[] = $etias;
            $o[] = '';
        }
        $o[] = (string) $p->intro;
        $o[] = '';
        $o[] = "Residence rule: {$p->status_rule}";
        $o[] = '';
        $o[] = "## What a {$d} visa costs from {$m->label()}";
        $o[] = '| Item | Amount | Paid to |';
        $o[] = '|---|---|---|';
        $local = $p->govt_fee_local_adult !== null && $p->govt_fee_local_currency ? ' ('.$fmt($p->govt_fee_local_currency, $p->govt_fee_local_adult).', verified '.$p->govt_fee_verified_at?->format('j M Y').')' : '';
        $o[] = "| Government visa fee, adult | EUR {$p->govt_fee_eur_adult}{$local} | The consulate |";
        $o[] = "| Government visa fee, child 6 to 11 | EUR {$p->govt_fee_eur_child} | The consulate |";
        $o[] = '| Government visa fee, under 6 | Free | The consulate |';
        $vac = ($p->vac_fee_amount !== null && (float) $p->vac_fee_amount > 0 && $p->vac_fee_currency) ? $fmt($p->vac_fee_currency, $p->vac_fee_amount).', verified '.$p->vac_fee_verified_at?->format('j M Y') : 'None';
        $o[] = "| Visa centre fee | {$vac} | ".ucfirst($p->operatorLabel()).' |';
        $o[] = '';
        $o[] = (string) $p->vac_fee_note;
        $o[] = 'You can apply without us on the official portal for the government and centre fees alone. Our fee is for preparation and review only.';
        $o[] = '';
        $o[] = "## How appointments work for {$d} in {$m->label()}";
        $o[] = (string) $p->appointment_copy;
        $o[] = '';
        $o[] = "Where you apply: {$p->jurisdiction_note}";
        foreach ((array) $p->centres as $c) {
            $o[] = '- '.($c['city'] ?? '').': '.($c['name'] ?? '').' (serves '.($c['serves'] ?? '').')';
        }
        $o[] = '';
        $o[] = "Processing once lodged: {$p->processing_text}.";
        $o[] = '';
        $o[] = '## Six steps';
        $o[] = '1. You message us with destination, dates, city and residence status.';
        $o[] = '2. We route you to the right post and build your checklist.';
        $o[] = '3. We prepare the form, cover letter and review your real bookings and insurance.';
        $o[] = '4. '.$p->bookingStepText();
        $o[] = '5. You attend the appointment with the appointment-day pack.';
        $o[] = "6. The consulate decides, {$p->processing_text}. You travel.";
        $o[] = '';
        $o[] = '## Documents';
        $o[] = '| Document | What the consulate wants | Common mistake |';
        $o[] = '|---|---|---|';
        foreach ((array) $p->documents as $doc) {
            $o[] = '| '.($doc['document'] ?? '').' | '.($doc['detail'] ?? '').' | '.($doc['common_mistake'] ?? '').' |';
        }
        $o[] = '';
        $o[] = '## What we do and what we do not';
        $o[] = 'We do not sell, hold or block-book appointments; they are free and booked only on the official site in your name. We do not run bots on your account or ask for your login. We cannot create availability, promise a date or speed up the consulate\'s decision. We are not affiliated with VFS Global, TLScontact, BLS International, Capago, Global Visa Center World, any embassy, consulate or government. What we do: make sure every document is ready before a date appears, watch availability for your route, and stay on WhatsApp while you book.';
        $o[] = '';
        $o[] = "## Asked every week by applicants in {$m->label()}";
        foreach ((array) $p->faqs as $f) {
            $o[] = '### '.($f['q'] ?? '');
            $o[] = (string) ($f['a'] ?? '');
            $o[] = '';
        }
        $o[] = '## How we reviewed this page';
        $o[] = "Reviewed by {$p->reviewed_by} on ".$p->reviewed_at?->format('j M Y').'. Sources:';
        foreach ((array) $p->source_urls as $s) {
            $o[] = '- '.($s['label'] ?? '').': '.($s['url'] ?? '');
        }
        $o[] = '';

        return implode("\n", $o);
    }

    public static function wordCount(MarketDestinationProfile $p): int
    {
        return str_word_count($p->copyText());
    }
}
```

- [ ] **Step 4: Replace the controller**

Replace `app/Http/Controllers/Market/MarketCountryMarkdownController.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Models\MarketDestinationProfile;
use App\Support\Market;
use App\Support\MarketCountryMarkdown;
use Illuminate\Http\Response;

/** /{market}/schengen-visa/{country}.md: Markdown twin, live rows only (spec 10). */
final class MarketCountryMarkdownController extends Controller
{
    public function __invoke(string $market, string $country): Response
    {
        $m = Market::current();
        $profile = MarketDestinationProfile::findFor($m->code, $country);
        abort_if($profile === null || ! $profile->isLive(), 404);

        return response(MarketCountryMarkdown::render($profile, $m), 200)
            ->header('Content-Type', 'text/markdown; charset=UTF-8')
            ->header('Link', '<'.market_url($profile->path(), $m).'>; rel="canonical"')
            ->header('X-Robots-Tag', 'noindex');
    }
}
```

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter='MarketCountryMarkdownTest|MarketCountryPageTest'`
Expected: PASS. If the `.md` request hits the HTML controller, the HTML route is registered before the `.md` route or its regex admits a dot; check `php artisan route:list --path=schengen-visa` ordering and the `where` pattern.

- [ ] **Step 6: Commit**

```bash
git add app/Support/MarketCountryMarkdown.php app/Http/Controllers/Market/MarketCountryMarkdownController.php tests/Feature/MarketCountryMarkdownTest.php
git commit -m "feat(seo): Markdown twin for live market destination pages"
```

---

### Task 12: Generated /llms.txt

**Files:**
- Create: `app/Http/Controllers/LlmsTxtController.php`
- Modify: `routes/web.php` (one route beside `/sitemap-intl.xml`)
- Test: `tests/Feature/LlmsTxtTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/LlmsTxtTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\CopyRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\MakesMarketProfiles;
use Tests\TestCase;

final class LlmsTxtTest extends TestCase
{
    use MakesMarketProfiles;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'ukv.intl_base_url' => 'https://beyondpassports.com', 'ukv.base_url' => 'https://beyondpassports.co.uk',
            'ukv.markets.ca.enabled' => true, 'ukv.markets.ca.indexable' => true,
            'ukv.markets.za.enabled' => true, 'ukv.markets.za.indexable' => false,
        ]);
    }

    public function test_llms_txt_lists_uk_pages_live_market_pages_and_facts(): void
    {
        $this->liveProfile('ca', 'italy');
        $this->liveProfile('ca', 'spain', ['published' => false]);
        $this->liveProfile('za', 'italy');
        $r = $this->get('/llms.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $body = $r->getContent();
        $this->assertStringStartsWith('# Beyond Passports', $body);
        $this->assertStringContainsString('17331903', $body);
        $this->assertStringContainsString('## United Kingdom', $body);
        $this->assertStringContainsString('https://beyondpassports.co.uk/schengen-visa/france', $body);
        $this->assertStringContainsString('## Canada', $body);
        $this->assertStringContainsString('[Schengen visa for Italy from Canada](https://beyondpassports.com/ca/schengen-visa/italy)', $body);
        $this->assertStringContainsString('https://beyondpassports.com/ca/schengen-visa/italy.md', $body);
        $this->assertStringContainsString('reviewed 1 Oct 2026', $body);
        $this->assertStringNotContainsString('/ca/schengen-visa/spain', $body);
        $this->assertStringNotContainsString('## South Africa', $body);
        $this->assertStringContainsString('## Facts a machine might need', $body);
        $this->assertStringContainsString('EUR 90', $body);
        $this->assertSame([], CopyRules::violations($body));
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=LlmsTxtTest`
Expected: FAIL with 404.

- [ ] **Step 3: Create the controller**

Create `app/Http/Controllers/LlmsTxtController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MarketDestinationProfile;
use App\Support\Market;
use App\Support\MarketAlternates;
use Illuminate\Http\Response;

/**
 * Generated /llms.txt (spec 10): sectioned, absolute URLs for both hosts, only verified + published
 * destination pages in enabled + indexable markets, and a facts section. Served identically on
 * either host. Atlys's 800-line file and Visard's "Facts a machine might need" are the benchmarks.
 */
final class LlmsTxtController extends Controller
{
    public function __invoke(): Response
    {
        $uk = rtrim(Market::uk()->baseUrl(), '/');
        $o = [];
        $o[] = '# Beyond Passports';
        $o[] = '';
        $o[] = '> Schengen visa document preparation with a named consultant on WhatsApp, for applicants in the United Kingdom and selected international markets. Beyond Passports Ltd is registered in England and Wales (Companies House 17331903) and with the ICO (ZC197159). It is a private consultancy, not a government, embassy or visa-centre service. Appointments are free on the official visa centre sites and are never sold.';
        $o[] = '';
        $o[] = '## United Kingdom (beyondpassports.co.uk)';
        $o[] = "- [Schengen visa hub]({$uk}/schengen-visa)";
        foreach (MarketAlternates::UK_COUNTRY_SLUGS as $slug) {
            $o[] = '- ['.ucfirst($slug)." Schengen visa from the UK]({$uk}/schengen-visa/{$slug})";
        }
        $o[] = '';
        foreach (Market::enabled() as $m) {
            if (! $m->isIndexable()) {
                continue;
            }
            $o[] = "## {$m->label()} (beyondpassports.com/{$m->code})";
            $o[] = '- [Schengen visa from '.$m->label().']('.market_url('/schengen-visa', $m).')';
            MarketDestinationProfile::live()->forMarket($m->code)->orderBy('destination_name')->get()
                ->each(function (MarketDestinationProfile $p) use (&$o, $m): void {
                    $url = market_url($p->path(), $m);
                    $o[] = "- [Schengen visa for {$p->destination_name} from {$m->label()}]({$url}): {$p->operatorLabel()}, booking mode {$p->booking_mode}, reviewed ".$p->reviewed_at?->format('j M Y').". Markdown: {$url}.md";
                });
            $o[] = '';
        }
        $o[] = '## Facts a machine might need';
        $o[] = '- Schengen short-stay visa fee: EUR 90 per adult, EUR 45 for children aged 6 to 11, free under 6, paid to the consulate. Visa centre service fees are extra and vary by operator and country.';
        $o[] = '- Appointments are released by consulates and booked free on the official VFS Global, TLScontact, BLS International, Capago, Global Visa Center World or consulate site, in the applicant\'s own name. No one can create availability, buy an earlier date or shorten the consulate\'s processing time.';
        $o[] = '- Processing is usually within 15 days once lodged and can take up to 45 days in busy periods. The applicant attends in person; fingerprints taken within the last 59 months can be reused where the operator allows.';
        $o[] = '- Beyond Passports prepares and reviews the file, coaches the booking on the applicant\'s own account and stays on WhatsApp until the decision. It does not attend, submit or decide.';
        $o[] = '';

        return response(implode("\n", $o), 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
```

- [ ] **Step 4: Register the route**

In `routes/web.php`, directly after the `/sitemap-intl.xml` route add:

```php
Route::get('/llms.txt', \App\Http\Controllers\LlmsTxtController::class)->name('llms');
```

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter=LlmsTxtTest`
Expected: PASS (1 test).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/LlmsTxtController.php routes/web.php tests/Feature/LlmsTxtTest.php
git commit -m "feat(seo): generated sectioned /llms.txt listing live market destination pages"
```

---

### Task 13: Filament resource for profiles

**Files:**
- Create: `app/Filament/Resources/MarketDestinationProfileResource.php`
- Create: `app/Filament/Resources/MarketDestinationProfileResource/Pages/ListMarketDestinationProfiles.php`, `CreateMarketDestinationProfile.php`, `EditMarketDestinationProfile.php`
- Modify: `tests/Feature/AdminPanelSmokeTest.php` (add the slug)

- [ ] **Step 1: Extend the smoke test**

In `tests/Feature/AdminPanelSmokeTest.php` add `'market-destination-profiles'` to the `$resources` array.

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=AdminPanelSmokeTest`
Expected: FAIL (404 on `/admin/market-destination-profiles`).

- [ ] **Step 3: Create the resource**

Create `app/Filament/Resources/MarketDestinationProfileResource.php`:

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Concerns\AuthorizesByRole;
use App\Filament\Concerns\HiddenFromEditor;
use App\Filament\Resources\MarketDestinationProfileResource\Pages;
use App\Models\MarketDestinationProfile;
use App\Support\Market;
use App\Support\MarketCountryMarkdown;
use App\Support\SchengenDestinations;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Ops surface for market x destination profiles (spec 4.1, 4.4). The model guard is the real gate;
 * the form mirrors it so a Viewer sees why a row cannot be verified. "Words" flags copy under the
 * 3,000-word editorial floor in amber.
 */
class MarketDestinationProfileResource extends Resource
{
    use AuthorizesByRole;
    use HiddenFromEditor;

    protected static ?string $model = MarketDestinationProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-europe-africa';

    protected static ?string $navigationGroup = 'International';

    protected static ?string $navigationLabel = 'Market destination profiles';

    public static function form(Form $form): Form
    {
        $verifiedRule = fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
            if (! $value) {
                return;
            }
            foreach (['reviewed_by', 'reviewed_at', 'govt_fee_verified_at', 'vac_fee_verified_at', 'vac_fee_source_url'] as $k) {
                if (blank($get($k))) {
                    $fail("Cannot mark verified while {$k} is empty.");
                }
            }
        };

        return $form->schema([
            Forms\Components\Section::make('Cell')->columns(3)->schema([
                Forms\Components\Select::make('market')->options(array_combine(Market::codes(), array_map(fn ($c) => Market::fromCode($c)->label(), Market::codes())))->required()->native(false),
                Forms\Components\Select::make('destination_slug')->label('Destination')->options(array_combine(SchengenDestinations::slugs(), SchengenDestinations::NAMES))->required()->native(false)->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set): void {
                        $set('destination_name', SchengenDestinations::nameFromSlug((string) $state));
                        $set('destination_iso', SchengenDestinations::isoFromSlug((string) $state));
                    }),
                Forms\Components\Hidden::make('destination_name'),
                Forms\Components\Hidden::make('destination_iso'),
                Forms\Components\Select::make('operator')->options(MarketDestinationProfile::OPERATOR_LABELS)->required()->native(false),
                Forms\Components\TextInput::make('portal_name')->maxLength(80)->helperText('Booking system shown to applicants, e.g. Prenot@mi.'),
                Forms\Components\Select::make('booking_mode')->options(array_combine(MarketDestinationProfile::BOOKING_MODES, MarketDestinationProfile::BOOKING_MODES))->required()->native(false),
            ]),
            Forms\Components\Section::make('Where and who')->schema([
                Forms\Components\Repeater::make('centres')->schema([
                    Forms\Components\TextInput::make('city')->required()->maxLength(60),
                    Forms\Components\TextInput::make('name')->required()->maxLength(120),
                    Forms\Components\TextInput::make('serves')->required()->maxLength(160),
                ])->columns(3)->addActionLabel('Add centre')->columnSpanFull(),
                Forms\Components\Textarea::make('jurisdiction_note')->rows(3)->columnSpanFull(),
                Forms\Components\Textarea::make('status_rule')->rows(3)->columnSpanFull()->helperText('In the consulate\'s own words, with the validity threshold.'),
                Forms\Components\TextInput::make('processing_text')->maxLength(160),
            ]),
            Forms\Components\Section::make('Copy')->schema([
                Forms\Components\Textarea::make('intro')->rows(5)->columnSpanFull(),
                Forms\Components\Textarea::make('appointment_copy')->rows(6)->columnSpanFull()->helperText('Honest, per operator. Never "earlier", "faster", "guaranteed" or any reply-time promise; the save is rejected if it slips in.'),
            ]),
            Forms\Components\Section::make('Fees (every figure needs a verified date and a source)')->columns(3)->schema([
                Forms\Components\TextInput::make('govt_fee_eur_adult')->numeric()->default(90)->required(),
                Forms\Components\TextInput::make('govt_fee_eur_child')->numeric()->default(45)->required(),
                Forms\Components\TextInput::make('govt_fee_local_adult')->numeric()->step('0.01'),
                Forms\Components\TextInput::make('govt_fee_local_currency')->maxLength(3),
                Forms\Components\DatePicker::make('govt_fee_verified_at'),
                Forms\Components\TextInput::make('govt_fee_source_url')->url()->maxLength(300),
                Forms\Components\TextInput::make('vac_fee_amount')->numeric()->step('0.01')->helperText('0 for consulate-direct posts; explain in the note.'),
                Forms\Components\TextInput::make('vac_fee_currency')->maxLength(3),
                Forms\Components\DatePicker::make('vac_fee_verified_at'),
                Forms\Components\TextInput::make('vac_fee_source_url')->url()->maxLength(300)->columnSpan(2),
                Forms\Components\TextInput::make('vac_fee_note')->maxLength(240)->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Documents, FAQs, sources')->schema([
                Forms\Components\Repeater::make('documents')->schema([
                    Forms\Components\TextInput::make('document')->required()->maxLength(120),
                    Forms\Components\Textarea::make('detail')->required()->rows(2),
                    Forms\Components\Textarea::make('common_mistake')->required()->rows(2),
                ])->columns(3)->addActionLabel('Add document')->reorderable()->columnSpanFull(),
                Forms\Components\Repeater::make('faqs')->schema([
                    Forms\Components\TextInput::make('q')->label('Question')->required()->maxLength(200),
                    Forms\Components\Textarea::make('a')->label('Answer')->required()->rows(3),
                ])->addActionLabel('Add FAQ')->reorderable()->columnSpanFull(),
                Forms\Components\Repeater::make('source_urls')->schema([
                    Forms\Components\TextInput::make('label')->required()->maxLength(120),
                    Forms\Components\TextInput::make('url')->url()->required()->maxLength(300),
                ])->columns(2)->addActionLabel('Add source')->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Review and publication')->columns(4)->schema([
                Forms\Components\TextInput::make('reviewed_by')->maxLength(80),
                Forms\Components\DateTimePicker::make('reviewed_at'),
                Forms\Components\Toggle::make('verified')->inline(false)->rules([$verifiedRule])->helperText('Needs reviewer, review date, both fee dates and the VAC source URL.'),
                Forms\Components\Toggle::make('published')->inline(false)->helperText('Live only when verified too, and only in an enabled market.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('market')->badge()->sortable(),
                Tables\Columns\TextColumn::make('destination_name')->label('Destination')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('operator')->badge()->formatStateUsing(fn (string $state) => MarketDestinationProfile::OPERATOR_LABELS[$state] ?? $state),
                Tables\Columns\TextColumn::make('booking_mode')->label('Mode'),
                Tables\Columns\IconColumn::make('verified')->boolean(),
                Tables\Columns\IconColumn::make('published')->boolean(),
                Tables\Columns\TextColumn::make('reviewed_at')->date()->sortable(),
                Tables\Columns\TextColumn::make('words')->label('Words')
                    ->getStateUsing(fn (MarketDestinationProfile $r) => MarketCountryMarkdown::wordCount($r))
                    ->color(fn (int $state) => $state < 1800 ? 'warning' : 'success')
                    ->tooltip('Profile prose only; the template adds roughly 1,200 words. Aim for 1,800 or more here so the page clears 3,000.'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('market')->options(array_combine(Market::codes(), Market::codes())),
                Tables\Filters\TernaryFilter::make('verified'),
                Tables\Filters\TernaryFilter::make('published'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([])
            ->defaultSort('market');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketDestinationProfiles::route('/'),
            'create' => Pages\CreateMarketDestinationProfile::route('/create'),
            'edit' => Pages\EditMarketDestinationProfile::route('/{record}/edit'),
        ];
    }
}
```

- [ ] **Step 4: Create the three page classes**

`app/Filament/Resources/MarketDestinationProfileResource/Pages/ListMarketDestinationProfiles.php`:

```php
<?php

namespace App\Filament\Resources\MarketDestinationProfileResource\Pages;

use App\Filament\Resources\MarketDestinationProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketDestinationProfiles extends ListRecords
{
    protected static string $resource = MarketDestinationProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
```

`.../Pages/CreateMarketDestinationProfile.php`:

```php
<?php

namespace App\Filament\Resources\MarketDestinationProfileResource\Pages;

use App\Filament\Resources\MarketDestinationProfileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMarketDestinationProfile extends CreateRecord
{
    protected static string $resource = MarketDestinationProfileResource::class;
}
```

`.../Pages/EditMarketDestinationProfile.php`:

```php
<?php

namespace App\Filament\Resources\MarketDestinationProfileResource\Pages;

use App\Filament\Resources\MarketDestinationProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarketDestinationProfile extends EditRecord
{
    protected static string $resource = MarketDestinationProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
```

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter=AdminPanelSmokeTest`
Expected: PASS. If the icon name is rejected, use `heroicon-o-globe-alt`.

- [ ] **Step 6: Commit**

```bash
git add app/Filament/Resources/MarketDestinationProfileResource.php app/Filament/Resources/MarketDestinationProfileResource/Pages tests/Feature/AdminPanelSmokeTest.php
git commit -m "feat(admin): Filament resource for market destination profiles with verification rule"
```

---

### Task 14: Full suite, runbook, spec status

**Files:**
- Modify: `docs/GO-LIVE-RUNBOOK.md`
- Modify: `docs/superpowers/specs/2026-10-06-sp3-market-country-pages-design.md` (status line)

- [ ] **Step 1: Run the whole suite**

Run: `php artisan test`
Expected: all PASS. Fix regressions; never skip tests.

- [ ] **Step 2: Append to the runbook**

Append to `docs/GO-LIVE-RUNBOOK.md`:

```markdown
## Market x destination pages (beyondpassports.com, SP3)

Spec: docs/superpowers/specs/2026-10-06-sp3-market-country-pages-design.md.

1. `php artisan migrate && php artisan db:seed --class=MarketDestinationProfileSeeder --force` seeds the
   verified profiles (idempotent). New batches: add rows to the seeder or edit in /admin under
   International > Market destination profiles.
2. A cell goes live only when its row is verified AND published AND its market is enabled. Verified
   needs reviewer, review date, both fee verification dates and the VAC source URL; the model
   refuses to save otherwise.
3. Before `published`: ten or more documents, ten or more FAQs, appointment copy for the real
   booking mode, "Words" column green, sources listed. Greece: never before the manual check.
4. Smoke: `curl -sI https://beyondpassports.com/ca/schengen-visa/italy | grep -i robots` shows no
   noindex once CA is indexable; `/ca/schengen-visa/austria` shows `X-Robots-Tag: noindex, nofollow`
   until verified; `/ca/schengen-visa/italy.md` returns text/markdown; `/llms.txt` lists the page;
   `/sitemap-intl.xml` carries it with the review date; `https://beyondpassports.co.uk/schengen-visa/italy`
   head contains `hreflang="en-CA"`.
```

- [ ] **Step 3: Update the spec status line**

In the spec, change `**Status:** draft for owner review.` to `**Status:** implemented on branch (plan 2026-10-06-sp3-market-country-pages.md); awaiting owner acceptance against section 14.`

- [ ] **Step 4: Commit**

```bash
git add docs/GO-LIVE-RUNBOOK.md docs/superpowers/specs/2026-10-06-sp3-market-country-pages-design.md
git commit -m "docs: SP3 go-live steps; spec status updated"
```

---

## Self-review (done at writing time)

- Spec coverage: 4.1 Tasks 2, 4, 13; 4.2 Task 2; 4.3 Task 2 (`bookingStepText`); 4.4 Task 2 guard + Task 13 rule; 4.5 Task 1 + Task 2 guard + content tests in Tasks 7, 11, 12; 4.6 Task 4; 4.7 Task 3; 5.1 Task 2; 5.2 Task 5; 5.3 Task 12; 6.1-6.15 Tasks 6, 7; 6.10 Task 3 seam + Task 6 partial; 6.14 Task 5; 7 Task 8; 8.1-8.3 Task 9; 9 Task 10; 10 Tasks 11, 12; 11 Task 1 + every rendered-surface test; 12 build order is editorial (runbook Task 14); 13 error handling Tasks 5, 6, 11; 14 acceptance items 1-15 map to Tasks 5, 5, 6, 7, 8, 9, 10, 11, 12, 10, 1+7+11+12, 2, 4+7, 5, 13.
- Review Focus: 1 Task 11 tests (`test_html_route_does_not_swallow_md_suffix`, 404 when not live); 2 Tasks 5, 9, 10, 12 tests on the non-indexable `za` market; 3 Task 9 tests; 4 Task 2 tests; 5 Task 6 `test_fee_table_handles_missing_local_government_fee`.
- Type consistency: `MarketDestinationProfile::findFor/live/forMarket/isLive/operatorLabel/portalLabel/path/bookingStepText/copyText`, `SchengenDestinations::NAMES/ISO/slug/slugs/nameFromSlug/isoFromSlug/routePattern`, `MarketAlternates::forDestination/UK_COUNTRY_SLUGS`, `LpAssembler::inject(string, ?array, string)`, `MarketCountryMarkdown::render/wordCount`, `MarketAvailability::for`, `MarketQualification::tiles/etiasSplit` used consistently across tasks.
- Temporary stubs are each replaced in a named later task: `MarketAlternates::forDestination` (Task 5 stub, Task 9 real), `MarketCountryMarkdownController` (Task 5 placeholder, Task 11 real), `partials.market-country-schema` (Task 7 stub, Task 8 real).
- Known judgement calls left to the executor: Task 5 step 7 route-suffix fallback; Task 8 step 4 timezone; Task 9 step 7 `<head` matching in static files; Task 13 step 5 icon name.
