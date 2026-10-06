# Visa-led Tours Catalogue (SP2) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** replace the six config-driven trip cards with a database catalogue edited in Filament, served as UK `/tour-packages` + `/tour-packages/{slug}` and per-market `/{market}/tour-packages` + `/{market}/tour-packages/{slug}`, using the honest card anatomy (named transport, flights excluded, dated price only when set per market), a per-market compliance strip, enquiry-only WhatsApp CTAs tagged with trip and market, FAQPage schema, reciprocal hreflang and sitemap entries.

**Architecture:** three tables (`tour_packages`, `tour_package_destination`, `tour_package_prices`) behind `App\Models\TourPackage` and `App\Models\TourPackagePrice`. One `TourPackageController` serves market index and both detail pages, reading `Market::current()` (UK default) so 404 rules are identical on both hosts. The UK index keeps its CMS-switchable route and the `partials.tours-body` locked-include; the partial is rewritten to render shared `partials.tour-card` cards from the DB via the existing view composer. Shared partials (`tour-card`, `tour-compliance-strip`, `tour-faq`, `tour-schema`, `tour-detail-body`) are used by UK and market views so the anatomy cannot drift. `App\Support\TourFaqs` holds the eight FAQs. Compliance texts live in `config('ukv.tours.compliance')`. `MarketAlternates::for()` gains an `only` filter for detail hreflang. Both sitemap controllers list published trips per market.

**Tech Stack:** PHP 8.2+, Laravel 12, Filament 3.2, Blade, PHPUnit via `php artisan test` (sqlite in-memory, `RefreshDatabase`), Livewire test helpers for the Filament create page. Reuses SP1: `App\Support\Market`, `market_url()`, `App\Support\MarketAlternates`, `partials.market-trust-strip`, `partials.market-price`, `partials.hreflang`, `partials.lp-chrome`, `partials.lp-footer`, `partials.analytics-head`, `partials.utm-capture`, `App\Http\Controllers\SitemapIntlController`. Existing: `partials.disclaimer-strip` (locked), `App\Support\SiteStats`, `App\Filament\Concerns\{AuthorizesByRole,HiddenFromEditor}`.

**Spec:** `ukv-app/docs/superpowers/specs/2026-10-06-sp2-tours-catalogue-design.md` (owner-approved; final section "Amendments from the tours travel-seller exposure memo" is OWNER-PENDING). Decision basis: `ukv-app/docs/product-goals-2026-10.md` (G4), `ukv-app/docs/superpowers/research/2026-10-06-deepdive-tours-lane.md` (6a-6h), `ukv-app/docs/superpowers/research/2026-10-06-competitive-service-brief.md` (section 6).

**Owner-pending amendments (apply once confirmed, before the affected task starts):** (1) public slug `/tour-packages` becomes `/trips` on both hosts with 301s; copy says "trip idea"/"itinerary"; (2) prices, if shown, are per-service supplier figures (hotel, transport), each dated, never one combined "from"; (3) enquiry flow is option (a) referral: one named licensed operator, no client data transmitted, never two supplier links in one chat, no commission or margin; (4) disclaimer describes mechanics, never a denial; the PTR statute sentence is removed. Tasks below carry an "Amendment note" where they are affected.

All paths below are relative to `ukv-app/`. Run every command from `ukv-app/`.

## Global Constraints

- No payment, deposit, checkout or Stripe reference on any tours page; `config('ukv.tours.enquiry_only')` is hard-coded `true` (spec 8; EuroPath exposure).
- No flights: no "flight", "fly", "air-inclusive" as an inclusion anywhere; every card and detail page states "Flights not included" (spec 6; product goals "Excluded on purpose").
- The word "package" never appears in visible copy, titles, meta descriptions or JSON-LD text on any tours page, UK or market. Only the URL path `/tour-packages` and the statute name "Package Travel and Linked Travel Arrangements Regulations 2018" may contain it (spec 5, 6; PTR 2018 reg 2). Amendment note: once confirmed, both exceptions disappear (slug `/trips`, statute sentence removed).
- A price renders only when `price_from` AND `price_checked_at` are both set for the current market; otherwise the card shows "Price quoted on WhatsApp for your dates" and no number (spec 4.2). Amendment note: per-service lines, each under the same rule, never summed.
- No em-dashes in user-facing copy. No "N services" counters. No "guaranteed", "early", "priority", "fast-track", "we book your appointment" (spec 6; G1 honesty rule).
- Always include `partials.disclaimer-strip` via the partial, never bare `.disc-strip` markup (memory disclaimer-strip-partial).
- Keep `ukv_`/`ukv.`/`UKV_` identifiers and the `tour_packages` table name; display copy says Beyond Passports and "trip" (memory brand-beyond-passports).
- Existing suites must stay green: `php artisan test --filter='Cms|Market|PublicSmoke|LpAssembler|AvailabilityService|AdminPanelSmoke'`.
- Commit after every task. Never push; the owner pushes (memory no-auto-deploy).

## Review Focus

1. Trip available in `markets` but market disabled: `/za/tour-packages/{slug}` must 404 from `ResolveMarket` before the controller runs; trip with `markets` lacking `uk`: UK detail must 404 even though published. Tests in Tasks 6 and 7.
2. Price row with `price_from` set and `price_checked_at` null (possible via DB or seed): must render as no price, never a number without a date. Tests in Tasks 1 and 4; Filament validation in Task 9.
3. The CMS golden test compares the coded `/tour-packages` with the CMS locked-include render. The rewritten `tours-body` must take all data from the view composer (no leading `@php` data block) so both paths render identically. Test re-run in Task 5.
4. hreflang on a detail page must not list a market where the trip is unavailable, and must omit `en-GB` when `uk` is not in `markets`. Test in Task 8.
5. "package" leak through shared partials (`hero-check-form`, stat band, footer). The visible-text test in Task 5 strips tags, scripts, URLs and the statute name; if it fails on a shared partial, fix the copy in that partial and note it in the commit.

---

### Task 0: Pre-flight (SP1 present, branch)

**Files:**
- None modified.

- [ ] **Step 1: Confirm SP1 artefacts exist**

Run: `ls app/Support/Market.php app/Support/MarketAlternates.php app/Http/Controllers/SitemapIntlController.php app/Http/Controllers/Market/MarketToursController.php resources/views/partials/market-trust-strip.blade.php resources/views/partials/hreflang.blade.php resources/views/market/tours.blade.php && grep -c "function market_url" app/Support/helpers.php`
Expected: every path listed, and `1`.

- [ ] **Step 2: Branch**

Run: `git checkout -b feat/sp2-tours-catalogue && php artisan test --filter='Market|Cms\\ContentPagesGolden|PublicSmoke'`
Expected: branch created; all PASS.

---

### Task 1: Tables, models, factory

**Files:**
- Create: `database/migrations/2026_10_06_000001_create_tour_packages_tables.php`
- Create: `app/Models/TourPackage.php`
- Create: `app/Models/TourPackagePrice.php`
- Create: `database/factories/TourPackageFactory.php`
- Test: `tests/Unit/TourPackageModelTest.php`

**Interfaces:**
- Produces: `TourPackage` with `scopePublished`, `scopeOrdered`, static `listFor(Market $m): Collection`, `availableIn(Market $m): bool`, `priceFor(Market $m): ?TourPackagePrice`, `citiesLine(): string`, `cityNames(): string`, `transportLine(): string`, `enquiryMessage(Market $m): string`, `url(Market $m): string`, relations `destinations()`, `mainDestination()`, `prices()`; `TourPackagePrice::isDisplayable(): bool`, `isStale(): bool`.

Amendment note: if amendment (2) is confirmed before this task, add `service` (string, `hotel`|`transport`) and `supplier_name` (string nullable) to `tour_package_prices`, change the unique key to `[tour_package_id, market, service]`, and make `priceFor()` return a keyed collection of displayable rows per service. If amendment (1) is confirmed, `url()` uses `/trips/` instead of `/tour-packages/`.

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/TourPackageModelTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Destination;
use App\Models\TourPackage;
use App\Support\Market;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TourPackageModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['ukv.intl_base_url' => 'https://beyondpassports.com', 'ukv.whatsapp' => '447882747584']);
        Market::bind(null);
    }

    public function test_list_for_filters_published_and_market_and_orders_by_sort(): void
    {
        TourPackage::factory()->create(['name' => 'B Trip', 'slug' => 'b', 'sort' => 20, 'markets' => ['uk', 'za']]);
        TourPackage::factory()->create(['name' => 'A Trip', 'slug' => 'a', 'sort' => 10, 'markets' => ['uk']]);
        TourPackage::factory()->create(['name' => 'Hidden', 'slug' => 'h', 'published' => false, 'markets' => ['uk', 'za']]);

        $this->assertSame(['a', 'b'], TourPackage::listFor(Market::uk())->pluck('slug')->all());
        $this->assertSame(['b'], TourPackage::listFor(Market::fromCode('za'))->pluck('slug')->all());
        $this->assertSame([], TourPackage::listFor(Market::fromCode('ae'))->pluck('slug')->all());
    }

    public function test_price_for_requires_both_amount_and_checked_date(): void
    {
        $p = TourPackage::factory()->create();
        $p->prices()->create(['market' => 'za', 'price_from' => 18900, 'price_checked_at' => '2026-10-01', 'season_label' => 'May departures']);
        $p->prices()->create(['market' => 'ae', 'price_from' => 4900, 'price_checked_at' => null]);
        $p->prices()->create(['market' => 'us', 'price_from' => null, 'price_checked_at' => '2026-10-01']);
        $p->load('prices');

        $this->assertSame('18900.00', $p->priceFor(Market::fromCode('za'))?->price_from);
        $this->assertNull($p->priceFor(Market::fromCode('ae')));
        $this->assertNull($p->priceFor(Market::fromCode('us')));
        $this->assertNull($p->priceFor(Market::uk()));
    }

    public function test_lines_and_enquiry_message(): void
    {
        $italy = Destination::factory()->create(['name' => 'Italy', 'slug' => 'italy']);
        $p = TourPackage::factory()->create([
            'name' => 'Italy Highlights', 'slug' => 'italy-highlights', 'nights' => 6,
            'cities' => [['name' => 'Rome', 'nights' => 2], ['name' => 'Florence', 'nights' => 2], ['name' => 'Venice', 'nights' => 2]],
            'transport' => [
                ['mode' => 'Frecciarossa 2nd class', 'from' => 'Roma Termini', 'to' => 'Firenze S.M.N.'],
                ['mode' => 'Private sedan transfer', 'from' => 'Fiumicino airport', 'to' => 'hotel'],
            ],
            'main_destination_id' => $italy->id,
        ]);

        $this->assertSame('Rome 2 · Florence 2 · Venice 2', $p->citiesLine());
        $this->assertSame('Frecciarossa 2nd class, Roma Termini to Firenze S.M.N.; Private sedan transfer, Fiumicino airport to hotel', $p->transportLine());
        $this->assertSame('Italy', $p->mainDestination->name);

        $msg = $p->enquiryMessage(Market::fromCode('za'));
        $this->assertStringContainsString('Italy Highlights trip (6 nights, Rome, Florence, Venice) from South Africa', $msg);
        $this->assertStringContainsString('[TRIP:italy-highlights] [ZA]', $msg);
        $this->assertStringContainsString('[TRIP:italy-highlights] [UK]', $p->enquiryMessage(Market::uk()));
        $this->assertStringNotContainsStringIgnoringCase('package', $msg);
    }

    public function test_url_per_market_and_empty_json_is_safe(): void
    {
        $p = TourPackage::factory()->create(['slug' => 'paris-long-weekend', 'cities' => null, 'transport' => null]);
        $this->assertSame(url('/tour-packages/paris-long-weekend'), $p->url(Market::uk()));
        $this->assertSame('https://beyondpassports.com/ae/tour-packages/paris-long-weekend', $p->url(Market::fromCode('ae')));
        $this->assertSame('', $p->citiesLine());
        $this->assertSame('', $p->transportLine());
    }

    public function test_price_stale_after_configured_days(): void
    {
        config(['ukv.tours.price_stale_days' => 90]);
        $p = TourPackage::factory()->create();
        $fresh = $p->prices()->create(['market' => 'uk', 'price_from' => 890, 'price_checked_at' => now()->subDays(10)->toDateString()]);
        $old = $p->prices()->create(['market' => 'za', 'price_from' => 18900, 'price_checked_at' => now()->subDays(120)->toDateString()]);
        $this->assertFalse($fresh->isStale());
        $this->assertTrue($old->isStale());
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=TourPackageModelTest`
Expected: FAIL with "Class App\Models\TourPackage not found".

- [ ] **Step 3: Create the migration**

Create `database/migrations/2026_10_06_000001_create_tour_packages_tables.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Visa-led trips catalogue (SP2 spec section 4). Hotel tier + NAMED ground transport legs, no flights.
 * Prices live per market in tour_package_prices and render only when both price_from and
 * price_checked_at are set (deep dive 6c: nobody dates a "from" price; we do).
 * Amendment (2), owner-pending: add `service` + `supplier_name` and widen the unique key.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_packages', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 140)->unique();
            $t->string('name', 120);
            $t->string('where_line', 160);                 // "Rome · Florence · Venice" (where is a reserved word)
            $t->unsignedTinyInteger('nights');
            $t->json('cities')->nullable();                // [{name, nights}]
            $t->string('hotel_tier', 160)->nullable();     // "4-star central, breakfast included"
            $t->json('transport')->nullable();             // [{mode, from, to}] named legs
            $t->json('highlights')->nullable();            // [string]
            $t->string('image', 255)->nullable();          // /assets/tours/italy.jpg
            $t->string('flag_css', 255)->nullable();
            $t->foreignId('main_destination_id')->nullable()->constrained('destinations')->nullOnDelete();
            $t->json('markets')->nullable();               // ["uk","za","ae","us","ca"]
            $t->unsignedSmallInteger('sort')->default(100);
            $t->boolean('published')->default(false);
            $t->timestamps();
            $t->index(['published', 'sort']);
        });

        Schema::create('tour_package_destination', function (Blueprint $t) {
            $t->foreignId('tour_package_id')->constrained()->cascadeOnDelete();
            $t->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $t->primary(['tour_package_id', 'destination_id']);
        });

        Schema::create('tour_package_prices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tour_package_id')->constrained()->cascadeOnDelete();
            $t->string('market', 2);                       // uk|za|ae|us|ca
            $t->decimal('price_from', 10, 2)->nullable();  // land only, per person, two sharing, market currency
            $t->string('season_label', 120)->nullable();   // "May and June departures; peak dates higher"
            $t->string('fx_note', 160)->nullable();        // "EUR 1 = R 20.10 on 1 Oct 2026"
            $t->date('price_checked_at')->nullable();      // required whenever price_from is set
            $t->timestamps();
            $t->unique(['tour_package_id', 'market']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_package_prices');
        Schema::dropIfExists('tour_package_destination');
        Schema::dropIfExists('tour_packages');
    }
};
```

- [ ] **Step 4: Create the models**

Create `app/Models/TourPackagePrice.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TourPackagePrice extends Model
{
    protected $fillable = ['tour_package_id', 'market', 'price_from', 'season_label', 'fx_note', 'price_checked_at'];

    protected function casts(): array
    {
        return ['price_from' => 'decimal:2', 'price_checked_at' => 'date'];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class, 'tour_package_id');
    }

    /** A price is displayable only when the amount AND the checked date are both present. */
    public function isDisplayable(): bool
    {
        return $this->price_from !== null && $this->price_checked_at !== null;
    }

    public function isStale(): bool
    {
        $days = (int) config('ukv.tours.price_stale_days', 90);

        return $this->price_checked_at === null || $this->price_checked_at->lt(now()->subDays($days)->startOfDay());
    }
}
```

Create `app/Models/TourPackage.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Market;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A visa-led trip: hotel tier + named ground transport around a Schengen application. No flights,
 * enquiry only. Copy calls it a "trip" (PTR 2018 reg 2); the table keeps the technical name.
 * Amendment (1), owner-pending: url() path becomes /trips/.
 */
final class TourPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'name', 'where_line', 'nights', 'cities', 'hotel_tier', 'transport', 'highlights',
        'image', 'flag_css', 'main_destination_id', 'markets', 'sort', 'published',
    ];

    protected function casts(): array
    {
        return [
            'nights' => 'integer', 'cities' => 'array', 'transport' => 'array', 'highlights' => 'array',
            'markets' => 'array', 'sort' => 'integer', 'published' => 'boolean',
        ];
    }

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class, 'tour_package_destination');
    }

    public function mainDestination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'main_destination_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(TourPackagePrice::class);
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('published', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort')->orderBy('name');
    }

    /** Published trips available in a market, in display order. Filtered in PHP: portable and tiny. */
    public static function listFor(Market $market): Collection
    {
        return self::query()->published()->ordered()->with(['prices', 'mainDestination'])->get()
            ->filter(fn (TourPackage $p) => $p->availableIn($market))->values();
    }

    public function availableIn(Market $market): bool
    {
        return in_array($market->code, (array) ($this->markets ?? []), true);
    }

    /** Display rule: amount AND checked date for this market, else null (never a bare number). */
    public function priceFor(Market $market): ?TourPackagePrice
    {
        $price = $this->prices->firstWhere('market', $market->code);

        return $price && $price->isDisplayable() ? $price : null;
    }

    /** "Rome 2 · Florence 2 · Venice 2" */
    public function citiesLine(): string
    {
        return collect((array) ($this->cities ?? []))
            ->map(fn (array $c) => trim(($c['name'] ?? '').' '.($c['nights'] ?? '')))
            ->filter()->implode(' · ');
    }

    /** "Rome, Florence, Venice" */
    public function cityNames(): string
    {
        return collect((array) ($this->cities ?? []))->pluck('name')->filter()->implode(', ');
    }

    /** "Frecciarossa 2nd class, Roma Termini to Firenze S.M.N.; Private sedan transfer, Fiumicino airport to hotel" */
    public function transportLine(): string
    {
        return collect((array) ($this->transport ?? []))
            ->map(function (array $leg) {
                $route = trim(($leg['from'] ?? '').(isset($leg['to']) && $leg['to'] !== '' ? ' to '.$leg['to'] : ''));

                return trim(($leg['mode'] ?? '').($route !== '' ? ', '.$route : ''));
            })->filter()->implode('; ');
    }

    /** Prefilled WhatsApp text, tagged with trip slug and market code for Lead Chats filtering (6f). */
    public function enquiryMessage(Market $market): string
    {
        return 'Hi Beyond Passports, I am interested in the '.$this->name.' trip ('.$this->nights.' nights, '
            .$this->cityNames().') from '.$market->label().'. I need a Schengen visa. [TRIP:'.$this->slug.'] ['
            .strtoupper($market->code).']';
    }

    public function url(Market $market): string
    {
        return market_url('/tour-packages/'.$this->slug, $market);
    }
}
```

- [ ] **Step 5: Create the factory**

Create `database/factories/TourPackageFactory.php`:

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TourPackage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<TourPackage> */
final class TourPackageFactory extends Factory
{
    protected $model = TourPackage::class;

    public function definition(): array
    {
        $name = fake()->unique()->city().' Escape';

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'where_line' => 'Rome · Florence',
            'nights' => 5,
            'cities' => [['name' => 'Rome', 'nights' => 3], ['name' => 'Florence', 'nights' => 2]],
            'hotel_tier' => '4-star central, breakfast included',
            'transport' => [['mode' => 'Frecciarossa 2nd class', 'from' => 'Roma Termini', 'to' => 'Firenze S.M.N.']],
            'highlights' => ['Colosseum and Forum', 'Uffizi Gallery'],
            'image' => '/assets/tours/italy.jpg',
            'flag_css' => 'linear-gradient(90deg,#009246 33%,#fff 33% 66%,#CE2B37 66%)',
            'markets' => ['uk', 'za', 'ae', 'us', 'ca'],
            'sort' => 100,
            'published' => true,
        ];
    }
}
```

- [ ] **Step 6: Run to verify it passes**

Run: `php artisan test --filter=TourPackageModelTest`
Expected: PASS (5 tests). If `price_from` compares as float, keep the `decimal:2` cast and assert `'18900.00'` as written.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_10_06_000001_create_tour_packages_tables.php app/Models/TourPackage.php app/Models/TourPackagePrice.php database/factories/TourPackageFactory.php tests/Unit/TourPackageModelTest.php
git commit -m "feat(tours): tour_packages, destinations pivot and per-market prices with models + factory"
```

---

### Task 2: Seed the six trips from config

**Files:**
- Create: `database/seeders/TourPackageSeeder.php`
- Modify: `database/seeders/ProductionSeeder.php`
- Test: `tests/Feature/TourPackageSeederTest.php`

**Interfaces:**
- Produces: six published rows, all five markets, no prices, main destinations linked when `SchengenSeeder` rows exist.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/TourPackageSeederTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TourPackage;
use Database\Seeders\SchengenSeeder;
use Database\Seeders\TourPackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TourPackageSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_six_trips_idempotently_with_named_transport_and_no_prices(): void
    {
        (new SchengenSeeder)->run();
        (new TourPackageSeeder)->run();
        (new TourPackageSeeder)->run();

        $this->assertSame(6, TourPackage::count());
        $this->assertSame(
            ['paris-long-weekend', 'amsterdam-and-the-rhine', 'italy-highlights', 'greek-islands-escape', 'spain-and-portugal', 'best-of-western-europe'],
            TourPackage::query()->ordered()->pluck('slug')->all()
        );
        foreach (TourPackage::all() as $p) {
            $this->assertTrue($p->published, $p->slug);
            $this->assertSame(['uk', 'za', 'ae', 'us', 'ca'], $p->markets, $p->slug);
            $this->assertSame(0, $p->prices()->count(), $p->slug);
            $this->assertNotEmpty($p->transport, $p->slug);
            $this->assertStringNotContainsStringIgnoringCase('flight', json_encode($p->transport), $p->slug);
            $this->assertNotNull($p->main_destination_id, $p->slug);
            $this->assertSame((int) collect($p->cities)->sum('nights'), $p->nights, $p->slug);
        }
        $italy = TourPackage::where('slug', 'italy-highlights')->first();
        $this->assertSame('Italy', $italy->mainDestination->name);
        $this->assertStringContainsString('Frecciarossa 2nd class', $italy->transportLine());
        $this->assertSame(3, TourPackage::where('slug', 'best-of-western-europe')->first()->destinations()->count());
    }

    public function test_seeder_survives_missing_destinations(): void
    {
        (new TourPackageSeeder)->run();
        $this->assertSame(6, TourPackage::count());
        $this->assertNull(TourPackage::where('slug', 'italy-highlights')->first()->main_destination_id);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=TourPackageSeederTest`
Expected: FAIL with "Class Database\Seeders\TourPackageSeeder not found".

- [ ] **Step 3: Create the seeder**

Create `database/seeders/TourPackageSeeder.php`:

```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\TourPackage;
use Illuminate\Database\Seeder;

/**
 * Migrates the six trips that lived in config('ukv.tours.packages') into the catalogue with the
 * deep-dive 6a/6b anatomy: nights per city, hotel tier, NAMED ground transport legs, no flights,
 * main destination (consulate), all five markets, NO prices (owner sets per market after the
 * travel-seller memo for that market; spec section 3 launch gates). Idempotent on slug.
 * Run after SchengenSeeder so destination slugs resolve; tolerates missing destinations.
 */
final class TourPackageSeeder extends Seeder
{
    public function run(): void
    {
        $trips = [
            [
                'slug' => 'paris-long-weekend', 'name' => 'Paris Long Weekend', 'where_line' => 'Paris', 'nights' => 3, 'sort' => 10,
                'cities' => [['name' => 'Paris', 'nights' => 3]],
                'hotel_tier' => '3 to 4-star central Paris, breakfast included',
                'transport' => [
                    ['mode' => 'Private sedan transfer', 'from' => 'Gare du Nord or Charles de Gaulle', 'to' => 'hotel and back'],
                    ['mode' => 'Seine river cruise, one hour (Bateaux Parisiens)', 'from' => 'Port de la Bourdonnais', 'to' => 'round trip'],
                ],
                'highlights' => ['Eiffel Tower and the Seine by boat', 'Louvre or Musée d\'Orsay afternoon', 'Montmartre evening'],
                'image' => '/assets/tours/paris.jpg',
                'flag_css' => 'linear-gradient(90deg,#0055A4 33%,#fff 33% 66%,#EF4135 66%)',
                'main' => 'france', 'destinations' => ['france'],
            ],
            [
                'slug' => 'amsterdam-and-the-rhine', 'name' => 'Amsterdam & the Rhine', 'where_line' => 'Amsterdam · Cologne', 'nights' => 5, 'sort' => 20,
                'cities' => [['name' => 'Amsterdam', 'nights' => 3], ['name' => 'Cologne', 'nights' => 2]],
                'hotel_tier' => '3 to 4-star central, breakfast included',
                'transport' => [
                    ['mode' => 'Private transfer', 'from' => 'Schiphol airport', 'to' => 'hotel'],
                    ['mode' => 'Eurostar 2nd class', 'from' => 'Amsterdam Centraal', 'to' => 'Köln Hauptbahnhof'],
                    ['mode' => 'KD Rhine day cruise', 'from' => 'Cologne', 'to' => 'Königswinter and back'],
                ],
                'highlights' => ['Canal ring and Rijksmuseum', 'Cologne Cathedral', 'Rhine valley by boat'],
                'image' => '/assets/tours/amsterdam.jpg',
                'flag_css' => 'linear-gradient(90deg,#21468B 50%,#FFCE00 50%)',
                'main' => 'netherlands', 'destinations' => ['netherlands', 'germany'],
            ],
            [
                'slug' => 'italy-highlights', 'name' => 'Italy Highlights', 'where_line' => 'Rome · Florence · Venice', 'nights' => 6, 'sort' => 30,
                'cities' => [['name' => 'Rome', 'nights' => 2], ['name' => 'Florence', 'nights' => 2], ['name' => 'Venice', 'nights' => 2]],
                'hotel_tier' => '4-star central, breakfast included',
                'transport' => [
                    ['mode' => 'Private sedan transfer', 'from' => 'Fiumicino airport', 'to' => 'hotel'],
                    ['mode' => 'Frecciarossa 2nd class', 'from' => 'Roma Termini', 'to' => 'Firenze S.M.N.'],
                    ['mode' => 'Frecciarossa 2nd class', 'from' => 'Firenze S.M.N.', 'to' => 'Venezia Santa Lucia'],
                    ['mode' => 'Alilaguna water bus', 'from' => 'Venice Marco Polo', 'to' => 'San Marco'],
                ],
                'highlights' => ['Colosseum and Forum', 'Uffizi and the Duomo', 'Grand Canal at dusk'],
                'image' => '/assets/tours/italy.jpg',
                'flag_css' => 'linear-gradient(90deg,#009246 33%,#fff 33% 66%,#CE2B37 66%)',
                'main' => 'italy', 'destinations' => ['italy'],
            ],
            [
                'slug' => 'greek-islands-escape', 'name' => 'Greek Islands Escape', 'where_line' => 'Athens · Santorini · Mykonos', 'nights' => 6, 'sort' => 40,
                'cities' => [['name' => 'Athens', 'nights' => 2], ['name' => 'Santorini', 'nights' => 2], ['name' => 'Mykonos', 'nights' => 2]],
                'hotel_tier' => '3 to 4-star, breakfast included',
                'transport' => [
                    ['mode' => 'Private transfer', 'from' => 'Athens airport', 'to' => 'Plaka hotel'],
                    ['mode' => 'Blue Star or SeaJets ferry, economy', 'from' => 'Piraeus', 'to' => 'Santorini'],
                    ['mode' => 'SeaJets ferry, economy', 'from' => 'Santorini', 'to' => 'Mykonos'],
                    ['mode' => 'Blue Star ferry, economy', 'from' => 'Mykonos', 'to' => 'Piraeus'],
                ],
                'highlights' => ['Acropolis at opening time', 'Oia sunset', 'Mykonos old town'],
                'image' => '/assets/tours/greece.jpg',
                'flag_css' => 'linear-gradient(180deg,#0D5EAF 0 20%,#fff 20% 40%,#0D5EAF 40% 60%,#fff 60% 80%,#0D5EAF 80%)',
                'main' => 'greece', 'destinations' => ['greece'],
            ],
            [
                'slug' => 'spain-and-portugal', 'name' => 'Spain & Portugal', 'where_line' => 'Madrid · Seville · Lisbon', 'nights' => 9, 'sort' => 50,
                'cities' => [['name' => 'Madrid', 'nights' => 3], ['name' => 'Seville', 'nights' => 3], ['name' => 'Lisbon', 'nights' => 3]],
                'hotel_tier' => '4-star central, breakfast included',
                'transport' => [
                    ['mode' => 'Private transfer', 'from' => 'Madrid Barajas', 'to' => 'hotel'],
                    ['mode' => 'Renfe AVE, Turista class', 'from' => 'Madrid Atocha', 'to' => 'Sevilla Santa Justa'],
                    ['mode' => 'Alsa coach', 'from' => 'Seville Plaza de Armas', 'to' => 'Lisbon Sete Rios'],
                ],
                'highlights' => ['Prado and Retiro', 'Real Alcázar of Seville', 'Belém and the tram 28 route'],
                'image' => '/assets/tours/spain-portugal.jpg',
                'flag_css' => 'linear-gradient(90deg,#AA151B 50%,#046A38 50%)',
                'main' => 'spain', 'destinations' => ['spain', 'portugal'],
            ],
            [
                'slug' => 'best-of-western-europe', 'name' => 'Best of Western Europe', 'where_line' => 'Paris · Lucerne · Florence · Rome', 'nights' => 13, 'sort' => 60,
                'cities' => [['name' => 'Paris', 'nights' => 4], ['name' => 'Lucerne', 'nights' => 3], ['name' => 'Florence', 'nights' => 3], ['name' => 'Rome', 'nights' => 3]],
                'hotel_tier' => '3 to 4-star central, breakfast included',
                'transport' => [
                    ['mode' => 'Private sedan transfers', 'from' => 'arrival and departure airports', 'to' => 'hotel'],
                    ['mode' => 'TGV Lyria 2nd class, then SBB InterRegio', 'from' => 'Paris Gare de Lyon', 'to' => 'Basel SBB and Lucerne'],
                    ['mode' => 'EuroCity 2nd class via the Gotthard, then Frecciarossa', 'from' => 'Lucerne', 'to' => 'Milano Centrale and Florence'],
                    ['mode' => 'Frecciarossa 2nd class', 'from' => 'Firenze S.M.N.', 'to' => 'Roma Termini'],
                ],
                'highlights' => ['Paris by river and on foot', 'Lake Lucerne and Mount Pilatus', 'Florence and Rome by high-speed rail'],
                'image' => '/assets/tours/western-europe.jpg',
                'flag_css' => 'linear-gradient(90deg,#0055A4 33%,#DA291C 33% 66%,#009246 66%)',
                'main' => 'italy', 'destinations' => ['france', 'switzerland', 'italy'],
            ],
        ];

        $bySlug = Destination::query()->whereIn('slug', collect($trips)->flatMap(fn ($t) => $t['destinations'])->unique()->all())
            ->pluck('id', 'slug');

        foreach ($trips as $t) {
            $main = $t['main'];
            $destinations = $t['destinations'];
            unset($t['main'], $t['destinations']);

            $t['markets'] = ['uk', 'za', 'ae', 'us', 'ca'];
            $t['published'] = true;
            $t['main_destination_id'] = $bySlug[$main] ?? null;

            $slug = $t['slug'];
            unset($t['slug']);
            $package = TourPackage::updateOrCreate(['slug' => $slug], $t);
            $package->destinations()->sync(collect($destinations)->map(fn ($d) => $bySlug[$d] ?? null)->filter()->values()->all());
        }
    }
}
```

- [ ] **Step 4: Register in `ProductionSeeder`**

In `database/seeders/ProductionSeeder.php`, after the `SchengenCentreSeeder::class,` line add:

```php
            TourPackageSeeder::class,        // six visa-led trips (SP2); prices stay null until set per market
```

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter=TourPackageSeederTest`
Expected: PASS (2 tests).

- [ ] **Step 6: Commit**

```bash
git add database/seeders/TourPackageSeeder.php database/seeders/ProductionSeeder.php tests/Feature/TourPackageSeederTest.php
git commit -m "feat(tours): seed the six trips with named transport and main consulate; no prices"
```

---

### Task 3: Compliance texts in config and the eight FAQs

**Files:**
- Modify: `config/ukv.php` (`tours` block: `nav_label`, `enquiry_only`, `price_stale_days`, `compliance`; keep `packages` until Task 10)
- Create: `app/Support/TourFaqs.php`
- Test: `tests/Unit/TourFaqsTest.php`

**Interfaces:**
- Produces: `config('ukv.tours.compliance.{uk|za|ae|us|ca}')` strings; `TourFaqs::for(Market $m): array<int, array{q:string, a:string}>` (8 items); `TourFaqs::compliance(Market $m): string` (falls back to uk text).

Amendment note (4), owner-pending: once confirmed, remove the "Where hotel and transport are booked together through us, the Package Travel ... Regulations 2018 apply ..." sentence from the uk text and rewrite each text as mechanics (who you book with, what we send, what we do not do), never a denial; then drop the statute exception from `TourFaqsTest::test_no_banned_words_or_package_in_any_market`. Amendment (3): FAQ 3's answer changes to the referral mechanics (one named licensed operator, you book directly, no commission).

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/TourFaqsTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Market;
use App\Support\TourFaqs;
use Tests\TestCase;

final class TourFaqsTest extends TestCase
{
    public function test_eight_faqs_with_market_currency_and_uk_only_atol_line(): void
    {
        $uk = TourFaqs::for(Market::uk());
        $za = TourFaqs::for(Market::fromCode('za'));
        $this->assertCount(8, $uk);
        $this->assertCount(8, $za);
        $this->assertSame('Is the trip price the same as the visa fee?', $uk[0]['q']);
        $this->assertStringContainsString('GBP', $uk[0]['a']);
        $this->assertStringContainsString('ZAR', $za[0]['a']);
        $this->assertStringContainsString('ATOL', $uk[1]['a']);
        $this->assertStringNotContainsString('ATOL', $za[1]['a']);
        $this->assertSame('Can you arrange halal, vegetarian or Indian meals, or family rooms?', $uk[7]['q']);
    }

    public function test_no_banned_words_or_package_in_any_market(): void
    {
        foreach (['uk', 'za', 'ae', 'us', 'ca'] as $code) {
            $text = json_encode(TourFaqs::for(Market::fromCode($code))).TourFaqs::compliance(Market::fromCode($code));
            // Statute-name exception: removed once amendment (4) is confirmed.
            $text = str_replace('Package Travel and Linked Travel Arrangements Regulations 2018', '', $text);
            foreach (['guaranteed', 'fast-track', 'priority', 'early appointment', 'package', "\u{2014}"] as $banned) {
                $this->assertStringNotContainsStringIgnoringCase($banned, $text, "$code contains $banned");
            }
        }
    }

    public function test_compliance_per_market_with_uk_fallback(): void
    {
        $this->assertStringContainsString('not ATOL protected', TourFaqs::compliance(Market::uk()));
        $this->assertStringContainsString('not a UAE-licensed tour operator', TourFaqs::compliance(Market::fromCode('ae')));
        $this->assertStringNotContainsString('ATOL', TourFaqs::compliance(Market::fromCode('ae')));
        $this->assertStringContainsString('indicative in rand', TourFaqs::compliance(Market::fromCode('za')));
        $this->assertStringContainsString('seller of travel', TourFaqs::compliance(Market::fromCode('us')));
        $this->assertStringContainsString('TICO', TourFaqs::compliance(Market::fromCode('ca')));
        config(['ukv.tours.compliance.ca' => null]);
        $this->assertStringContainsString('not ATOL protected', TourFaqs::compliance(Market::fromCode('ca')));
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=TourFaqsTest`
Expected: FAIL with "Class App\Support\TourFaqs not found".

- [ ] **Step 3: Edit the `tours` block in `config/ukv.php`**

Replace the lines from `'tours' => [` through `'enquiry_only' => (bool) env('UKV_TOURS_ENQUIRY_ONLY', true),` with:

```php
    'tours' => [
        'nav_label'        => 'Plan a trip',
        // Hard-coded: there is no checkout for trips. Hotels and transport are quoted as separate
        // supplier lines on WhatsApp and paid to each supplier (PTR 2018 reg 2; no ATOL held).
        'enquiry_only'     => true,
        // Filament flags a per-market price whose price_checked_at is older than this.
        'price_stale_days' => 90,
        // Per-market compliance strip text (tours deep dive 6d). Rendered through the locked
        // partials.disclaimer-strip. Counsel edits land here (launch gates L1/L2 in the SP2 spec);
        // a missing market falls back to 'uk'. Phase 1 is zero-fee enquiry referral, so the US/CA
        // wording is the honest "not registered" branch. OWNER-PENDING amendment (4): rewrite as
        // mechanics only and drop the PTR sentence from 'uk'.
        'compliance' => [
            'uk' => 'Beyond Passports Ltd prepares your Schengen visa application. Hotels and ground transport shown are indicative, priced separately from the visa service, and arranged on enquiry with named suppliers. Flights are not included and we do not sell flights, so bookings are not ATOL protected. Where hotel and transport are booked together through us, the Package Travel and Linked Travel Arrangements Regulations 2018 apply and we will tell you who the organiser is and how your money is protected before you pay. Appointment dates are set by the visa centre, not by us. Visa decisions are made solely by the consulate.',
            'ae' => 'Beyond Passports Ltd prepares your Schengen visa application. Hotels and ground transport shown are indicative, priced separately from the visa service, and arranged on enquiry with named suppliers. We do not sell flights. We are not a UAE-licensed tour operator; hotels and transport are arranged through licensed partners named in your quote. Visa appointment availability is controlled by the visa centre (VFS Global, BLS or TLScontact), not by us. Visa decisions are made solely by the consulate.',
            'za' => 'Beyond Passports Ltd prepares your Schengen visa application. Hotels and ground transport shown are indicative, priced separately from the visa service, and arranged on enquiry with named suppliers. Flights are not included and we do not sell flights. Prices are indicative in rand, converted from euro supplier rates on the date shown. We are not ASATA members. Travel insurance is required for every Schengen application. Appointment dates are set by the visa centre, not by us. Visa decisions are made solely by the consulate.',
            'us' => 'Beyond Passports Ltd prepares your Schengen visa application. Hotels and ground transport shown are indicative, priced separately from the visa service, and arranged on enquiry with named suppliers. We do not sell flights or air-inclusive trips. We are not registered as a seller of travel in any US state; hotel and transport bookings are made by you directly with the named supplier, or through a licensed local operator we introduce. Appointment dates are set by the visa centre or consulate, not by us. Visa decisions are made solely by the consulate.',
            'ca' => 'Beyond Passports Ltd prepares your Schengen visa application. Hotels and ground transport shown are indicative, priced separately from the visa service, and arranged on enquiry with named suppliers. We do not sell flights or air-inclusive trips. We are not registered with TICO; the Ontario travel industry compensation fund does not apply to arrangements made through us. Hotel and transport bookings are made by you directly with the named supplier, or through a licensed local operator we introduce. Appointment dates are set by the visa centre or consulate, not by us. Visa decisions are made solely by the consulate.',
        ],
```

Leave the existing `'packages' => [ ... ]` array in place for now (deleted in Task 10).

- [ ] **Step 4: Create `app/Support/TourFaqs.php`**

```php
<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The eight trip FAQs (tours deep dive 6e) and the per-market compliance text (6d). One source for
 * the on-page accordion and the FAQPage JSON-LD so they can never disagree. Honest by construction:
 * three separate amounts, no flights, no online payment, refundable hotels, consulate decides.
 * OWNER-PENDING amendment (3): FAQ 3 becomes the option (a) referral mechanics.
 */
final class TourFaqs
{
    /** @return array<int, array{q: string, a: string}> */
    public static function for(Market $m): array
    {
        $cur = $m->currency();
        $flights = $m->isUk()
            ? 'We do not sell or arrange flights, so nothing we arrange is ATOL protected and nothing on this page is a flight-inclusive holiday.'
            : 'We do not sell or arrange flights, and nothing on this page is an air-inclusive trip.';

        return [
            ['q' => 'Is the trip price the same as the visa fee?',
                'a' => 'No. There are three separate amounts: our visa preparation fee, quoted in '.$cur.' before you commit; the consulate fee of EUR 90 per adult, paid by you at the visa centre; and the hotel and ground transport lines, each from a named supplier. Nothing is rolled into one total.'],
            ['q' => 'Do you book flights?',
                'a' => 'No. '.$flights.' You book your own flights once the visa is issued and we match the hotel and transport dates to them. Every price shown is land only.'],
            ['q' => 'Can I pay for the trip online?',
                'a' => 'Not for trips. Everything starts as a WhatsApp quote: the visa service first, then each hotel and transport line with the supplier\'s name and the date it stays refundable. You pay each supplier\'s invoice separately and you see what is paid, to whom and when, before any money moves.'],
            ['q' => 'What happens to the hotel if my visa is refused?',
                'a' => 'Hotels are held on refundable rates until your visa is issued, with the cancel-by date written in the quote. If the visa is refused before that date, the hotel is cancelled at no cost. The consulate fee is never refunded by any consulate, whatever the decision.'],
            ['q' => 'Will the hotel reservation help my visa?',
                'a' => 'Your file includes the hotel confirmation letters and an itinerary that matches them, which is what the consulate asks to see. The decision is the consulate\'s alone; no reservation can secure an outcome and we never say otherwise.'],
            ['q' => 'When should I enquire?',
                'a' => 'As soon as you have a travel window. Appointment lead times differ by consulate and season, and we tell you what the official calendar shows on the day you ask. Hotels stay refundable until the visa is issued, so asking early costs nothing.'],
            ['q' => 'Can I change dates to match my appointment?',
                'a' => 'Yes, while the hotel is still on a refundable rate. Moving into a peak week reprices the hotel and transport lines and we show you the new figures before anything is confirmed.'],
            ['q' => 'Can you arrange halal, vegetarian or Indian meals, or family rooms?',
                'a' => 'Breakfast is the only meal included where a trip says so. Dietary needs and family or connecting rooms depend on the hotel; tell us at enquiry and we flag what each hotel can confirm in writing before you decide.'],
        ];
    }

    /** Compliance strip text for a market, falling back to the UK text so the strip is never empty. */
    public static function compliance(Market $m): string
    {
        $text = config('ukv.tours.compliance.'.$m->code);

        return is_string($text) && $text !== '' ? $text : (string) config('ukv.tours.compliance.uk');
    }
}
```

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter=TourFaqsTest`
Expected: PASS (3 tests).

- [ ] **Step 6: Commit**

```bash
git add config/ukv.php app/Support/TourFaqs.php tests/Unit/TourFaqsTest.php
git commit -m "feat(tours): per-market compliance texts in config and the eight trip FAQs"
```

---

### Task 4: Shared partials: card, compliance strip, FAQ + schema; `visibleText` helper

**Files:**
- Create: `resources/views/partials/tour-card.blade.php`
- Create: `resources/views/partials/tour-compliance-strip.blade.php`
- Create: `resources/views/partials/tour-faq.blade.php`
- Create: `resources/views/partials/tour-schema.blade.php`
- Create: `tests/Feature/TourPackagesUkPageTest.php` (helper `visibleText` only here; page tests are added to this file in Task 5)
- Test: `tests/Feature/TourPartialsTest.php`

**Interfaces:**
- Consumes: `TourPackage`, `Market`, `TourFaqs`, `partials.disclaimer-strip`.
- Produces: `@include('partials.tour-card', ['package' => $p, 'market' => $m])`; `@include('partials.tour-compliance-strip', ['market' => $m, 'wrap' => bool, 'variant' => 'light'|'dark'])`; `@include('partials.tour-faq', ['market' => $m, 'schema' => bool])`; `@include('partials.tour-schema', ['market' => $m, 'package' => ?TourPackage])` (BreadcrumbList when package given); static `TourPackagesUkPageTest::visibleText(string $html): string`.

Amendment note, owner-pending: (2) the card's single "From {X}" block becomes up to two per-service lines (hotel, transport), each with its own supplier and checked date, never summed; (1) card and breadcrumb links follow `TourPackage::url()` so the `/trips` rename needs no card change, but the `visibleText` helper's URL regex and the breadcrumb label change to `/trips`; (4) once the statute sentence is removed, drop the `str_ireplace` exception from `visibleText`.

- [ ] **Step 1: Create the shared helper**

Create `tests/Feature/TourPackagesUkPageTest.php` (Task 5 appends the page tests to this class):

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TourPackagesUkPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Visible copy only: drop scripts, tags, URLs and the statute name (the two allowed "package"
     * carriers). Owner-pending amendments (1) and (4) remove both carriers: then drop the
     * `/tour-packages` alternation and the str_ireplace line.
     */
    public static function visibleText(string $html): string
    {
        $html = preg_replace('#<script\b[^>]*>.*?</script>#si', ' ', $html);
        $text = strip_tags($html);
        $text = preg_replace('#https?://\S+|/tour-packages\S*#', ' ', $text);

        return str_ireplace('Package Travel and Linked Travel Arrangements Regulations 2018', ' ', $text);
    }

    public function test_helper_strips_urls_scripts_and_statute_name(): void
    {
        $html = '<a href="/tour-packages/x">Trip</a><script>var p="package";</script><p>Package Travel and Linked Travel Arrangements Regulations 2018 apply. Flights not included.</p>';
        $text = self::visibleText($html);
        $this->assertStringNotContainsStringIgnoringCase('package', $text);
        $this->assertStringContainsString('Flights not included', $text);
    }
}
```

- [ ] **Step 2: Write the failing partial tests**

Create `tests/Feature/TourPartialsTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TourPackage;
use App\Support\Market;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TourPartialsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['ukv.intl_base_url' => 'https://beyondpassports.com', 'ukv.markets.za.whatsapp' => '27680000000']);
    }

    public function test_card_without_price_shows_anatomy_and_no_number(): void
    {
        $p = TourPackage::factory()->create(['name' => 'Italy Highlights', 'slug' => 'italy-highlights', 'nights' => 6]);
        $html = $this->view('partials.tour-card', ['package' => $p->load('prices'), 'market' => Market::fromCode('za')])->render();

        $this->assertStringContainsString('Italy Highlights', $html);
        $this->assertStringContainsString('6 nights · Rome 3 · Florence 2', $html);
        $this->assertStringContainsString('Hotels', $html);
        $this->assertStringContainsString('4-star central, breakfast included', $html);
        $this->assertStringContainsString('Frecciarossa 2nd class, Roma Termini to Firenze S.M.N.', $html);
        $this->assertStringContainsString('Schengen application prepared by Beyond Passports', $html);
        $this->assertStringContainsString('fee quoted separately', $html);
        $this->assertStringContainsString('Flights not included', $html);
        $this->assertStringContainsString('Price quoted on WhatsApp for your dates', $html);
        $this->assertStringNotContainsString('From R', $html);
        $this->assertStringNotContainsString('Indicative, checked', $html);
        $this->assertStringContainsString('https://wa.me/27680000000?text=', $html);
        $this->assertStringContainsString(rawurlencode('[TRIP:italy-highlights] [ZA]'), $html);
        $this->assertStringContainsString('Ask about Italy Highlights', $html);
        $this->assertStringContainsString('href="https://beyondpassports.com/za/tour-packages/italy-highlights"', $html);
        $this->assertStringNotContainsStringIgnoringCase('package', TourPackagesUkPageTest::visibleText($html));
        $this->assertStringNotContainsString("\u{2014}", $html);
    }

    public function test_card_with_dated_price_shows_from_and_checked_line(): void
    {
        $p = TourPackage::factory()->create();
        $p->prices()->create(['market' => 'za', 'price_from' => 18900, 'price_checked_at' => '2026-10-01', 'season_label' => 'May departures']);
        $html = $this->view('partials.tour-card', ['package' => $p->load('prices'), 'market' => Market::fromCode('za')])->render();

        $this->assertStringContainsString('From R18,900', $html);
        $this->assertStringContainsString('per person, two sharing, May departures', $html);
        $this->assertStringContainsString('Indicative, checked 1 Oct 2026', $html);
        $this->assertStringNotContainsString('Price quoted on WhatsApp', $html);
    }

    public function test_compliance_strip_uses_locked_partial_with_market_text(): void
    {
        $uk = $this->view('partials.tour-compliance-strip', ['market' => Market::uk()])->render();
        $ae = $this->view('partials.tour-compliance-strip', ['market' => Market::fromCode('ae')])->render();
        $this->assertStringContainsString('class="disc-strip', $uk);
        $this->assertStringContainsString('not ATOL protected', $uk);
        $this->assertStringContainsString('not a UAE-licensed tour operator', $ae);
        $this->assertStringNotContainsString('ATOL', $ae);
    }

    public function test_faq_partial_renders_eight_and_optional_faqpage_schema(): void
    {
        $with = $this->view('partials.tour-faq', ['market' => Market::uk(), 'schema' => true])->render();
        $without = $this->view('partials.tour-faq', ['market' => Market::uk(), 'schema' => false])->render();
        $this->assertSame(8, substr_count($with, '<details'));
        $this->assertStringContainsString('"@type":"FAQPage"', $with);
        $this->assertSame(8, substr_count($with, '"@type":"Question"'));
        $this->assertStringNotContainsString('FAQPage', $without);
    }

    public function test_schema_partial_emits_breadcrumbs_for_a_trip(): void
    {
        $p = TourPackage::factory()->create(['name' => 'Paris Long Weekend', 'slug' => 'paris-long-weekend']);
        $html = $this->view('partials.tour-schema', ['market' => Market::uk(), 'package' => $p])->render();
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $html);
        $this->assertStringContainsString('"name":"Plan a trip"', $html);
        $this->assertStringContainsString('"name":"Paris Long Weekend"', $html);
        $this->assertStringContainsString(url('/tour-packages/paris-long-weekend'), $html);
    }
}
```

- [ ] **Step 3: Run to verify it fails**

Run: `php artisan test --filter='TourPartialsTest|TourPackagesUkPageTest'`
Expected: `TourPackagesUkPageTest` PASS (1 test); `TourPartialsTest` FAIL with "View [partials.tour-card] not found".

- [ ] **Step 4: Create `partials/tour-card.blade.php`**

```blade
{{-- Trip card (tours deep dive 6b). One TourPackage in one Market. Named transport legs (Tourloom and
     Rayna "transfers" read as vapour), explicit not-included list (iVisa transparency), price only when
     the owner has set AND dated it for this market (no competitor dates a "from" price). Copy says
     "trip", never "package" (PTR 2018 reg 2). Enquiry-only CTA tagged [TRIP:slug] [CODE].
     OWNER-PENDING amendment (2): the single "From" block becomes per-service lines (hotel, transport),
     each with supplier + checked date, never summed. --}}
@php
  $price = $package->priceFor($market);
  $main  = $package->mainDestination;
  $href  = $package->url($market);
@endphp
<article class="tc" id="trip-{{ $package->slug }}">
  @if ($package->image)
  <a class="tc-img" href="{{ $href }}" style="background-image:url('{{ $package->image }}')" aria-label="{{ $package->name }}"></a>
  @endif
  <div class="tc-body">
    <h3 class="tc-name">@if ($package->flag_css)<span class="tc-flag" style="background:{{ $package->flag_css }}"></span>@endif<a href="{{ $href }}">{{ $package->name }}</a></h3>
    <p class="tc-line">{{ $package->nights }} nights · {{ $package->citiesLine() }}</p>
    <dl class="tc-facts">
      @if ($package->hotel_tier)<div><dt>Hotels</dt><dd>{{ $package->hotel_tier }}</dd></div>@endif
      @if ($package->transportLine() !== '')<div><dt>Getting around</dt><dd>{{ $package->transportLine() }}</dd></div>@endif
      <div><dt>Visa</dt><dd>Schengen application prepared by Beyond Passports@if ($main) ({{ $main->name }} consulate)@endif, fee quoted separately</dd></div>
    </dl>
    @if ($price)
      <p class="tc-price"><b>From {{ $market->symbol() }}{{ number_format((float) $price->price_from, 0) }}</b> per person, two sharing{{ $price->season_label ? ', '.$price->season_label : '' }}</p>
      <p class="tc-checked">Indicative, checked {{ $price->price_checked_at->format('j M Y') }}</p>
    @else
      <p class="tc-price">Price quoted on WhatsApp for your dates</p>
    @endif
    <p class="tc-excl">Flights not included: you book them, we match the itinerary. Also not included: visa fee, insurance, lunches and dinners, city tax.</p>
    <a class="tc-cta" href="{{ $market->chatUrl($package->enquiryMessage($market)) }}" target="_blank" rel="noopener">Ask about {{ $package->name }}</a>
  </div>
</article>
@once
<style>
.tc{background:#fff;border:1px solid #dde3ec;border-radius:16px;overflow:hidden;display:flex;flex-direction:column;text-align:left;font-family:"Outfit",system-ui,sans-serif;color:#16222E;box-shadow:0 20px 40px -30px rgba(20,34,46,.45)}
.tc-img{display:block;height:160px;background-size:cover;background-position:center}
.tc-body{padding:16px 18px 18px;display:flex;flex-direction:column;gap:8px}
.tc-name{margin:0;font-size:20px;line-height:1.15;display:flex;align-items:center;gap:8px}
.tc-name a{color:inherit;text-decoration:none}
.tc-flag{width:20px;height:14px;border-radius:3px;display:inline-block;box-shadow:0 0 0 1px rgba(0,0,0,.08);flex:none}
.tc-line{margin:0;color:#5d6b76;font-size:14px}
.tc-facts{margin:0;display:grid;gap:6px;font-size:13.5px;line-height:1.45}
.tc-facts div{display:grid;grid-template-columns:110px 1fr;gap:8px}
.tc-facts dt{font-weight:700;color:#155E7A}.tc-facts dd{margin:0}
.tc-price{margin:4px 0 0;font-size:15px}.tc-price b{font-size:18px}
.tc-checked{margin:0;font-size:12.5px;color:#5d6b76}
.tc-excl{margin:0;font-size:12.5px;color:#5d6b76;line-height:1.45}
.tc-cta{margin-top:auto;display:inline-flex;justify-content:center;align-items:center;background:#25D366;color:#fff;font-weight:800;padding:12px 16px;border-radius:12px;text-decoration:none;font-size:14px}
.tc-cta:hover{background:#1da851}
@media (max-width:560px){.tc-facts div{grid-template-columns:1fr}}
</style>
@endonce
```

- [ ] **Step 5: Create `partials/tour-compliance-strip.blade.php`**

```blade
{{-- Per-market trips compliance strip (tours deep dive 6d) through the LOCKED disclaimer-strip partial.
     Text comes from config('ukv.tours.compliance.<code>') via TourFaqs::compliance so counsel edits are
     config-only (SP2 spec launch gates L1/L2). OWNER-PENDING amendment (4): texts describe mechanics,
     never a denial; the PTR statute sentence is removed from 'uk'. --}}
@php($__tm = $market ?? \App\Support\Market::current())
@include('partials.disclaimer-strip', [
  'wrap' => $wrap ?? true,
  'variant' => $variant ?? 'light',
  'text' => '<b>Separate services, no flights.</b> '.e(\App\Support\TourFaqs::compliance($__tm)),
])
```

- [ ] **Step 6: Create `partials/tour-faq.blade.php`**

```blade
{{-- Eight trip FAQs (deep dive 6e) as an accordion; FAQPage JSON-LD from the SAME array when
     schema=true (index pages only; detail pages pass false and carry BreadcrumbList instead). --}}
@php
  $__fm = $market ?? \App\Support\Market::current();
  $__faqs = \App\Support\TourFaqs::for($__fm);
@endphp
<div class="tr-faqpanel"><div class="tr-faqd">
  @foreach ($__faqs as $i => $f)
  <details @if ($i === 0) open @endif><summary>{{ $f['q'] }}</summary><p>{{ $f['a'] }}</p></details>
  @endforeach
</div></div>
@if ($schema ?? false)
<script type="application/ld+json">{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'FAQPage',
  'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']]], $__faqs),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
@once
<style>
.tr-faqpanel{background:#fff;border:1px solid #dde3ec;border-radius:18px;padding:6px 30px;max-width:80ch;margin:0 auto;font-family:"Outfit",system-ui,sans-serif}
.tr-faqd details{border-bottom:1px solid #dde3ec;padding:18px 0}.tr-faqd details:last-child{border-bottom:0}
.tr-faqd summary{font-size:18px;color:#16222E;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center;gap:16px}
.tr-faqd summary::-webkit-details-marker{display:none}
.tr-faqd summary::after{content:"+";font-size:22px;color:#155E7A;flex:0 0 auto;font-weight:700}
.tr-faqd details[open] summary::after{content:"\2013"}
.tr-faqd p{margin:12px 0 0;color:#3a4b55;font-size:16px;line-height:1.65}
@media (max-width:560px){.tr-faqpanel{padding:4px 18px}}
</style>
@endonce
```

- [ ] **Step 7: Create `partials/tour-schema.blade.php`**

```blade
{{-- BreadcrumbList for a trip detail page (Home > Plan a trip > {name}). No Offer/price schema: prices
     are per market and often null, and schema must never advertise a price the page does not show.
     OWNER-PENDING amendment (1): the second crumb's item path follows the /trips rename via market_url. --}}
@php($__sm = $market ?? \App\Support\Market::current())
@if (isset($package))
<script type="application/ld+json">{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'BreadcrumbList',
  'itemListElement' => [
    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => market_url('/', $__sm)],
    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Plan a trip', 'item' => market_url('/tour-packages', $__sm)],
    ['@type' => 'ListItem', 'position' => 3, 'name' => $package->name, 'item' => $package->url($__sm)],
  ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
```

- [ ] **Step 8: Run to verify it passes**

Run: `php artisan test --filter='TourPartialsTest|TourPackagesUkPageTest'`
Expected: PASS (6 tests). If `number_format` yields `18,900` but the symbol test fails, confirm `currency_symbol` for `za` is exactly `R`.

- [ ] **Step 9: Commit**

```bash
git add resources/views/partials/tour-card.blade.php resources/views/partials/tour-compliance-strip.blade.php resources/views/partials/tour-faq.blade.php resources/views/partials/tour-schema.blade.php tests/Feature/TourPartialsTest.php tests/Feature/TourPackagesUkPageTest.php
git commit -m "feat(tours): shared trip card, per-market compliance strip, FAQ accordion + FAQPage, breadcrumb schema"
```

---

### Task 5: UK `/tour-packages` rewrite (DB cards, honest copy, eight FAQs)

**Files:**
- Modify: `resources/views/partials/tours-body.blade.php` (hero copy, how-it-works copy, trips grid, FAQ section, CTA copy; proof band untouched)
- Modify: `app/Providers/AppServiceProvider.php` (view composer feeds DB trips + market)
- Modify: `resources/views/public/tours.blade.php` (title, description)
- Modify: `database/seeders/CmsContentPagesSeeder.php` (tour-packages seo_title/seo_description)
- Test: `tests/Feature/TourPackagesUkPageTest.php` (extend the file created in Task 4; keep the `visibleText` helper and its test)

**Interfaces:**
- Consumes: `TourPackage::listFor(Market::uk())`, `partials.tour-card`, `partials.tour-compliance-strip`, `partials.tour-faq`.
- Produces: composer variables for `partials.tours-body`: `tours` (Collection<TourPackage>), `market` (Market), `sla`, `apps`, `revs`, `ins`, `waCheck`, `waConsult`, `waIcon`.

Amendment note, owner-pending: (1) the route stays `/tour-packages` here; once the `/trips` rename is confirmed, the CMS slug, `@section('canonical')`, nav href, `PublicSmokeTest`, `Cms\*` fixtures and the `visibleText` URL regex move with it and 301s are added; copy below already avoids "package" and uses "trip"; (2) the "From £490" assertions become per-service lines; (4) the "not ATOL protected" assertion stays, the statute exception in `visibleText` goes.

- [ ] **Step 1: Write the failing tests**

In `tests/Feature/TourPackagesUkPageTest.php` (created in Task 4) add the imports

```php
use App\Models\TourPackage;
use Database\Seeders\TourPackageSeeder;
```

and add these methods to the class after `test_helper_strips_urls_scripts_and_statute_name`:

```php
    public function test_empty_catalogue_renders_with_fallback_line(): void
    {
        $r = $this->get('/tour-packages');
        $r->assertOk();
        $r->assertSee('No trips listed yet');
    }

    public function test_lists_seeded_trips_with_card_anatomy_and_uk_compliance(): void
    {
        (new TourPackageSeeder)->run();
        $r = $this->get('/tour-packages');
        $r->assertOk();
        $r->assertSee('Paris Long Weekend');
        $r->assertSee('Best of Western Europe');
        $r->assertSee('Frecciarossa 2nd class');
        $r->assertSee('Flights not included');
        $r->assertSee('not ATOL protected');
        $r->assertSee('class="disc-strip', false);
        $r->assertSee(rawurlencode('[TRIP:italy-highlights] [UK]'), false);
        $r->assertSee('href="'.url('/tour-packages/italy-highlights').'"', false);
    }

    public function test_no_package_word_no_flights_claim_no_payment_no_em_dash(): void
    {
        (new TourPackageSeeder)->run();
        $html = $this->get('/tour-packages')->getContent();
        $text = self::visibleText($html);

        $this->assertStringNotContainsStringIgnoringCase('package', $text);
        preg_match('/<meta name="description" content="([^"]*)"/', $html, $m);
        $this->assertStringNotContainsStringIgnoringCase('package', $m[1] ?? '');
        $this->assertStringNotContainsStringIgnoringCase('flights included', $text);
        $this->assertStringNotContainsStringIgnoringCase('one booking', $text);
        $this->assertStringNotContainsStringIgnoringCase('we book your appointment', $text);
        foreach (['guaranteed', 'fast-track', 'priority appointment', 'early appointment', '/checkout', 'stripe'] as $banned) {
            $this->assertStringNotContainsStringIgnoringCase($banned, $html, $banned);
        }
        $this->assertStringNotContainsString("\u{2014}", $html);
    }

    public function test_price_hidden_when_null_and_shown_with_date_when_set(): void
    {
        (new TourPackageSeeder)->run();
        $this->get('/tour-packages')->assertDontSee('From £')->assertDontSee('Indicative, checked')->assertSee('Price quoted on WhatsApp');

        TourPackage::where('slug', 'paris-long-weekend')->first()->prices()
            ->create(['market' => 'uk', 'price_from' => 490, 'price_checked_at' => '2026-10-01', 'season_label' => 'November departures']);

        $r = $this->get('/tour-packages');
        $r->assertSee('From £490');
        $r->assertSee('per person, two sharing, November departures');
        $r->assertSee('Indicative, checked 1 Oct 2026');
    }

    public function test_eight_faqs_with_faqpage_schema_and_nav_label(): void
    {
        (new TourPackageSeeder)->run();
        $html = $this->get('/tour-packages')->getContent();
        $this->assertStringContainsString('"@type":"FAQPage"', $html);
        $this->assertSame(8, substr_count($html, '"@type":"Question"'));
        $this->assertStringContainsString('Is the trip price the same as the visa fee?', $html);
        $this->assertStringContainsString('Plan a trip', $html);
        $this->assertStringNotContainsString('Tour Packages', $html);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=TourPackagesUkPageTest`
Expected: FAIL (no "No trips listed yet", "package" found in copy, FAQPage missing).

- [ ] **Step 3: Replace the view composer in `app/Providers/AppServiceProvider.php`**

Replace the whole `View::composer('partials.tours-body', function ($view) { ... });` block with:

```php
        // Trips body: data injected here (not via an in-partial @php) so every render path has it:
        // coded route, @include, and the CMS locked-include (Review Focus 3 in the SP2 plan).
        // Catalogue comes from the DB (SP2); the UK market is implicit on .co.uk.
        View::composer('partials.tours-body', function ($view) {
            $stats = \App\Support\SiteStats::class;
            $market = \App\Support\Market::uk();
            $view->with([
                'tours'     => \App\Models\TourPackage::listFor($market),
                'market'    => $market,
                'sla'       => $stats::responseSla(),
                'apps'      => $stats::applications(),
                'revs'      => $stats::reversals(),
                'ins'       => $stats::insuranceMin(),
                'waCheck'   => $stats::chatUrl('Hi Beyond Passports, I would like to check my eligibility before planning a trip.'),
                'waConsult' => $stats::chatUrl('Hi Beyond Passports, I would like to talk through a visa-led trip.'),
                'waIcon'    => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width:17px;height:17px;fill:#fff;vertical-align:-3px"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.978-1.607zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>',
            ]);
        });
```

- [ ] **Step 4: Edit `resources/views/partials/tours-body.blade.php`**

Delete the two leading `@php ... @endphp` blocks (lines 2-11 and the `$waIcon` block at lines 126-128); the composer supplies every variable. Keep the `<style>` block but delete the `.tr-cin`, `.tr-card*` and `.tr-incl*` rules (lines 49-76) and add in their place:

```css
  .tr-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;text-align:left}
  .tr-empty{background:#fff;border:1px solid var(--paper-edge);border-radius:16px;padding:28px;max-width:60ch;margin:0 auto}
  @media(max-width:900px){.tr-grid{grid-template-columns:1fr 1fr}}
  @media(max-width:760px){.tr-grid{grid-template-columns:1fr}}
```

Replace section 1 (HERO) text nodes:
- eyebrow: `Visa first. Then the trip.`
- h1: `We prepare the visa.<br>The trip is planned around it.`
- lede: `Most tours leave the Schengen visa to you. We start with it: a named consultant prepares your file, you book the appointment on the official visa centre site, and hotels and named ground transport are held on refundable rates until the visa is issued. Flights are not included.`
- chips: `Refusal-risk check` · `Hotels on refundable rates` · `Named rail and transfers`
- form `.fs`: `Tell us where you're going and your passport. A <x-reg-verify>England and Wales registered</x-reg-verify> service spots what could get you refused before you plan anything.`

Trust bar third item: replace `<span><b>Hotels + transfers</b> included</span>` with `<span><b>Hotels + named</b> transport</span>`.

Replace section 2 (HOW IT WORKS) inner copy:

```blade
    <p class="eyebrow">How it works</p>
    <h2>We prepare the file first. Then you travel.</h2>
    <p class="tr-sub" style="margin:12px auto 0;max-width:52ch">Most tours leave the visa to you. We flip it: the file is ready and the appointment is in your name before a single hotel night is confirmed.</p>
  </div>
  <div class="steps">
    <div class="step"><div class="num">01</div><h3>Risk check</h3><p>Name and number, that is it. An advisor tells you honestly what could get you refused. No payment.</p></div>
    <div class="step"><div class="num">02</div><h3>We prepare the file, you book the appointment</h3><p>We build the application the way a consulate reads it and watch the official calendar with you. Appointments are free and booked in your name on the visa centre site.</p></div>
    <div class="step"><div class="num">03</div><h3>The trip is arranged around the decision</h3><p>Hotels held on refundable rates and named rail or transfers quoted line by line. Flights are yours to book; we match the itinerary to them.</p></div>
  </div>
  <p class="reassure">No payment until after your risk check.</p>
```

Replace section 3 (PACKAGES) entirely with:

```blade
{{-- 3 · TRIPS (DB catalogue, shared card, enquiry-only; SP2 spec section 6) --}}
<section class="tr-sec tr-white" id="trips"><div class="wrap" style="text-align:center">
  <p class="eyebrow">Ways to see Europe</p>
  <h2>Pick the trip. The visa comes first.</h2>
  <p class="tr-sub" style="max-width:60ch;margin:0 auto 40px">Every trip lists its hotel tier, its named ground transport and what is not included. Prices are indicative, dated, and confirmed on WhatsApp for your dates. Flights are never included.</p>
  @if ($tours->isEmpty())
    <div class="tr-empty"><p style="margin:0 0 12px">No trips listed yet. Tell us where you want to go and a named consultant replies with the visa steps first.</p><a class="btn" href="{{ $waConsult }}" target="_blank" rel="noopener">{!! $waIcon !!} Tell us where you want to go</a></div>
  @else
    <div class="tr-grid">
      @foreach ($tours as $package)
        @include('partials.tour-card', ['package' => $package, 'market' => $market])
      @endforeach
    </div>
  @endif
  <p class="tr-pkfoot">Quotes on WhatsApp. No payment until after your risk check.</p>
  @include('partials.tour-compliance-strip', ['market' => $market])
</div></section>
```

Replace section 5 (FAQ) body with:

```blade
{{-- 5 · FAQ (eight trip questions, deep dive 6e; FAQPage schema emitted here) --}}
<section class="tr-sec"><div class="wrap">
  <div class="sec-head"><p class="eyebrow">Questions</p><h2>Questions people ask before planning a trip</h2></div>
  @include('partials.tour-faq', ['market' => $market, 'schema' => true])
</div></section>
```

Delete the old `.tr-faqpanel`/`.tr-faqd` CSS rules from the style block (the partial ships its own). In section 6 (CTA) change the h2 to `Start with the visa. The trip follows.` and the body sentence to `Send your name and number. We will tell you honestly what your chances look like and what the trip would involve. No obligation.` Keep the script block; change its message string to `'Hi Beyond Passports, I would like to check my eligibility before planning a trip.'`.

- [ ] **Step 5: Update `public/tours.blade.php` and the CMS seeder strings**

In `resources/views/public/tours.blade.php` replace the `title` and `description` sections and the header comment:

```blade
{{-- "Plan a trip": visa-led Europe trips. Catalogue from tour_packages (SP2); cards via
     partials.tour-card; enquiry-only (WhatsApp), no prices without a checked date, no flights. --}}
@section('title', 'Plan a trip: Europe trips with the Schengen visa prepared first | Beyond Passports')
@section('description', 'Visa-led Europe trips. We prepare your Schengen visa file first; hotels and named ground transport are held on refundable rates and quoted separately on WhatsApp. Flights not included.')
```

In `database/seeders/CmsContentPagesSeeder.php` set the `tour-packages` entry to:

```php
                'seo_title' => 'Plan a trip: Europe trips with the Schengen visa prepared first | Beyond Passports',
                'seo_description' => 'Visa-led Europe trips. We prepare your Schengen visa file first; hotels and named ground transport are held on refundable rates and quoted separately on WhatsApp. Flights not included.',
```

- [ ] **Step 6: Run to verify it passes, including the CMS golden test**

Run: `php artisan test --filter='TourPackagesUkPageTest|ContentPagesGolden|ContentRouteToggle|PublicSmoke|NavService'`
Expected: PASS. If `test_no_package_word...` fails pointing at a shared partial (Review Focus 5), edit that partial's copy to "trip" and re-run.

- [ ] **Step 7: Commit**

```bash
git add resources/views/partials/tours-body.blade.php app/Providers/AppServiceProvider.php resources/views/public/tours.blade.php database/seeders/CmsContentPagesSeeder.php tests/Feature/TourPackagesUkPageTest.php
git commit -m "feat(tours): UK /tour-packages renders DB trips with honest copy, UK compliance strip, eight FAQs"
```

---

### Task 6: UK detail page `/tour-packages/{slug}`

**Files:**
- Create: `app/Http/Controllers/TourPackageController.php` (`show` now; `index` added in Task 7)
- Create: `resources/views/partials/tour-detail-body.blade.php`
- Create: `resources/views/public/tour-show.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/TourPackageDetailPageTest.php`

**Interfaces:**
- Produces: route `tours.show` (`GET /tour-packages/{slug}`); `@include('partials.tour-detail-body', ['package' => $p, 'market' => $m])`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/TourPackageDetailPageTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TourPackage;
use Database\Seeders\SchengenSeeder;
use Database\Seeders\TourPackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TourPackageDetailPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new SchengenSeeder)->run();
        (new TourPackageSeeder)->run();
    }

    public function test_detail_renders_itinerary_visa_block_not_included_and_cta(): void
    {
        $r = $this->get('/tour-packages/italy-highlights');
        $r->assertOk();
        $r->assertSee('<h1>Italy Highlights</h1>', false);
        $r->assertSee('Rome');
        $r->assertSee('2 nights');
        $r->assertSee('Frecciarossa 2nd class');
        $r->assertSee('Italy consulate');
        $r->assertSee('Flights not included');
        $r->assertSee('not ATOL protected');
        $r->assertSee(rawurlencode('[TRIP:italy-highlights] [UK]'), false);
        $r->assertSee('"@type":"BreadcrumbList"', false);
        $r->assertDontSee('"@type":"FAQPage"', false);
        $r->assertSee('<link rel="canonical" href="'.url('/tour-packages/italy-highlights').'">', false);
        $this->assertStringNotContainsStringIgnoringCase('package', TourPackagesUkPageTest::visibleText($r->getContent()));
    }

    public function test_detail_shows_price_only_when_dated(): void
    {
        $p = TourPackage::where('slug', 'paris-long-weekend')->first();
        $this->get('/tour-packages/paris-long-weekend')->assertDontSee('From £')->assertSee('quoted on WhatsApp');
        $p->prices()->create(['market' => 'uk', 'price_from' => 490, 'price_checked_at' => '2026-10-01']);
        $this->get('/tour-packages/paris-long-weekend')->assertSee('From £490')->assertSee('Indicative, checked 1 Oct 2026');
    }

    public function test_404_for_unknown_unpublished_and_non_uk_trips(): void
    {
        $this->get('/tour-packages/nope')->assertNotFound();
        TourPackage::where('slug', 'greek-islands-escape')->update(['published' => false]);
        $this->get('/tour-packages/greek-islands-escape')->assertNotFound();
        TourPackage::where('slug', 'spain-and-portugal')->update(['markets' => json_encode(['za', 'ae'])]);
        $this->get('/tour-packages/spain-and-portugal')->assertNotFound();
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=TourPackageDetailPageTest`
Expected: FAIL with 404 on the first test.

- [ ] **Step 3: Create the controller**

Create `app/Http/Controllers/TourPackageController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TourPackage;
use App\Support\Market;
use Illuminate\Http\Response;

/**
 * Visa-led trips (SP2). One controller for both hosts: Market::current() is UK on .co.uk and the bound
 * market inside the /{market} group, so the 404 rules (unknown, unpublished, not available in this
 * market) are identical everywhere. Enquiry only; no checkout exists for trips.
 */
final class TourPackageController extends Controller
{
    public function index(): Response
    {
        $market = Market::current();

        return response()->view('market.tours', ['market' => $market, 'packages' => TourPackage::listFor($market)]);
    }

    public function show(string $slug): Response
    {
        $market = Market::current();
        $package = TourPackage::query()->published()->where('slug', $slug)
            ->with(['prices', 'mainDestination', 'destinations'])->first();
        abort_unless($package && $package->availableIn($market), 404);

        return response()->view($market->isUk() ? 'public.tour-show' : 'market.tour-show', [
            'package' => $package,
            'market' => $market,
        ]);
    }
}
```

- [ ] **Step 4: Create `partials/tour-detail-body.blade.php`**

```blade
{{-- Trip detail body, shared by UK (layouts.public) and market (standalone) pages. Same facts as the
     card, expanded: nights per city, named legs, highlights, visa-first timing, what is and is not
     included, dated price only when set for this market, compliance strip, FAQ accordion (no FAQPage
     here; BreadcrumbList is emitted by partials.tour-schema in the page head). --}}
@php
  $price = $package->priceFor($market);
  $main  = $package->mainDestination;
  $wa    = $market->chatUrl($package->enquiryMessage($market));
@endphp
<section class="td">
  <nav class="td-crumbs" aria-label="Breadcrumb"><a href="{{ market_url('/', $market) }}">Home</a> › <a href="{{ market_url('/tour-packages', $market) }}">Plan a trip</a> › <span>{{ $package->name }}</span></nav>
  <h1>{{ $package->name }}</h1>
  <p class="td-lede">{{ $package->nights }} nights · {{ $package->citiesLine() }}. Visa prepared first, hotels on refundable rates, named ground transport, flights not included.</p>
  <a class="td-cta" href="{{ $wa }}" target="_blank" rel="noopener">Ask about {{ $package->name }} on WhatsApp</a>

  <div class="td-grid">
    <div class="td-card">
      <h2>Where you stay</h2>
      <table class="td-table"><tbody>
        @foreach ((array) ($package->cities ?? []) as $c)<tr><th>{{ $c['name'] ?? '' }}</th><td>{{ $c['nights'] ?? '' }} nights</td></tr>@endforeach
      </tbody></table>
      @if ($package->hotel_tier)<p class="td-note">Hotels: {{ $package->hotel_tier }}. Named in your quote, held on a refundable rate until your visa is issued.</p>@endif
    </div>
    <div class="td-card">
      <h2>Getting around</h2>
      <ul class="td-list">
        @foreach ((array) ($package->transport ?? []) as $leg)<li><b>{{ $leg['mode'] ?? '' }}</b>@if (! empty($leg['from'])): {{ $leg['from'] }}@if (! empty($leg['to'])) to {{ $leg['to'] }}@endif @endif</li>@endforeach
      </ul>
      <p class="td-note">Public fares at the class named, booked after your visa is issued.</p>
    </div>
    @if (! empty($package->highlights))
    <div class="td-card">
      <h2>Highlights</h2>
      <ul class="td-list">@foreach ($package->highlights as $h)<li>{{ $h }}</li>@endforeach</ul>
    </div>
    @endif
    <div class="td-card td-visa">
      <h2>Visa first, then the trip</h2>
      <p>Schengen application prepared by Beyond Passports@if ($main) for the {{ $main->name }} consulate (your main destination)@endif, fee quoted separately. We time the application so the decision lands before any hotel stops being refundable. Appointments are free and booked in your name on the official visa centre site; we watch the calendar with you and never sell a slot.</p>
    </div>
  </div>

  <div class="td-card td-price">
    <h2>What it costs</h2>
    @if ($price)
      <p class="td-from"><b>From {{ $market->symbol() }}{{ number_format((float) $price->price_from, 0) }}</b> per person, two sharing{{ $price->season_label ? ', '.$price->season_label : '' }}. Land only.</p>
      <p class="td-note">Indicative, checked {{ $price->price_checked_at->format('j M Y') }}.@if ($price->fx_note) {{ $price->fx_note }}.@endif Hotel and transport are quoted as separate supplier lines and paid to each supplier.</p>
    @else
      <p class="td-from">Hotel and transport are quoted on WhatsApp for your dates, as separate supplier lines, each with the date it stays refundable.</p>
    @endif
    <p class="td-note"><b>Not included:</b> flights (you book them, we match the itinerary), the EUR 90 consulate fee paid at the visa centre, travel insurance, lunches and dinners, city tax. <b>Flights not included.</b></p>
    @if ($market->isUk())
      <p class="td-note">Our visa preparation fee is shown on the <a href="{{ url('/schengen-visa') }}">Schengen visa page</a>.</p>
    @else
      @include('partials.market-price', ['market' => $market])
    @endif
    <a class="td-cta" href="{{ $wa }}" target="_blank" rel="noopener">Ask about {{ $package->name }} on WhatsApp</a>
  </div>

  @include('partials.tour-compliance-strip', ['market' => $market, 'wrap' => false])

  <h2 class="td-faqh">Questions people ask before planning a trip</h2>
  @include('partials.tour-faq', ['market' => $market, 'schema' => false])
</section>
@once
<style>
.td{max-width:1100px;margin:0 auto;padding:40px 24px 56px;font-family:"Outfit",system-ui,sans-serif;color:#16222E}
.td-crumbs{font-size:13px;color:#5d6b76;margin:0 0 12px}.td-crumbs a{color:#155E7A}
.td h1{font-size:clamp(28px,4.4vw,44px);line-height:1.08;margin:0 0 10px}
.td-lede{font-size:18px;line-height:1.5;max-width:62ch;margin:0 0 18px;color:#2a3a47}
.td-cta{display:inline-block;background:#25D366;color:#fff;font-weight:800;padding:13px 20px;border-radius:12px;text-decoration:none;margin:0 0 28px}
.td-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin:0 0 16px}
.td-card{background:#fff;border:1px solid #dde3ec;border-radius:16px;padding:18px 20px}
.td-card h2{font-size:18px;margin:0 0 10px}
.td-table{width:100%;border-collapse:collapse;font-size:15px}.td-table th{text-align:left;font-weight:600;padding:6px 0}.td-table td{text-align:right;padding:6px 0;color:#5d6b76}
.td-list{margin:0;padding-left:18px;line-height:1.55;font-size:15px}
.td-note{margin:10px 0 0;font-size:13.5px;color:#5d6b76;line-height:1.5}
.td-visa{background:#eef4f6}
.td-price{margin:0 0 18px}.td-from{margin:0;font-size:17px}.td-from b{font-size:22px}
.td-faqh{font-size:22px;margin:32px 0 14px;text-align:center}
@media (max-width:560px){.td{padding:28px 16px 40px}}
</style>
@endonce
```

- [ ] **Step 5: Create `public/tour-show.blade.php`**

```blade
@extends('layouts.public')

{{-- UK trip detail (SP2 spec 5). Same body partial as the market page; UK chrome from the layout. --}}
@section('title', $package->name.': '.$package->nights.' nights, visa prepared first | Beyond Passports')
@section('description', $package->name.', '.$package->nights.' nights ('.$package->cityNames().'). Schengen visa prepared first, '.($package->hotel_tier ?: 'hotels').', '.($package->transportLine() ?: 'named ground transport').'. Flights not included; quoted on WhatsApp.')
@section('canonical', url('/tour-packages/'.$package->slug))

@push('head')
@include('partials.hreflang', ['path' => '/tour-packages/'.$package->slug, 'only' => $package->markets])
@include('partials.tour-schema', ['package' => $package, 'market' => $market])
@endpush

@section('content')
@include('partials.tour-detail-body', ['package' => $package, 'market' => $market])
@endsection
```

- [ ] **Step 6: Register the route**

In `routes/web.php`, directly after the `/tour-packages` line add:

```php
Route::get('/tour-packages/{slug}', [\App\Http\Controllers\TourPackageController::class, 'show'])->name('tours.show'); // trip detail (SP2); 404 unless published + available in UK
```

- [ ] **Step 7: Run to verify it passes**

Run: `php artisan test --filter='TourPackageDetailPageTest|TourPackagesUkPageTest'`
Expected: PASS. The hreflang partial ignores the `only` key until Task 8; that is fine here.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/TourPackageController.php resources/views/partials/tour-detail-body.blade.php resources/views/public/tour-show.blade.php routes/web.php tests/Feature/TourPackageDetailPageTest.php
git commit -m "feat(tours): UK trip detail page with itinerary, visa-first block, dated price rule, breadcrumbs"
```

---

### Task 7: Market index and detail (replace SP1 interim page)

**Files:**
- Modify: `resources/views/market/tours.blade.php` (rewrite)
- Create: `resources/views/market/tour-show.blade.php`
- Delete: `app/Http/Controllers/Market/MarketToursController.php`
- Modify: `routes/web.php` (market group routes)
- Replace: `tests/Feature/MarketToursPageTest.php`

**Interfaces:**
- Produces: routes `market.tours` (`GET /{market}/tour-packages`) and `market.tours.show` (`GET /{market}/tour-packages/{slug}`), both on `TourPackageController`.

- [ ] **Step 1: Replace the failing test**

Replace `tests/Feature/MarketToursPageTest.php` with:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TourPackage;
use Database\Seeders\TourPackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MarketToursPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'ukv.intl_base_url' => 'https://beyondpassports.com',
            'ukv.markets.za.enabled' => true, 'ukv.markets.za.whatsapp' => '27680000000',
            'ukv.markets.ae.enabled' => true, 'ukv.markets.us.enabled' => true, 'ukv.markets.ca.enabled' => true,
        ]);
        (new TourPackageSeeder)->run();
    }

    public function test_market_index_lists_db_trips_with_tagged_cta_and_trust_strip(): void
    {
        $r = $this->get('/za/tour-packages');
        $r->assertOk();
        $r->assertSee('Paris Long Weekend');
        $r->assertSee('Frecciarossa 2nd class');
        $r->assertSee('https://wa.me/27680000000?text=', false);
        $r->assertSee(rawurlencode('[TRIP:paris-long-weekend] [ZA]'), false);
        $r->assertSee('href="https://beyondpassports.com/za/tour-packages/paris-long-weekend"', false);
        $r->assertSee('We never sell appointments');
        $r->assertSee('"@type":"FAQPage"', false);
        $r->assertSee('<link rel="canonical" href="https://beyondpassports.com/za/tour-packages">', false);
        $this->assertStringNotContainsStringIgnoringCase('package', TourPackagesUkPageTest::visibleText($r->getContent()));
    }

    public function test_compliance_strip_differs_per_market(): void
    {
        $this->get('/ae/tour-packages')->assertSee('not a UAE-licensed tour operator')->assertDontSee('ATOL');
        $this->get('/za/tour-packages')->assertSee('indicative in rand');
        $this->get('/us/tour-packages')->assertSee('seller of travel');
        $this->get('/ca/tour-packages')->assertSee('TICO');
    }

    public function test_price_in_local_currency_only_when_set_for_that_market(): void
    {
        $p = TourPackage::where('slug', 'italy-highlights')->first();
        $p->prices()->create(['market' => 'za', 'price_from' => 18900, 'price_checked_at' => '2026-10-01']);
        $this->get('/za/tour-packages')->assertSee('From R18,900')->assertSee('Indicative, checked 1 Oct 2026');
        $this->get('/ae/tour-packages')->assertDontSee('From AED')->assertDontSee('18,900');
        $this->get('/tour-packages')->assertDontSee('18,900');
    }

    public function test_market_detail_and_404_rules(): void
    {
        $r = $this->get('/za/tour-packages/italy-highlights');
        $r->assertOk();
        $r->assertSee('<h1>Italy Highlights</h1>', false);
        $r->assertSee('indicative in rand');
        $r->assertSee('"@type":"BreadcrumbList"', false);
        $r->assertSee('<link rel="canonical" href="https://beyondpassports.com/za/tour-packages/italy-highlights">', false);

        TourPackage::where('slug', 'italy-highlights')->update(['markets' => json_encode(['uk'])]);
        $this->get('/za/tour-packages/italy-highlights')->assertNotFound();
        $this->get('/za/tour-packages')->assertDontSee('Italy Highlights');
        config(['ukv.markets.za.enabled' => false]);
        $this->get('/za/tour-packages/paris-long-weekend')->assertNotFound();
    }

    public function test_no_payment_or_flights_claims_on_market_pages(): void
    {
        $html = $this->get('/ae/tour-packages')->getContent();
        foreach (['/checkout', 'stripe', 'flights included', 'guaranteed', 'WhatsApp call'] as $banned) {
            $this->assertStringNotContainsStringIgnoringCase($banned, $html, $banned);
        }
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketToursPageTest`
Expected: FAIL (config cards still render; detail route 404).

- [ ] **Step 3: Rewrite `resources/views/market/tours.blade.php`**

```blade
{{-- Market trips index (SP2 spec 5, 6). Standalone page, market chrome, shared card partial,
     per-market compliance strip, null-safe visa fee block, eight FAQs with FAQPage, trust strip. --}}
<!doctype html>
<html lang="{{ $market->locale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="{{ asset('assets/brand/favicon.svg?v=2') }}" type="image/svg+xml">
<title>Visa-led Europe trips from {{ $market->label() }} | Beyond Passports</title>
<meta name="description" content="Europe trips planned around your Schengen visa for applicants in {{ $market->label() }}. Visa prepared first; hotels and named ground transport on refundable rates, quoted separately. Flights not included.">
<link rel="canonical" href="{{ market_url('/tour-packages', $market) }}">
@if (! $market->isIndexable())<meta name="robots" content="noindex, nofollow">@endif
@include('partials.hreflang', ['path' => '/tour-packages'])
@include('partials.analytics-head')
<style>
body{margin:0;font-family:"Outfit",system-ui,sans-serif;color:#16222E;background:#F4F5F6}
.tr{max-width:1100px;margin:0 auto;padding:48px 24px 40px}
.tr h1{font-size:clamp(26px,4vw,40px);margin:0 0 10px}.tr .lede{max-width:62ch;line-height:1.5;margin:0 0 24px}
.tr-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;margin:0 0 20px}
.tr-empty{background:#fff;border:1px solid #dde3ec;border-radius:16px;padding:24px;max-width:60ch}
.tr-cta{display:inline-block;background:#155E7A;color:#fff;font-weight:700;padding:12px 18px;border-radius:12px;text-decoration:none}
.tr-faqh{font-size:22px;margin:32px 0 14px;text-align:center}
@media (max-width:560px){.tr{padding-left:16px;padding-right:16px}}
</style>
</head>
<body>
@include('partials.lp-chrome')
<section class="tr">
  <h1>Visa-led Europe trips from {{ $market->label() }}</h1>
  <p class="lede">We prepare your Schengen visa file first. You book the appointment in your name on the official visa centre site. Hotels are held on refundable rates until the visa is issued and ground transport is named leg by leg. Flights are not included and we do not sell flights. Prices are indicative, dated, and confirmed on WhatsApp for your dates.</p>
  @if ($packages->isEmpty())
    <div class="tr-empty"><p>No trips listed yet for {{ $market->label() }}. Tell us where you want to go and a named consultant replies with the visa steps first.</p><a class="tr-cta" href="{{ $market->chatUrl('Hi Beyond Passports, I would like to plan a visa-led trip from '.$market->label().'. ['.strtoupper($market->code).']') }}" target="_blank" rel="noopener">Tell us where you want to go</a></div>
  @else
    <div class="tr-grid">
      @foreach ($packages as $package)
        @include('partials.tour-card', ['package' => $package, 'market' => $market])
      @endforeach
    </div>
  @endif
  @include('partials.tour-compliance-strip', ['market' => $market, 'wrap' => false])
  @include('partials.market-price', ['market' => $market])
  <h2 class="tr-faqh">Questions people ask before planning a trip</h2>
  @include('partials.tour-faq', ['market' => $market, 'schema' => true])
</section>
@include('partials.market-trust-strip', ['market' => $market])
@include('partials.lp-footer')
@include('partials.utm-capture')
</body>
</html>
```

- [ ] **Step 4: Create `resources/views/market/tour-show.blade.php`**

```blade
{{-- Market trip detail (SP2 spec 5). Same body partial as the UK page, market chrome and trust strip. --}}
<!doctype html>
<html lang="{{ $market->locale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="{{ asset('assets/brand/favicon.svg?v=2') }}" type="image/svg+xml">
<title>{{ $package->name }}: {{ $package->nights }} nights from {{ $market->label() }}, visa prepared first | Beyond Passports</title>
<meta name="description" content="{{ $package->name }}, {{ $package->nights }} nights ({{ $package->cityNames() }}) for applicants in {{ $market->label() }}. Schengen visa prepared first, {{ $package->hotel_tier ?: 'hotels' }}, {{ $package->transportLine() ?: 'named ground transport' }}. Flights not included.">
<link rel="canonical" href="{{ $package->url($market) }}">
@if (! $market->isIndexable())<meta name="robots" content="noindex, nofollow">@endif
@include('partials.hreflang', ['path' => '/tour-packages/'.$package->slug, 'only' => $package->markets])
@include('partials.tour-schema', ['package' => $package, 'market' => $market])
@include('partials.analytics-head')
<style>body{margin:0;font-family:"Outfit",system-ui,sans-serif;color:#16222E;background:#F4F5F6}</style>
</head>
<body>
@include('partials.lp-chrome')
@include('partials.tour-detail-body', ['package' => $package, 'market' => $market])
@include('partials.market-trust-strip', ['market' => $market])
@include('partials.lp-footer')
@include('partials.utm-capture')
</body>
</html>
```

- [ ] **Step 5: Routes and controller removal**

In `routes/web.php`, inside the `/{market}` group, replace

```php
        Route::get('/tour-packages', \App\Http\Controllers\Market\MarketToursController::class)->name('market.tours');
```

with:

```php
        // Visa-led trips per market (SP2): DB catalogue, enquiry only, 404 unless the trip lists this market.
        Route::get('/tour-packages', [\App\Http\Controllers\TourPackageController::class, 'index'])->name('market.tours');
        Route::get('/tour-packages/{slug}', [\App\Http\Controllers\TourPackageController::class, 'show'])->name('market.tours.show');
```

Run: `git rm app/Http/Controllers/Market/MarketToursController.php`

- [ ] **Step 6: Run to verify it passes**

Run: `php artisan test --filter='MarketToursPageTest|MarketSitemapTest|MarketHomePageTest|MarketChromeTest'`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add resources/views/market/tours.blade.php resources/views/market/tour-show.blade.php routes/web.php tests/Feature/MarketToursPageTest.php
git commit -m "feat(tours): market trips index + detail from the DB catalogue; remove interim config controller"
```

---

### Task 8: hreflang `only` filter and sitemap entries

**Files:**
- Modify: `app/Support/MarketAlternates.php`
- Modify: `resources/views/partials/hreflang.blade.php`
- Modify: `app/Http/Controllers/SitemapController.php`
- Modify: `app/Http/Controllers/SitemapIntlController.php`
- Test: `tests/Feature/TourSeoTest.php`

**Interfaces:**
- Produces: `MarketAlternates::for(string $path, ?array $only = null): array`; `@include('partials.hreflang', ['path' => ..., 'only' => ?array])`; UK sitemap `/tour-packages/{slug}` entries; intl sitemap `/{market}/tour-packages/{slug}` entries.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/TourSeoTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TourPackage;
use App\Support\MarketAlternates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TourSeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'ukv.intl_base_url' => 'https://beyondpassports.com', 'ukv.base_url' => 'https://beyondpassports.co.uk',
            'ukv.markets.za.enabled' => true, 'ukv.markets.za.indexable' => true,
            'ukv.markets.ae.enabled' => true, 'ukv.markets.ae.indexable' => true,
            'ukv.markets.us.enabled' => true, 'ukv.markets.us.indexable' => false,
        ]);
    }

    public function test_alternates_only_filter_and_uk_omission(): void
    {
        $alt = MarketAlternates::for('/tour-packages/x', ['uk', 'za']);
        $this->assertSame('https://beyondpassports.co.uk/tour-packages/x', $alt['en-GB']);
        $this->assertSame('https://beyondpassports.com/za/tour-packages/x', $alt['en-ZA']);
        $this->assertArrayNotHasKey('en-AE', $alt);
        $this->assertSame('https://beyondpassports.com', $alt['x-default']);

        $noUk = MarketAlternates::for('/tour-packages/x', ['ae']);
        $this->assertArrayNotHasKey('en-GB', $noUk);
        $this->assertArrayHasKey('en-AE', $noUk);
        $this->assertSame([], MarketAlternates::for('/tour-packages/x', ['us']));
        $this->assertArrayHasKey('en-AE', MarketAlternates::for('/tour-packages'));
    }

    public function test_detail_pages_emit_filtered_reciprocal_hreflang(): void
    {
        TourPackage::factory()->create(['slug' => 'italy-highlights', 'markets' => ['uk', 'za']]);
        $uk = $this->get('/tour-packages/italy-highlights');
        $uk->assertSee('hreflang="en-ZA" href="https://beyondpassports.com/za/tour-packages/italy-highlights"', false);
        $uk->assertDontSee('hreflang="en-AE"', false);
        $za = $this->get('/za/tour-packages/italy-highlights');
        $za->assertSee('hreflang="en-GB" href="https://beyondpassports.co.uk/tour-packages/italy-highlights"', false);
        $za->assertDontSee('hreflang="en-AE"', false);
    }

    public function test_uk_sitemap_lists_published_uk_trips_only(): void
    {
        TourPackage::factory()->create(['slug' => 'yes-uk', 'markets' => ['uk', 'za']]);
        TourPackage::factory()->create(['slug' => 'draft', 'published' => false]);
        TourPackage::factory()->create(['slug' => 'za-only', 'markets' => ['za']]);
        $r = $this->get('/sitemap.xml');
        $r->assertOk();
        $r->assertSee('/tour-packages/yes-uk</loc>', false);
        $r->assertDontSee('/tour-packages/draft', false);
        $r->assertDontSee('/tour-packages/za-only', false);
    }

    public function test_intl_sitemap_lists_trips_per_indexable_market(): void
    {
        TourPackage::factory()->create(['slug' => 'wide', 'markets' => ['uk', 'za', 'ae', 'us']]);
        TourPackage::factory()->create(['slug' => 'za-only', 'markets' => ['za']]);
        TourPackage::factory()->create(['slug' => 'draft', 'published' => false, 'markets' => ['za']]);
        $r = $this->get('/sitemap-intl.xml');
        $r->assertOk();
        $r->assertSee('<loc>https://beyondpassports.com/za/tour-packages/wide</loc>', false);
        $r->assertSee('<loc>https://beyondpassports.com/za/tour-packages/za-only</loc>', false);
        $r->assertSee('<loc>https://beyondpassports.com/ae/tour-packages/wide</loc>', false);
        $r->assertDontSee('/ae/tour-packages/za-only', false);
        $r->assertDontSee('/us/tour-packages/', false);
        $r->assertDontSee('draft', false);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=TourSeoTest`
Expected: FAIL ("Too few arguments" or `en-AE` present).

- [ ] **Step 3: Extend `MarketAlternates::for`**

Replace the method in `app/Support/MarketAlternates.php` with:

```php
    /**
     * @param  array<int,string>|null  $only  market codes (incl. 'uk') this page exists in; null = all
     * @return array<string,string>
     */
    public static function for(string $path, ?array $only = null): array
    {
        $path = '/'.trim($path, '/');
        $markets = array_filter(
            Market::enabled(),
            fn (Market $m) => $m->isIndexable() && ($only === null || in_array($m->code, $only, true))
        );
        if ($markets === []) {
            return [];
        }
        $out = [];
        if ($only === null || in_array('uk', $only, true)) {
            // UK base must be absolute .co.uk regardless of the request host (SP1 Review Focus 3).
            $out['en-GB'] = rtrim(Market::uk()->baseUrl(), '/').($path === '/' ? '/' : $path);
        }
        foreach ($markets as $m) {
            $out[$m->locale()] = market_url($path, $m);
        }
        $out['x-default'] = rtrim((string) config('ukv.intl_base_url'), '/');

        return $out;
    }
```

- [ ] **Step 4: Pass `only` through the partial**

Replace the `@foreach` line in `resources/views/partials/hreflang.blade.php` with:

```blade
@foreach (\App\Support\MarketAlternates::for($path ?? '/', isset($only) ? array_values((array) $only) : null) as $lang => $href)
```

- [ ] **Step 5: UK sitemap entries**

In `app/Http/Controllers/SitemapController.php`, add `use App\Models\TourPackage;` and `use App\Support\Market;` to the imports, and directly after the `foreach ($guideSlugs as $slug) { ... }` loop add:

```php
        // Visa-led trip detail pages (SP2): published trips available in the UK only.
        foreach (TourPackage::listFor(Market::uk()) as $trip) {
            $urls[] = [
                'loc' => $base . '/tour-packages/' . $trip->slug,
                'lastmod' => optional($trip->updated_at)->toDateString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }
```

- [ ] **Step 6: Intl sitemap entries**

In `app/Http/Controllers/SitemapIntlController.php`, add `use App\Models\TourPackage;` and inside the `foreach (Market::enabled() as $m)` loop, after the inner `foreach (self::PATHS as $p) { ... }` add:

```php
            foreach (TourPackage::listFor($m) as $trip) {
                $urls[] = ['loc' => $trip->url($m), 'lastmod' => optional($trip->updated_at)->toDateString() ?: $today, 'changefreq' => 'monthly', 'priority' => '0.6'];
            }
```

- [ ] **Step 7: Run to verify it passes**

Run: `php artisan test --filter='TourSeoTest|MarketSeoTest|MarketSitemapTest|TourPackageDetailPageTest'`
Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add app/Support/MarketAlternates.php resources/views/partials/hreflang.blade.php app/Http/Controllers/SitemapController.php app/Http/Controllers/SitemapIntlController.php tests/Feature/TourSeoTest.php
git commit -m "feat(seo): trip detail hreflang filtered by availability; trips in UK and intl sitemaps"
```

---

### Task 9: Filament `TourPackageResource`

**Files:**
- Create: `app/Filament/Resources/TourPackageResource.php`
- Create: `app/Filament/Resources/TourPackageResource/Pages/ListTourPackages.php`, `CreateTourPackage.php`, `EditTourPackage.php`
- Modify: `tests/Feature/AdminPanelSmokeTest.php` (add `tour-packages`)
- Test: `tests/Feature/TourPackageResourceTest.php`

**Interfaces:**
- Produces: `/admin/tour-packages` index/create/edit; prices Repeater on the `prices` relationship with `price_checked_at` required when `price_from` is filled.

- [ ] **Step 1: Write the failing tests**

In `tests/Feature/AdminPanelSmokeTest.php` add `'tour-packages',` to the `$resources` array. Create `tests/Feature/TourPackageResourceTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\TourPackageResource\Pages\CreateTourPackage;
use App\Filament\Resources\TourPackageResource\Pages\EditTourPackage;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class TourPackageResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
    }

    public function test_create_persists_a_trip_with_markets_and_published_flag(): void
    {
        Livewire::test(CreateTourPackage::class)
            ->fillForm([
                'name' => 'Swiss Alps Escape', 'slug' => 'swiss-alps-escape', 'where_line' => 'Lucerne · Interlaken',
                'nights' => 5, 'hotel_tier' => '3-star, breakfast included', 'markets' => ['uk', 'za'],
                'published' => false, 'sort' => 70,
                'cities' => [['name' => 'Lucerne', 'nights' => 3], ['name' => 'Interlaken', 'nights' => 2]],
                'transport' => [['mode' => 'Swiss Travel Pass, 2nd class', 'from' => 'Zurich airport', 'to' => 'Lucerne and Interlaken']],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $trip = TourPackage::where('slug', 'swiss-alps-escape')->first();
        $this->assertNotNull($trip);
        $this->assertFalse($trip->published);
        $this->assertSame(['uk', 'za'], $trip->markets);
        $this->assertSame('Swiss Travel Pass, 2nd class, Zurich airport to Lucerne and Interlaken', $trip->transportLine());
    }

    public function test_price_without_checked_date_is_rejected(): void
    {
        $trip = TourPackage::factory()->create();
        Livewire::test(EditTourPackage::class, ['record' => $trip->getRouteKey()])
            ->fillForm(['prices' => [['market' => 'za', 'price_from' => 18900, 'price_checked_at' => null]]])
            ->call('save')
            ->assertHasFormErrors(['prices.0.price_checked_at']);

        Livewire::test(EditTourPackage::class, ['record' => $trip->getRouteKey()])
            ->fillForm(['prices' => [['market' => 'za', 'price_from' => 18900, 'price_checked_at' => '2026-10-01', 'season_label' => 'May departures']]])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame('18900.00', $trip->fresh()->prices()->where('market', 'za')->value('price_from'));
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter='TourPackageResourceTest|AdminPanelSmokeTest'`
Expected: FAIL (class not found; `/admin/tour-packages` 404).

- [ ] **Step 3: Create the resource**

Create `app/Filament/Resources/TourPackageResource.php`:

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Concerns\AuthorizesByRole;
use App\Filament\Concerns\HiddenFromEditor;
use App\Filament\Resources\TourPackageResource\Pages;
use App\Models\TourPackage;
use App\Support\Market;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Ops editor for visa-led trips (SP2 spec 11). Prices are per market and render on the site only
 * when BOTH an amount and a checked date exist. Helper texts carry the launch-gate rule: no price for
 * a market until its travel-seller memo is logged and the 6c construction sheet row exists.
 */
final class TourPackageResource extends Resource
{
    use AuthorizesByRole;
    use HiddenFromEditor;

    protected static ?string $model = TourPackage::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationLabel = 'Trips';

    protected static ?string $modelLabel = 'trip';

    protected static ?string $recordTitleAttribute = 'name';

    /** @return array<string,string> */
    private static function marketOptions(): array
    {
        $out = ['uk' => 'United Kingdom (GBP)'];
        foreach (Market::codes() as $code) {
            $m = Market::fromCode($code);
            $out[$code] = $m->label().' ('.$m->currency().')';
        }

        return $out;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identity')->columns(2)->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(120)->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, Forms\Set $set): void {
                        if ($operation === 'create') {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                Forms\Components\TextInput::make('slug')->required()->maxLength(140)->unique(ignoreRecord: true)
                    ->helperText('URL on every host: /tour-packages/{slug}. Copy says "trip"; the path is a technical identifier.'),
                Forms\Components\TextInput::make('where_line')->label('Where (display line)')->required()->maxLength(160)
                    ->placeholder('Rome · Florence · Venice'),
                Forms\Components\TextInput::make('nights')->numeric()->integer()->minValue(1)->maxValue(60)->required(),
                Forms\Components\TextInput::make('sort')->numeric()->integer()->minValue(0)->default(100),
                Forms\Components\Toggle::make('published')->inline(false)
                    ->helperText('Off = 404 everywhere, out of sitemaps and hreflang.'),
                Forms\Components\CheckboxList::make('markets')->options(self::marketOptions())->columns(3)->required()
                    ->helperText('Where this trip is listed. A market must also be enabled in config to serve it.')
                    ->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Itinerary')->schema([
                Forms\Components\Repeater::make('cities')->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(60),
                    Forms\Components\TextInput::make('nights')->numeric()->integer()->minValue(1)->required(),
                ])->columns(2)->addActionLabel('Add city')->reorderable()->default([]),
                Forms\Components\TextInput::make('hotel_tier')->maxLength(160)->placeholder('4-star central, breakfast included'),
                Forms\Components\Repeater::make('transport')->label('Ground transport legs')->schema([
                    Forms\Components\TextInput::make('mode')->required()->maxLength(120)->placeholder('Frecciarossa 2nd class'),
                    Forms\Components\TextInput::make('from')->maxLength(80),
                    Forms\Components\TextInput::make('to')->maxLength(80),
                ])->columns(3)->addActionLabel('Add leg')->reorderable()->default([])
                    ->helperText('Name the operator and class. "Transfers" alone is not allowed; flights are never a leg.'),
                Forms\Components\TagsInput::make('highlights')->placeholder('Add a highlight and press Enter'),
                Forms\Components\TextInput::make('image')->maxLength(255)->placeholder('/assets/tours/italy.jpg'),
                Forms\Components\TextInput::make('flag_css')->label('Flag CSS background')->maxLength(255),
            ]),
            Forms\Components\Section::make('Countries')->columns(2)->schema([
                Forms\Components\Select::make('main_destination_id')->label('Main destination (consulate)')
                    ->relationship('mainDestination', 'name')->searchable()->preload()
                    ->helperText('Where the application is lodged: most nights, or first entry if equal.'),
                Forms\Components\Select::make('destinations')->label('All countries entered')
                    ->relationship('destinations', 'name')->multiple()->preload()->searchable(),
            ]),
            Forms\Components\Section::make('Prices per market (land only, per person, two sharing)')
                ->description('LAUNCH GATE: enter a price for a market only after that market\'s travel-seller memo is logged and the price-construction sheet row (named hotel rate, public fare, FX, date) exists. A price without a checked date never renders.')
                ->schema([
                    Forms\Components\Repeater::make('prices')->relationship()->schema([
                        Forms\Components\Select::make('market')->options(self::marketOptions())->required()->distinct()->native(false),
                        Forms\Components\TextInput::make('price_from')->numeric()->minValue(0)->step('1'),
                        Forms\Components\TextInput::make('season_label')->maxLength(120)->placeholder('May and June departures; peak dates higher'),
                        Forms\Components\TextInput::make('fx_note')->maxLength(160)->placeholder('EUR 1 = R 20.10 on 1 Oct 2026'),
                        Forms\Components\DatePicker::make('price_checked_at')->label('Price checked on')
                            ->required(fn (Get $get): bool => filled($get('price_from')))
                            ->helperText('Required whenever a price is set; shown on the site as "Indicative, checked {date}".'),
                    ])->columns(5)->addActionLabel('Add market price')->default([]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nights')->sortable(),
                Tables\Columns\TextColumn::make('markets')->badge(),
                Tables\Columns\IconColumn::make('published')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('prices_count')->counts('prices')->label('Priced markets'),
                Tables\Columns\TextColumn::make('stale')->label('Stale prices')->badge()->color('danger')
                    ->getStateUsing(fn (TourPackage $r): int => $r->prices->filter(fn ($p) => $p->price_from !== null && $p->isStale())->count())
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? (string) $state : ''),
                Tables\Columns\TextColumn::make('sort')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('published'),
                Tables\Filters\SelectFilter::make('market')->options(self::marketOptions())
                    ->query(fn ($query, array $data) => filled($data['value'] ?? null) ? $query->whereJsonContains('markets', $data['value']) : $query),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('sort');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTourPackages::route('/'),
            'create' => Pages\CreateTourPackage::route('/create'),
            'edit' => Pages\EditTourPackage::route('/{record}/edit'),
        ];
    }
}
```

- [ ] **Step 4: Create the three page classes**

`app/Filament/Resources/TourPackageResource/Pages/ListTourPackages.php`:

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\TourPackageResource\Pages;

use App\Filament\Resources\TourPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListTourPackages extends ListRecords
{
    protected static string $resource = TourPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
```

`CreateTourPackage.php`:

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\TourPackageResource\Pages;

use App\Filament\Resources\TourPackageResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateTourPackage extends CreateRecord
{
    protected static string $resource = TourPackageResource::class;
}
```

`EditTourPackage.php`:

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\TourPackageResource\Pages;

use App\Filament\Resources\TourPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditTourPackage extends EditRecord
{
    protected static string $resource = TourPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
```

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter='TourPackageResourceTest|AdminPanelSmokeTest'`
Expected: PASS. If `assertHasFormErrors(['prices.0.price_checked_at'])` reports a different key shape, read the failing message and use the exact key Filament reports (the rule itself is `required` when `price_from` is filled).

- [ ] **Step 6: Commit**

```bash
git add app/Filament/Resources/TourPackageResource.php app/Filament/Resources/TourPackageResource/Pages tests/Feature/TourPackageResourceTest.php tests/Feature/AdminPanelSmokeTest.php
git commit -m "feat(admin): Trips resource with itinerary, countries and per-market dated prices"
```

---

### Task 10: Remove the config catalogue, full suite, runbook, spec status

**Files:**
- Modify: `config/ukv.php` (delete `'packages' => [...]` under `tours`, update the block comment)
- Modify: `docs/GO-LIVE-RUNBOOK.md`
- Modify: `docs/superpowers/specs/2026-10-06-sp2-tours-catalogue-design.md` (status line)

- [ ] **Step 1: Delete the config array and prove nothing reads it**

In `config/ukv.php` delete the whole `'packages' => [ ... ],` array inside `tours` and replace the block comment above `'tours' => [` with:

```php
    // ── Visa-led trips ("Plan a trip") ────────────────────────────────────────
    // Catalogue lives in the tour_packages tables (Filament > Catalogue > Trips; SP2 spec). This
    // block holds the nav label, the enquiry-only flag (no checkout exists for trips: PTR 2018, no
    // ATOL), the price-staleness window and the per-market compliance strip texts.
```

Run: `grep -rn "tours.packages" app resources routes config database tests`
Expected: no output.

- [ ] **Step 2: Run the whole suite**

Run: `php artisan test`
Expected: all PASS. Fix any regression before continuing; do not skip tests.

- [ ] **Step 3: Runbook section**

Append to `docs/GO-LIVE-RUNBOOK.md`:

```markdown
## Visa-led trips catalogue (SP2)

Spec: docs/superpowers/specs/2026-10-06-sp2-tours-catalogue-design.md.

1. Deploy runs `php artisan migrate` and `ProductionSeeder` (includes `TourPackageSeeder`: six trips,
   all markets, no prices). Re-running never duplicates (keyed on slug).
2. Edit trips in Filament > Catalogue > Trips. A trip is public only when `published` is on AND the
   market is listed under Markets AND (for .com) that market is enabled in `.env`.
3. Prices: launch gate per market. Enter a price only after (a) the market's travel-seller memo is
   logged (UK PTR 2018 read; US CA/FL/WA/HI seller-of-travel; CA TICO/OPC/BC; UAE DET; SA CPA) and
   (b) the price-construction sheet row exists (named hotel rate + date, public fare + class, FX rate +
   date). Always set "Price checked on". The Trips table shows a red "Stale prices" count after 90 days;
   re-check or clear the price.
4. Compliance strip text per market: `config('ukv.tours.compliance.<code>')`. Counsel edits go there,
   then `php artisan config:cache`.
5. Smoke: `/tour-packages` and `/tour-packages/italy-highlights` on .co.uk; `/za/tour-packages` on .com
   once ZA is enabled. Every CTA must open WhatsApp with `[TRIP:<slug>] [<CODE>]`; no page may show a
   price without "Indicative, checked <date>".
```

- [ ] **Step 4: Spec status and commit**

In the SP2 spec change `**Status:** draft for owner review.` to `**Status:** implemented on branch feat/sp2-tours-catalogue (plan 2026-10-06-sp2-tours-catalogue.md); awaiting owner acceptance against section 14.`

```bash
git add config/ukv.php docs/GO-LIVE-RUNBOOK.md docs/superpowers/specs/2026-10-06-sp2-tours-catalogue-design.md
git commit -m "chore(tours): drop config catalogue; runbook for trips, prices and launch gates; spec status"
```

---

## Self-review (done at writing time)

- Spec coverage: 4.1-4.3 (tables, prices, model API) Task 1; 12 (seed) Task 2; 7 (compliance texts) and 9 (eight FAQs) Task 3; 6 (card anatomy, "trip" rule) and 9 (FAQPage/Breadcrumb partials) Task 4; 5 UK index + 6 copy rules + 10 nav label Task 5; 5 UK detail + 13 (404 rules) Task 6; 5 market index/detail + 7 per-market strip + 8 tagged CTA Task 7; 10 hreflang `only` + sitemaps Task 8; 11 Filament Task 9; 12 config removal + runbook Task 10; 3 launch gates appear as helper text (Task 9), config comments (Task 3) and runbook (Task 10), never as build blockers; 14 acceptance items 1-11 map to Tasks 1, 2, 3, 4, 5, 6, 7, 8, 8, 9, 10 respectively; 15 citations live in the spec.
- Review Focus: 1 Tasks 6, 7 tests; 2 Tasks 1, 4, 9 tests; 3 Task 5 step 6 (golden test re-run); 4 Task 8 test; 5 Task 5 step 6 note.
- Global constraints enforced by tests: no payment/flights/package/em-dash (Tasks 5, 7), price rule (Tasks 1, 4, 5, 6, 7), disclaimer-strip via partial (Task 4), banned words (Tasks 3, 5, 7).
- Type consistency: `TourPackage::listFor(Market)`, `priceFor(Market): ?TourPackagePrice`, `enquiryMessage(Market)`, `url(Market)`, `TourFaqs::for(Market)`, `TourFaqs::compliance(Market)`, `MarketAlternates::for(string, ?array)`, partial parameter names `package`, `market`, `schema`, `only` used identically everywhere.
- Known judgement calls left to the executor: Task 5 step 6 shared-partial copy leaks; Task 9 step 5 exact Filament error key for the nested `required`; Task 6/7 `assertSee('<h1>Italy Highlights</h1>')` depends on no attributes on the h1 in `tour-detail-body` (none are added).

## Planner notes
- Two deliberate deviations from the brief, argued in the spec: the `where` field is stored as column `where_line` (reserved SQL word), and per-market prices sit in a child table `tour_package_prices` rather than columns on `tour_packages` (real `date` type for the staleness badge, one row per market).
- Existing-code facts the plan relies on: `partials.tours-body` is a CMS locked-include fed by a view composer in `app/Providers/AppServiceProvider.php` (lines 41-54) and guarded by `tests/Feature/Cms/ContentPagesGoldenTest.php`; `CmsContentPagesSeeder` currently says "wrap flights and hotels" in the tour-packages meta description (a flights claim), fixed in Task 5; `config('ukv.tours.nav_label')` is "Tour Packages" today.
