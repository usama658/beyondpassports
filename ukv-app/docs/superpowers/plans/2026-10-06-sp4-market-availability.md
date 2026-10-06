# Per-Market Availability Boards Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** make `/{market}/schengen-visa` render a per-market availability board fed only by real timestamped snapshots (Destination | Operator | Centre | Booking mode | Earliest date seen | Last checked by {name} | Official free booking link), with the eight honest states, honest per-operator copy, a code-enforced stage gate (live only when a snapshot row exists), and a hard guard that keeps the UK simulated pool (`SlotBoard`, `SlotTiles`) unreachable from any `.com` route.

**Architecture:** additive columns on `supply_nodes` (operator, booking_mode, booking_url) and `centre_availability` (checked_by, portal_state, last_seen_at) with defaults that leave the UK board untouched. `AvailabilityService` grows paste tokens (`none`, `down`, mode suffix, `filling` band) and two `setSnapshot` params. A new `App\Support\MarketBoard` turns nodes + snapshots into row arrays and states; `partials.market-board` renders them; `MarketHubController` swaps the SP1 stub for the board when `MarketBoard::isLive()`. `SlotBoard`/`SlotTiles` throw for non-UK markets.

**Tech Stack:** PHP 8.2+, Laravel 12, Blade, Filament v3 (`UpdateAvailability` page, `SupplyNodeResource`), PHPUnit via `php artisan test` (sqlite in-memory, `RefreshDatabase`), existing `App\Support\Market`, `market_url()`, `MarketHubController`, `partials.market-trust-strip`.

**Spec:** `ukv-app/docs/superpowers/specs/2026-10-06-sp4-market-availability-design.md`. Prerequisite: the SP1 plan `2026-10-06-international-market-foundation.md` is fully merged (Task 0 brought `supply_nodes.market` and `AvailabilityService($market)`).

All paths below are relative to `ukv-app/`. Run every command from `ukv-app/`.

## Global Constraints

- Never call `App\Support\SlotBoard` or `App\Support\SlotTiles` from `app/Support/MarketBoard.php`, `app/Http/Controllers/Market/*`, `resources/views/market/*` or `resources/views/partials/market-board.blade.php` (spec 4.3; static test in Task 3).
- No row shows a date without a non-stale, timestamped snapshot (spec 4.4). No seeded non-UK centres in code (spec A1).
- UK board behaviour unchanged: `CentreAvailability::status()` still returns only `ok|lim|ask`; existing `AvailabilityServiceTest`, `UpdateAvailabilityPageTest`, `SlotBoardTest`, `SlotTilesTest` stay green after every task (spec A3).
- No em-dashes in any user-facing copy. No "guaranteed", "fast-track", "priority appointment", "early appointment" substrings anywhere on `.com`, including inside negations (SP1 `MarketHubPageTest::test_hub_has_no_appointment_promises`).
- Always include `partials.disclaimer-strip` via `partials.market-trust-strip`; never bare markup (memory disclaimer-strip-partial).
- Commit after every task. Never push; the owner pushes (memory no-auto-deploy).

## Review Focus

1. A snapshot inside its 7-day window whose `next_available_on` has slipped into the past must render "We'll check for you", never a past date. Test in Task 4 (`test_state_check_when_date_is_in_the_past`).
2. `slug: none` (checked, nothing seen) must never be confused with `slug: ask` (unknown): `portal_state` `ok` vs `unknown`. Tests in Tasks 2 and 4.
3. `germany: 2026-11-02 waitlist` (mode token in the band position) must be an error row, not a silent write. Test in Task 2.
4. `filling` must map to `lim` for the UK board so no UK view gains an unknown status class. Test in Task 1.
5. Operator copy must render only for operators present in the rows (AE France operator unverified). Test in Task 4 (`test_copy_for_only_present_operators`).
6. The SlotBoard guard must not break the UK lead path: `SlotBoard::recordInquiry()` with a non-UK market bound returns without throwing. Test in Task 3.

---

### Task 0: Preflight (no commit)

- [ ] **Step 1: Confirm SP1 is merged and the suite is green**

Run: `test -f app/Support/Market.php && test -f app/Http/Controllers/Market/MarketHubController.php && grep -c "'market'" app/Models/SupplyNode.php && php artisan test --filter='Market|Availability|SlotBoard|SlotTiles'`
Expected: both files exist, grep prints `1` or more, all tests PASS. If any file is missing, stop: implement `2026-10-06-international-market-foundation.md` first.

---

### Task 1: Board columns on supply nodes and snapshots

**Files:**
- Create: `database/migrations/2026_10_06_000001_add_board_fields_to_supply_nodes_and_centre_availability.php`
- Modify: `app/Models/SupplyNode.php`, `app/Models/CentreAvailability.php`
- Modify: `config/ukv.php` (add `timezone` to each market)
- Modify: `app/Support/Market.php` (add `timezone()`)
- Test: `tests/Unit/CentreAvailabilityStateTest.php`

**Interfaces:**
- Produces: `SupplyNode::OPERATORS` (code => label), `SupplyNode::BOOKING_MODES` (code => label), fillable `operator, booking_mode, booking_url`; `CentreAvailability::BANDS = ['good','filling','limited']`, `CentreAvailability::PORTAL_STATES = ['ok','unavailable','unknown']`, fillable `checked_by, portal_state, last_seen_at` (datetime cast); `Market::timezone(): string`.

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/CentreAvailabilityStateTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\CentreAvailability;
use App\Models\SupplyNode;
use App\Support\Market;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class CentreAvailabilityStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_bands_include_filling_and_it_maps_to_lim_for_the_uk_board(): void
    {
        $this->assertSame(['good', 'filling', 'limited'], CentreAvailability::BANDS);
        $this->assertSame(['ok', 'unavailable', 'unknown'], CentreAvailability::PORTAL_STATES);

        $node = SupplyNode::create(['node_key' => 'n1', 'type' => 'centre', 'name' => 'C1', 'market' => 'za']);
        $a = CentreAvailability::create([
            'supply_node_id' => $node->getKey(),
            'next_available_on' => Carbon::today()->addDays(5)->toDateString(),
            'band' => 'filling',
            'confirmed_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addDays(7),
            'checked_by' => 'Chloe',
            'portal_state' => 'ok',
            'last_seen_at' => Carbon::now(),
        ]);

        $this->assertSame('lim', $a->status());
        $this->assertSame('Chloe', $a->fresh()->checked_by);
        $this->assertSame('ok', $a->fresh()->portal_state);
        $this->assertInstanceOf(Carbon::class, $a->fresh()->last_seen_at);
    }

    public function test_supply_node_board_fields_and_defaults(): void
    {
        $node = SupplyNode::create(['node_key' => 'n2', 'type' => 'centre', 'name' => 'C2', 'market' => 'za', 'operator' => 'capago', 'booking_url' => 'https://fr-za.capago.eu']);
        $this->assertSame('calendar', $node->fresh()->booking_mode);
        $this->assertSame('Capago', SupplyNode::OPERATORS['capago']);
        $this->assertSame('Allocation (offer by email)', SupplyNode::BOOKING_MODES['allocation']);
        $this->assertSame(['calendar', 'waitlist', 'allocation', 'embassy', 'email'], array_keys(SupplyNode::BOOKING_MODES));
    }

    public function test_market_timezones(): void
    {
        $this->assertSame('Europe/London', Market::uk()->timezone());
        $this->assertSame('Africa/Johannesburg', Market::fromCode('za')->timezone());
        $this->assertSame('Asia/Dubai', Market::fromCode('ae')->timezone());
        $this->assertSame('America/New_York', Market::fromCode('us')->timezone());
        $this->assertSame('America/Toronto', Market::fromCode('ca')->timezone());
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=CentreAvailabilityStateTest`
Expected: FAIL (`BANDS` has two entries; unknown column `checked_by`; `timezone()` undefined).

- [ ] **Step 3: Create the migration**

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-market availability boards (SP4 spec 4.1). Additive with defaults: the UK board never reads
 * these columns, so existing behaviour is unchanged. operator / booking_mode / booking_url live on
 * the per-market supply node because operators differ per market for the same destination.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supply_nodes', function (Blueprint $table): void {
            $table->string('operator', 20)->nullable()->after('market');          // vfs|tlscontact|bls|capago|gvcw|prenotami|consulate|email_queue
            $table->string('booking_mode', 20)->default('calendar')->after('operator'); // calendar|waitlist|allocation|embassy|email
            $table->string('booking_url', 255)->nullable()->after('booking_mode'); // official free booking page
        });

        Schema::table('centre_availability', function (Blueprint $table): void {
            $table->string('checked_by', 60)->nullable()->after('source');        // first name of the human who checked
            $table->string('portal_state', 12)->default('ok')->after('checked_by'); // ok|unavailable|unknown
            $table->dateTime('last_seen_at')->nullable()->after('portal_state');  // last time a date was seen (carried forward)
        });
    }

    public function down(): void
    {
        Schema::table('centre_availability', function (Blueprint $table): void {
            $table->dropColumn(['checked_by', 'portal_state', 'last_seen_at']);
        });
        Schema::table('supply_nodes', function (Blueprint $table): void {
            $table->dropColumn(['operator', 'booking_mode', 'booking_url']);
        });
    }
};
```

- [ ] **Step 4: Update `SupplyNode`**

In `app/Models/SupplyNode.php` add `'operator', 'booking_mode', 'booking_url',` to `$fillable` after `'market',` and add these constants directly above `protected $fillable`:

```php
    /** Operator codes -> public labels (SP4 spec 4.1). Unknown code renders "Operator pending verification". */
    public const OPERATORS = [
        'vfs' => 'VFS Global',
        'tlscontact' => 'TLScontact',
        'bls' => 'BLS International',
        'capago' => 'Capago',
        'gvcw' => 'Global Visa Center World',
        'prenotami' => 'Prenot@mi (consulate)',
        'consulate' => 'Consulate direct',
        'email_queue' => 'Consulate email queue',
    ];

    /** Booking modes -> public labels. Keys are also the paste-block mode tokens. */
    public const BOOKING_MODES = [
        'calendar' => 'Online calendar',
        'waitlist' => 'Waitlist',
        'allocation' => 'Allocation (offer by email)',
        'embassy' => 'Embassy direct',
        'email' => 'Email queue',
    ];
```

- [ ] **Step 5: Update `CentreAvailability`**

In `app/Models/CentreAvailability.php` replace `public const BANDS = ['good', 'limited'];` with:

```php
    /** good = Available, filling = Filling, limited = Limited on market boards. UK board maps filling to lim. */
    public const BANDS = ['good', 'filling', 'limited'];

    /** ok = portal reachable; unavailable = portal down / login blocked; unknown = reset, never checked. */
    public const PORTAL_STATES = ['ok', 'unavailable', 'unknown'];
```

Add `'checked_by', 'portal_state', 'last_seen_at',` to `$fillable` after `'note',`. Add `'last_seen_at' => 'datetime',` to `casts()`. In `status()` replace the match with:

```php
        return match ($this->band) {
            'good' => 'ok',
            'filling', 'limited' => 'lim',
            default => 'ask',
        };
```

- [ ] **Step 6: Add `timezone` to config and `Market`**

In `config/ukv.php`, inside each market array add after `'locale'`: `za` `'timezone' => 'Africa/Johannesburg',`; `ae` `'timezone' => 'Asia/Dubai',`; `us` `'timezone' => 'America/New_York',`; `ca` `'timezone' => 'America/Toronto',`.

In `app/Support/Market.php` add after `locale()`:

```php
    /** IANA timezone for displaying "last checked" stamps to this market. UK default. */
    public function timezone(): string { return (string) ($this->cfg['timezone'] ?? 'Europe/London'); }
```

- [ ] **Step 7: Run to verify it passes**

Run: `php artisan test --filter='CentreAvailabilityStateTest|AvailabilityServiceTest|MarketsConfigTest|CentreAvailabilityTest'`
Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add database/migrations/2026_10_06_000001_add_board_fields_to_supply_nodes_and_centre_availability.php app/Models/SupplyNode.php app/Models/CentreAvailability.php config/ukv.php app/Support/Market.php tests/Unit/CentreAvailabilityStateTest.php
git commit -m "feat(board): operator/booking-mode/link on supply nodes; checker, portal state, last-seen on snapshots"
```

---

### Task 2: Paste tokens and snapshot params in AvailabilityService

**Files:**
- Modify: `app/Services/AvailabilityService.php` (`setSnapshot`, `parseBulk`)
- Test: `tests/Feature/AvailabilityServiceMarketBoardTest.php`

**Interfaces:**
- Produces: `setSnapshot(int $supplyNodeId, ?Carbon $nextAvailableOn, ?string $band, string $source = 'manual', ?int $freshnessDays = null, ?string $checkedBy = null, string $portalState = 'ok'): CentreAvailability`; `parseBulk()` rows gain `state` (`dated|none|down|reset`) and `mode` (`?string`).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/AvailabilityServiceMarketBoardTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CentreAvailability;
use App\Models\Destination;
use App\Models\SupplyNode;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class AvailabilityServiceMarketBoardTest extends TestCase
{
    use RefreshDatabase;

    private function dest(string $slug = 'germany'): Destination
    {
        return Destination::create([
            'name' => ucfirst($slug), 'slug' => $slug, 'visa_type' => 'Schengen',
            'govt_fee_gbp' => 0, 'tier_standard_gbp' => 39, 'tier_express_gbp' => 59, 'tier_premium_gbp' => 89,
            'passport_validity_months' => 6,
        ]);
    }

    private function node(string $market = 'za', array $extra = []): SupplyNode
    {
        return SupplyNode::create(array_merge([
            'node_key' => 'n-'.$market.'-'.uniqid(), 'type' => 'centre', 'market' => $market,
            'name' => 'Centre '.$market, 'we_book_here' => true, 'is_global' => false,
        ], $extra));
    }

    public function test_set_snapshot_writes_checker_and_portal_state_and_tracks_last_seen(): void
    {
        $node = $this->node();
        $svc = app(AvailabilityService::class);

        $dated = $svc->setSnapshot($node->getKey(), Carbon::today()->addDays(4), 'good', 'manual', null, 'Chloe', 'ok');
        $this->assertSame('Chloe', $dated->checked_by);
        $this->assertSame('ok', $dated->portal_state);
        $this->assertNotNull($dated->last_seen_at, 'a dated write stamps last_seen_at');
        $seen = $dated->last_seen_at->copy();

        $this->travel(2)->days();
        $none = $svc->setSnapshot($node->getKey(), null, null, 'manual', null, 'Sam', 'ok');
        $this->assertNull($none->next_available_on);
        $this->assertSame('Sam', $none->checked_by);
        $this->assertTrue($none->last_seen_at->equalTo($seen), 'a dateless write carries last_seen_at forward');

        $reset = $svc->setSnapshot($node->getKey(), null, null, 'manual', null, 'Sam', 'unknown');
        $this->assertSame('unknown', $reset->portal_state);
        $this->assertTrue($reset->last_seen_at->equalTo($seen));
    }

    public function test_set_snapshot_rejects_bad_portal_state(): void
    {
        $node = $this->node();
        $this->expectException(\InvalidArgumentException::class);
        app(AvailabilityService::class)->setSnapshot($node->getKey(), null, null, 'manual', null, null, 'broken');
    }

    public function test_parse_bulk_tokens(): void
    {
        $dest = $this->dest('germany');
        $node = $this->node('za');
        $dest->supplyNodes()->attach($node->getKey());

        $input = implode("\n", [
            'germany: 2026-12-01 filling waitlist',  // dated + filling band + mode
            'germany: none',                         // checked, nothing seen
            'germany: none allocation',              // nothing seen + mode
            'germany: down',                         // portal unavailable
            'germany: ask',                          // reset
            'germany: 2026-12-01 waitlist',          // mode where band should be = error
            'germany: 2026-12-01 good',              // legacy UK line still fine
        ]);

        $r = app(AvailabilityService::class)->parseBulk($input, 'za');
        $this->assertSame(6, $r['ok']);
        $this->assertSame(1, $r['errors']);

        [$dated, $none, $noneMode, $down, $ask, $bad, $legacy] = $r['rows'];
        $this->assertSame('dated', $dated['state']);
        $this->assertSame('filling', $dated['band']);
        $this->assertSame('waitlist', $dated['mode']);
        $this->assertSame('none', $none['state']);
        $this->assertNull($none['mode']);
        $this->assertSame('allocation', $noneMode['mode']);
        $this->assertSame('down', $down['state']);
        $this->assertSame('reset', $ask['state']);
        $this->assertTrue($ask['reset']);
        $this->assertStringContainsString('band', strtolower((string) $bad['error']));
        $this->assertSame('dated', $legacy['state']);
        $this->assertSame('good', $legacy['band']);
        $this->assertNull($legacy['mode']);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=AvailabilityServiceMarketBoardTest`
Expected: FAIL (`setSnapshot` takes 5 args; rows have no `state`).

- [ ] **Step 3: Extend `setSnapshot`**

Replace the whole `setSnapshot` method in `app/Services/AvailabilityService.php` with:

```php
    /**
     * Upsert a snapshot for one supply node (keyed on supply_node_id). Market-agnostic: the node id
     * already identifies which market's centre this is.
     *
     * confirmed_at = now(); expires_at = now()+freshness. A null date forces a null band. The band
     * must be a valid CentreAvailability::BANDS member or null. MANUAL-WINS: a 'derived' write does
     * not overwrite an existing fresh 'manual' snapshot. $checkedBy is the human's first name
     * (market boards print "by {name}"). $portalState: ok (reachable), unavailable (down/blocked),
     * unknown (reset). last_seen_at is stamped on dated writes and carried forward on dateless ones
     * so empty rows can say "last seen DD Mon" (SP4 spec 4.1, 4.4).
     */
    public function setSnapshot(
        int $supplyNodeId,
        ?Carbon $nextAvailableOn,
        ?string $band,
        string $source = 'manual',
        ?int $freshnessDays = null,
        ?string $checkedBy = null,
        string $portalState = 'ok',
    ): CentreAvailability {
        if ($nextAvailableOn === null) {
            $band = null;
        }

        if ($band !== null && ! in_array($band, CentreAvailability::BANDS, true)) {
            throw new InvalidArgumentException("Invalid availability band: {$band}");
        }
        if (! in_array($portalState, CentreAvailability::PORTAL_STATES, true)) {
            throw new InvalidArgumentException("Invalid portal state: {$portalState}");
        }

        $existing = CentreAvailability::query()->where('supply_node_id', $supplyNodeId)->first();

        // MANUAL-WINS: a derived write never clobbers a fresh manual snapshot.
        if ($source === 'derived' && $existing && $existing->source === 'manual' && ! $existing->isStale()) {
            return $existing;
        }

        $now = Carbon::now();

        $lastSeen = $existing?->last_seen_at;
        if ($lastSeen === null && $existing !== null && $existing->next_available_on !== null) {
            $lastSeen = $existing->confirmed_at; // legacy rows written before last_seen_at existed
        }
        if ($nextAvailableOn !== null) {
            $lastSeen = $now;
        }

        return CentreAvailability::updateOrCreate(
            ['supply_node_id' => $supplyNodeId],
            [
                'next_available_on' => $nextAvailableOn,
                'band' => $band,
                'source' => $source,
                'confirmed_at' => $now,
                'expires_at' => $now->copy()->addDays($freshnessDays ?? CentreAvailability::FRESHNESS_DAYS),
                'checked_by' => $checkedBy !== null && trim($checkedBy) !== '' ? mb_substr(trim($checkedBy), 0, 60) : null,
                'portal_state' => $portalState,
                'last_seen_at' => $lastSeen,
            ],
        );
    }
```

- [ ] **Step 4: Extend `parseBulk`**

In `parseBulk`, change the row template to include the two new keys:

```php
            $row = [
                'slug' => '',
                'destination' => null,
                'node_id' => null,
                'next_available_on' => null,
                'band' => null,
                'reset' => false,
                'state' => 'reset',   // dated|none|down|reset
                'mode' => null,       // optional booking-mode token (SupplyNode::BOOKING_MODES key)
                'error' => null,
            ];
```

Update the no-colon error text to:

```php
                $row['error'] = 'Line must be "<slug>: <YYYY-MM-DD> <good|filling|limited> [mode]", "<slug>: none [mode]", "<slug>: down" or "<slug>: ask".';
```

Replace everything from the comment `// Reset: "ask" or blank clears the snapshot.` to the end of the loop body (the final `$rows[] = $row; $ok++;`) with:

```php
            // Tokens after the colon. An optional trailing booking-mode token is peeled off first.
            $parts = $rest === '' ? [] : (preg_split('/\s+/', strtolower($rest)) ?: []);
            if (count($parts) > 1 && array_key_exists(end($parts), SupplyNode::BOOKING_MODES)) {
                $row['mode'] = array_pop($parts);
            }
            $first = $parts[0] ?? '';

            // Reset: "ask" or blank -> unknown (We'll check for you).
            if ($first === '' || $first === 'ask') {
                $row['reset'] = true;
                $row['state'] = 'reset';
                $rows[] = $row;
                $ok++;

                continue;
            }

            // Checked, no dates seen -> fresh dateless snapshot, portal ok.
            if ($first === 'none') {
                $row['state'] = 'none';
                $rows[] = $row;
                $ok++;

                continue;
            }

            // Portal unavailable / login blocked.
            if ($first === 'down') {
                $row['state'] = 'down';
                $rows[] = $row;
                $ok++;

                continue;
            }

            // Dated: "<YYYY-MM-DD> <good|filling|limited>".
            $dateStr = $first;
            $bandStr = $parts[1] ?? '';

            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
                $row['error'] = 'Bad date, expected YYYY-MM-DD.';
                $rows[] = $row;
                $errors++;

                continue;
            }

            try {
                $date = Carbon::createFromFormat('Y-m-d', $dateStr);
                if ($date === false || $date->format('Y-m-d') !== $dateStr) {
                    throw new InvalidArgumentException('bad date');
                }
            } catch (\Throwable) {
                $row['error'] = 'Bad date, expected YYYY-MM-DD.';
                $rows[] = $row;
                $errors++;

                continue;
            }

            if (! in_array($bandStr, CentreAvailability::BANDS, true)) {
                $row['error'] = 'Bad band, expected good, filling or limited (a booking mode goes after the band).';
                $rows[] = $row;
                $errors++;

                continue;
            }

            $row['next_available_on'] = $dateStr;
            $row['band'] = $bandStr;
            $row['state'] = 'dated';
            $rows[] = $row;
            $ok++;
```

Update the `parseBulk` docblock first line to: `Parse a bulk ops paste into validated rows for one market. WRITES NOTHING. Tokens: SP4 spec 4.2.`

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter='AvailabilityServiceMarketBoardTest|AvailabilityServiceTest|AvailabilityCommandsTest'`
Expected: PASS. The legacy `test_parse_bulk_handles_valid_reset_bad_date_bad_band_and_unknown_slug` still passes (`maybe` is not a band and not a mode).

- [ ] **Step 6: Commit**

```bash
git add app/Services/AvailabilityService.php tests/Feature/AvailabilityServiceMarketBoardTest.php
git commit -m "feat(board): paste tokens none/down/mode + filling band; setSnapshot checker, portal state, last-seen"
```

---

### Task 3: Hard guard: SlotBoard and SlotTiles are UK-only

**Files:**
- Modify: `app/Support/SlotBoard.php`, `app/Support/SlotTiles.php`
- Test: `tests/Feature/MarketBoardGuardTest.php`

**Interfaces:**
- Produces: `LogicException` from `SlotBoard::allocation()`, `remaining()`, `remainingFor()`, `total()`, `nextDate()` and `SlotTiles::hydrate()` when `Market::current()` is not UK.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MarketBoardGuardTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\Market;
use App\Support\SlotBoard;
use App\Support\SlotTiles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class MarketBoardGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Market::bind(null);
        SlotBoard::setCountriesForTesting(null);
        parent::tearDown();
    }

    public function test_slotboard_throws_for_a_non_uk_market(): void
    {
        SlotBoard::setCountriesForTesting(['France']);
        Market::bind(Market::fromCode('za'));
        $this->expectException(\LogicException::class);
        SlotBoard::remaining();
    }

    public function test_slottiles_throws_for_a_non_uk_market(): void
    {
        Market::bind(Market::fromCode('ae'));
        $this->expectException(\LogicException::class);
        SlotTiles::hydrate('<div class=slnum><b>32</b><span>slots open</span></div>', 'France');
    }

    public function test_slotboard_still_works_for_uk(): void
    {
        SlotBoard::setCountriesForTesting(['France', 'Spain']);
        Market::bind(null);
        $this->assertSame(SlotBoard::TOTAL, array_sum(SlotBoard::remaining()));
    }

    public function test_record_inquiry_never_throws_on_a_market_page(): void
    {
        config(['ukv.slots.dynamic' => true]);
        SlotBoard::setCountriesForTesting(['France']);
        Market::bind(Market::fromCode('us'));
        SlotBoard::recordInquiry('France', Request::create('/us/schengen-visa'));
        $this->assertTrue(true, 'reached without exception');
    }

    public function test_market_code_never_references_the_simulated_pool(): void
    {
        $files = array_merge(
            [base_path('app/Support/MarketBoard.php'), base_path('resources/views/partials/market-board.blade.php')],
            glob(base_path('app/Http/Controllers/Market/*.php')) ?: [],
            glob(base_path('resources/views/market/*.blade.php')) ?: [],
        );
        $this->assertNotEmpty($files);
        foreach ($files as $f) {
            if (! is_file($f)) {
                continue; // MarketBoard.php and the partial are created in Tasks 4 and 5
            }
            $src = (string) file_get_contents($f);
            $this->assertStringNotContainsString('SlotBoard', $src, $f);
            $this->assertStringNotContainsString('SlotTiles', $src, $f);
        }
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketBoardGuardTest`
Expected: FAIL on the two `throws` tests (no exception).

- [ ] **Step 3: Add the guard to `SlotBoard`**

In `app/Support/SlotBoard.php` add `use App\Support\Market;` is unnecessary (same namespace). Add this private method after `setCountriesForTesting()`:

```php
    /**
     * SP4 spec 4.3: the simulated weekly pool is a UK-only mechanic and must never feed a .com market
     * page. Every public read path calls this first. recordInquiry()/decrement() swallow Throwables,
     * so a .com lead can never fail because of this guard.
     */
    private static function assertUkOnly(): void
    {
        if (! Market::current()->isUk()) {
            throw new \LogicException('SlotBoard is the UK simulated pool and is unreachable from market pages (SP4 spec 4.3).');
        }
    }
```

Insert `self::assertUkOnly();` as the first statement of `allocation()`, `remaining()`, `remainingFor()`, `total()` and `nextDate()`.

- [ ] **Step 4: Add the guard to `SlotTiles`**

In `app/Support/SlotTiles.php`, make the first lines of `hydrate()` (before `try {`):

```php
        if (! Market::current()->isUk()) {
            throw new \LogicException('SlotTiles hydrates UK pages only; market pages are snapshot-only (SP4 spec 4.3).');
        }
```

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter='MarketBoardGuardTest|SlotBoardTest|SlotTilesTest|MarketHubPageTest|LpAssemblerTest'`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Support/SlotBoard.php app/Support/SlotTiles.php tests/Feature/MarketBoardGuardTest.php
git commit -m "feat(board): hard guard, SlotBoard/SlotTiles throw for non-UK markets; static no-reference test"
```

---

### Task 4: `MarketBoard` renderer and per-operator copy config

**Files:**
- Create: `app/Support/MarketBoard.php`
- Modify: `config/ukv.php` (new `board_copy` key after `markets`)
- Test: `tests/Feature/MarketBoardTest.php`

**Interfaces:**
- Produces: `MarketBoard::LABELS`, `MarketBoard::isLive(Market): bool`, `MarketBoard::rows(Market, ?Destination = null): array`, `MarketBoard::stateFor(SupplyNode, ?CentreAvailability): string`, `MarketBoard::copyFor(Market, array $rows): array<string,string>` (key `_all` first, then operator codes present).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MarketBoardTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CentreAvailability;
use App\Models\Destination;
use App\Models\SupplyNode;
use App\Support\Market;
use App\Support\MarketBoard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class MarketBoardTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    private function dest(string $name): Destination
    {
        return Destination::create([
            'name' => $name, 'slug' => strtolower($name), 'visa_type' => 'Schengen',
            'govt_fee_gbp' => 0, 'tier_standard_gbp' => 39, 'tier_express_gbp' => 59, 'tier_premium_gbp' => 89,
            'passport_validity_months' => 6,
        ]);
    }

    private function node(Destination $d, array $attrs = []): SupplyNode
    {
        $this->seq++;
        $n = SupplyNode::create(array_merge([
            'node_key' => 'za-'.$this->seq, 'type' => 'centre', 'market' => 'za', 'name' => 'Centre '.$this->seq,
            'operator' => 'vfs', 'booking_mode' => 'calendar', 'booking_url' => 'https://visa.vfsglobal.com/zaf/en/'.strtolower($d->name),
        ], $attrs));
        $d->supplyNodes()->attach($n->getKey());

        return $n;
    }

    private function snap(SupplyNode $n, array $attrs = []): CentreAvailability
    {
        return CentreAvailability::create(array_merge([
            'supply_node_id' => $n->getKey(),
            'next_available_on' => Carbon::today()->addDays(6)->toDateString(),
            'band' => 'good', 'source' => 'manual',
            'confirmed_at' => Carbon::now(), 'expires_at' => Carbon::now()->addDays(7),
            'checked_by' => 'Chloe', 'portal_state' => 'ok', 'last_seen_at' => Carbon::now(),
        ], $attrs));
    }

    public function test_is_live_only_when_a_snapshot_exists_for_that_market(): void
    {
        $za = Market::fromCode('za');
        $this->assertFalse(MarketBoard::isLive($za));
        $d = $this->dest('Italy');
        $n = $this->node($d);
        $this->assertFalse(MarketBoard::isLive($za), 'a node without a snapshot does not open the board');
        $this->snap($n);
        $this->assertTrue(MarketBoard::isLive($za));
        $this->assertFalse(MarketBoard::isLive(Market::fromCode('ae')));
    }

    public function test_state_mapping_covers_all_eight_states(): void
    {
        $d = $this->dest('Germany');
        $cal = $this->node($d);
        $wait = $this->node($d, ['booking_mode' => 'waitlist']);
        $alloc = $this->node($d, ['booking_mode' => 'allocation']);

        $this->assertSame('check', MarketBoard::stateFor($cal, null));
        $this->assertSame('available', MarketBoard::stateFor($cal, $this->snap($cal)));
        $this->assertSame('filling', MarketBoard::stateFor($cal, $this->snap($this->node($d), ['band' => 'filling'])));
        $this->assertSame('limited', MarketBoard::stateFor($cal, $this->snap($this->node($d), ['band' => 'limited'])));
        $this->assertSame('none', MarketBoard::stateFor($cal, $this->snap($this->node($d), ['next_available_on' => null, 'band' => null])));
        $this->assertSame('waitlist', MarketBoard::stateFor($wait, $this->snap($wait, ['next_available_on' => null, 'band' => null])));
        $this->assertSame('allocation', MarketBoard::stateFor($alloc, $this->snap($alloc, ['next_available_on' => null, 'band' => null])));
        $this->assertSame('portal_down', MarketBoard::stateFor($cal, $this->snap($this->node($d), ['next_available_on' => null, 'band' => null, 'portal_state' => 'unavailable'])));
        $this->assertSame('check', MarketBoard::stateFor($cal, $this->snap($this->node($d), ['next_available_on' => null, 'band' => null, 'portal_state' => 'unknown'])));
        $this->assertSame('check', MarketBoard::stateFor($cal, $this->snap($this->node($d), ['expires_at' => Carbon::now()->subMinute()])), 'stale > 7 days');
    }

    public function test_state_check_when_date_is_in_the_past(): void
    {
        $d = $this->dest('Spain');
        $n = $this->node($d);
        $a = $this->snap($n, ['next_available_on' => Carbon::today()->subDay()->toDateString()]);
        $this->assertSame('check', MarketBoard::stateFor($n, $a));
    }

    public function test_rows_carry_timestamp_checker_last_seen_and_link(): void
    {
        $za = Market::fromCode('za');
        $it = $this->dest('Italy');
        $fr = $this->dest('France');
        $vfs = $this->node($it, ['name' => 'VFS Johannesburg']);
        $cap = $this->node($fr, ['name' => 'Capago Sandton', 'operator' => 'capago', 'booking_url' => 'https://fr-za.capago.eu']);
        $this->snap($vfs);
        $this->snap($cap, ['next_available_on' => null, 'band' => null, 'last_seen_at' => Carbon::parse('2026-10-03 09:00'), 'confirmed_at' => Carbon::parse('2026-10-06 12:05:00')]);

        $rows = MarketBoard::rows($za);
        $this->assertCount(2, $rows);
        $this->assertSame(['France', 'Italy'], array_column($rows, 'destination'), 'ordered by destination');

        $france = $rows[0];
        $this->assertSame('Capago', $france['operator']);
        $this->assertSame('none', $france['state']);
        $this->assertSame('No dates seen', $france['state_label']);
        $this->assertSame('03 Oct', $france['last_seen']);
        $this->assertSame('Chloe', $france['checked_by']);
        $this->assertSame('6 Oct 2026, 14:05', $france['last_checked'], 'confirmed_at shown in Africa/Johannesburg (UTC+2)');
        $this->assertSame('https://fr-za.capago.eu', $france['booking_url']);

        $italy = $rows[1];
        $this->assertSame('available', $italy['state']);
        $this->assertSame(Carbon::today()->addDays(6)->format('j M Y'), $italy['earliest']);
        $this->assertSame('Online calendar', $italy['booking_mode']);

        $only = MarketBoard::rows($za, $it);
        $this->assertCount(1, $only);
        $this->assertSame('Italy', $only[0]['destination']);
    }

    public function test_copy_for_only_present_operators_and_no_banned_words(): void
    {
        $za = Market::fromCode('za');
        $fr = $this->dest('France');
        $this->node($fr, ['operator' => 'capago']);
        $rows = MarketBoard::rows($za);

        $copy = MarketBoard::copyFor($za, $rows);
        $this->assertSame(['_all', 'capago'], array_keys($copy));
        $this->assertStringContainsString('Checked by a person, not a bot', $copy['_all']);
        $this->assertStringContainsString('EUR 32', $copy['capago']);

        foreach (config('ukv.board_copy') as $market => $lines) {
            foreach ((array) $lines as $k => $line) {
                $this->assertStringNotContainsString("\u{2014}", $line, "$market.$k has an em-dash");
                foreach (['guaranteed', 'fast-track', 'priority appointment', 'early appointment'] as $banned) {
                    $this->assertStringNotContainsStringIgnoringCase($banned, $line, "$market.$k contains '$banned'");
                }
            }
        }
    }

    public function test_unknown_operator_renders_pending_label_and_null_link_stays_null(): void
    {
        $za = Market::fromCode('za');
        $d = $this->dest('Greece');
        $this->node($d, ['operator' => null, 'booking_url' => null]);
        $row = MarketBoard::rows($za)[0];
        $this->assertSame('Operator pending verification', $row['operator']);
        $this->assertNull($row['booking_url']);
        $this->assertSame('check', $row['state']);
        $this->assertNull($row['last_checked']);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketBoardTest`
Expected: FAIL with "Class App\Support\MarketBoard not found".

- [ ] **Step 3: Create `app/Support/MarketBoard.php`**

```php
<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\CentreAvailability;
use App\Models\Destination;
use App\Models\SupplyNode;

/**
 * Per-market availability board (SP4 spec 4.4). Reads ONLY timestamped CentreAvailability snapshots
 * for supply nodes in the given market. One row per (destination, centre). Never touches the UK
 * simulated pool; never invents a date. Consumed by the market hub (SP4) and SP3 destination pages.
 */
final class MarketBoard
{
    public const LABELS = [
        'check' => "We'll check for you",
        'portal_down' => 'Portal unavailable',
        'allocation' => 'Allocation queue',
        'waitlist' => 'Waitlist',
        'none' => 'No dates seen',
        'available' => 'Available',
        'filling' => 'Filling',
        'limited' => 'Limited',
    ];

    /** Stage gate: a market board is live only when at least one snapshot row exists for it (spec 4.6). */
    public static function isLive(Market $market): bool
    {
        return CentreAvailability::query()
            ->whereHas('supplyNode', fn ($q) => $q->where('market', $market->code))
            ->exists();
    }

    /**
     * @return array<int, array{destination:string, slug:string, operator:string, operator_code:?string, centre:string,
     *   booking_mode:string, state:string, state_label:string, earliest:?string, last_checked:?string,
     *   checked_by:?string, last_seen:?string, booking_url:?string}>
     */
    public static function rows(Market $market, ?Destination $only = null): array
    {
        $nodes = SupplyNode::query()
            ->where('market', $market->code)
            ->where('type', 'centre')
            ->with(['availability', 'destinations' => fn ($q) => $q->where('visa_type', 'Schengen')])
            ->orderBy('name')
            ->get();

        $rows = [];
        foreach ($nodes as $node) {
            foreach ($node->destinations as $destination) {
                if ($only !== null && $only->getKey() !== $destination->getKey()) {
                    continue;
                }
                $rows[] = self::row($market, $destination, $node);
            }
        }

        usort($rows, fn (array $a, array $b) => [$a['destination'], $a['centre']] <=> [$b['destination'], $b['centre']]);

        return $rows;
    }

    public static function stateFor(SupplyNode $node, ?CentreAvailability $a): string
    {
        if ($a === null || $a->isStale() || $a->portal_state === 'unknown') {
            return 'check';
        }
        if ($a->portal_state === 'unavailable') {
            return 'portal_down';
        }
        if ($a->next_available_on === null) {
            return match ($node->booking_mode) {
                'allocation' => 'allocation',
                'waitlist' => 'waitlist',
                default => 'none',
            };
        }

        return match ($a->band) {
            'good' => 'available',
            'filling' => 'filling',
            default => 'limited',
        };
    }

    /**
     * Honest copy: the global line plus one line per operator code present in $rows, in row order.
     * Operators without a node render nothing (AE France and Greece everywhere stay unpublished until
     * verified). Lines live in config('ukv.board_copy').
     *
     * @param  array<int, array<string,mixed>>  $rows
     * @return array<string,string>
     */
    public static function copyFor(Market $market, array $rows): array
    {
        $all = (array) config('ukv.board_copy', []);
        $out = ['_all' => (string) ($all['_all'] ?? '')];
        $perMarket = (array) ($all[$market->code] ?? []);
        foreach ($rows as $r) {
            $code = $r['operator_code'] ?? null;
            if ($code !== null && isset($perMarket[$code]) && ! isset($out[$code])) {
                $out[$code] = (string) $perMarket[$code];
            }
        }

        return $out;
    }

    private static function row(Market $market, Destination $destination, SupplyNode $node): array
    {
        $a = $node->availability;
        $state = self::stateFor($node, $a);
        $dated = in_array($state, ['available', 'filling', 'limited'], true);
        $checked = $a !== null && $state !== 'check';

        return [
            'destination' => (string) $destination->name,
            'slug' => (string) $destination->slug,
            'operator' => SupplyNode::OPERATORS[$node->operator] ?? 'Operator pending verification',
            'operator_code' => $node->operator,
            'centre' => (string) $node->name,
            'booking_mode' => SupplyNode::BOOKING_MODES[$node->booking_mode] ?? SupplyNode::BOOKING_MODES['calendar'],
            'state' => $state,
            'state_label' => self::LABELS[$state],
            'earliest' => $dated ? $a->next_available_on->format('j M Y') : null,
            'last_checked' => $checked ? $a->confirmed_at->copy()->setTimezone($market->timezone())->format('j M Y, H:i') : null,
            'checked_by' => $checked ? $a->checked_by : null,
            'last_seen' => ($state === 'none' && $a?->last_seen_at) ? $a->last_seen_at->copy()->setTimezone($market->timezone())->format('d M') : null,
            'booking_url' => $node->booking_url ?: null,
        ];
    }
}
```

- [ ] **Step 4: Add `board_copy` to `config/ukv.php`**

Directly after the closing `],` of the `'markets' => [ ... ]` block add:

```php
    // ── Market board honesty copy (SP4 spec 4.5) ─────────────────────────────
    // Rendered under each market board: '_all' always; an operator line only when a supply node with
    // that operator exists in the market (so unverified operators stay unpublished). Text from the
    // 2026-10-06 market deep dives, "Appointment copy per operator". No em-dashes; never the words
    // "guaranteed", "fast-track", "priority appointment", "early appointment".
    'board_copy' => [
        '_all' => 'Checked by a person, not a bot. Dates move within minutes; a date shown here can be gone before you log in. We never hold or sell appointments. Appointments are free on the official site and are booked in your name.',
        'za' => [
            'vfs' => 'VFS Global (Italy, Netherlands, Portugal, Switzerland, Austria): online calendar released in batches by the consulate, not VFS. The Netherlands currently warns that appointments are limited. We check on our own account and tell you the moment a date appears; you book in your name.',
            'capago' => 'Capago (France): the France-Visas form comes first, then the Capago calendar. A slot is only confirmed once the EUR 32 service fee is prepaid and it is non-refundable, so we book when your file is ready, not before.',
            'tlscontact' => 'TLScontact (Germany, Belgium): online calendar; the embassy advises booking about four weeks ahead. Prime Time is a comfort slot, not a faster decision, and TLScontact says so in writing. Belgium offers a R100 postal route if your fingerprints were taken in the last 59 months.',
            'bls' => 'BLS International (Spain): the login is tied to one applicant and BLS says appointments must be booked by the applicant directly. We prepare everything and stay on WhatsApp while you book.',
            'gvcw' => 'Global Visa Center World (Greece): online booking; walk-ins only at the centre manager\'s discretion. Bring bank statements with an original ink stamp.',
        ],
        'ae' => [
            'vfs' => 'VFS Global: slots are released by the consulate, not VFS, and are free. Where a waitlist exists (Germany, Greece, Switzerland) we register you and watch the release pattern; the booking is made in your name on your own VFS account. Switzerland requires the online form at the moment of booking, so we prepare it in advance.',
            'bls' => 'BLS International (Spain): BLS states appointments must be booked directly by the applicant without intermediaries. We cannot book for you; we prepare everything, tell you when slots typically drop, and sit with you on WhatsApp while you book. Premium Lounge and Prime Time change your waiting room, not your decision.',
            'tlscontact' => 'TLScontact (France, Belgium): France-Visas form first, then the TLScontact calendar. We complete the France-Visas file and hand you the reference.',
        ],
        'us' => [
            'tlscontact' => 'TLScontact (France): any of the ten US centres regardless of where you live. There is no expedite procedure. We watch release patterns and tell you the moment a date appears; you book under your own TLScontact account.',
            'bls' => 'BLS International (Spain, Germany): your centre is fixed by your state. BLS\'s own Prime Time and Premium Lounge are the only legitimate paid options and they change comfort, not the decision. BLS calls buying appointments through an intermediary a fraudulent practice, and so do we.',
            'prenotami' => 'Prenot@mi (Italy): appointments are personal and free. Slots drop in batches; we coach the daily check and verify your booking 3 to 10 days before.',
            'vfs' => 'VFS Global (Netherlands, Switzerland, Portugal, Austria): fixed jurisdiction for Austria and Switzerland, any centre for the Netherlands. The Netherlands asks you not to make an appointment through an intermediary; you book, we prepare.',
            'consulate' => 'Belgium: no visa centre. Mail-in if your biometrics were taken in the last 59 months; otherwise Washington, New York, Los Angeles or Atlanta.',
        ],
        'ca' => [
            'vfs' => 'VFS Global (France, Netherlands, Austria, Switzerland): France is any Canadian centre with decisions in Montreal and no walk-ins since early 2026. Edmonton and Vancouver centres exist; Ontario Swiss applications go to VFS Montreal. Prime Time is a paid comfort option; we tell you when it is worth it.',
            'consulate' => 'Consulate direct (Germany Toronto, Belgium Montreal, Portugal): German slots open about a month out in batches, and the consulate says it does not work with brokers or agencies, and neither do we. Belgium Montreal serves all of Canada and publishes a wait of more or less one month. Portugal uses the e-visa portal first.',
            'prenotami' => 'Prenot@mi (Italy): free, personal, released daily around 6pm Toronto time on an eight-week rolling calendar. We coach the check; you book.',
            'email_queue' => 'Consulate email queue (Spain Toronto and Montreal): booking is by email in a strict format; one mistake and the request is auto-rejected. We draft it with you. Western Canada can use the Swiss consulate in Vancouver.',
        ],
    ],
```

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter='MarketBoardTest|MarketBoardGuardTest'`
Expected: PASS (6 + 5 tests). If `last_checked` differs by an hour, confirm `config('app.timezone')` is `UTC` in `phpunit.xml`/`config/app.php`; the test fixes `confirmed_at` at `12:05 UTC` and expects `14:05` Johannesburg.

- [ ] **Step 6: Commit**

```bash
git add app/Support/MarketBoard.php config/ukv.php tests/Feature/MarketBoardTest.php
git commit -m "feat(board): MarketBoard renderer (8 states, rows, stage gate, per-operator honesty copy)"
```

---

### Task 5: Board partial and hub integration

**Files:**
- Create: `resources/views/partials/market-board.blade.php`
- Modify: `app/Http/Controllers/Market/MarketHubController.php`
- Modify: `resources/views/market/hub.blade.php`
- Test: `tests/Feature/MarketHubBoardTest.php`

**Interfaces:**
- Consumes: `MarketBoard::isLive/rows/copyFor`, `Market`, `partials.market-trust-strip`.
- Produces: `@include('partials.market-board', ['market' => $market, 'rows' => $rows, 'copy' => $copy, 'wa' => $wa])`; hub view variables `boardLive`, `rows`, `copy`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/MarketHubBoardTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CentreAvailability;
use App\Models\Destination;
use App\Models\SupplyNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class MarketHubBoardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['ukv.markets.za.enabled' => true, 'ukv.intl_base_url' => 'https://beyondpassports.com', 'ukv.markets.za.whatsapp' => '27680000000']);
    }

    private function seedBoard(): void
    {
        $it = Destination::create(['name' => 'Italy', 'slug' => 'italy', 'visa_type' => 'Schengen', 'govt_fee_gbp' => 0, 'tier_standard_gbp' => 39, 'tier_express_gbp' => 59, 'tier_premium_gbp' => 89, 'passport_validity_months' => 6]);
        $fr = Destination::create(['name' => 'France', 'slug' => 'france', 'visa_type' => 'Schengen', 'govt_fee_gbp' => 0, 'tier_standard_gbp' => 39, 'tier_express_gbp' => 59, 'tier_premium_gbp' => 89, 'passport_validity_months' => 6]);
        $vfs = SupplyNode::create(['node_key' => 'za-vfs-jhb', 'type' => 'centre', 'market' => 'za', 'name' => 'VFS Johannesburg', 'operator' => 'vfs', 'booking_url' => 'https://visa.vfsglobal.com/zaf/en/ita']);
        $cap = SupplyNode::create(['node_key' => 'za-capago', 'type' => 'centre', 'market' => 'za', 'name' => 'Capago Sandton', 'operator' => 'capago', 'booking_url' => 'https://fr-za.capago.eu']);
        $it->supplyNodes()->attach($vfs->getKey());
        $fr->supplyNodes()->attach($cap->getKey());
        CentreAvailability::create(['supply_node_id' => $vfs->getKey(), 'next_available_on' => Carbon::today()->addDays(9)->toDateString(), 'band' => 'limited', 'confirmed_at' => Carbon::now(), 'expires_at' => Carbon::now()->addDays(7), 'checked_by' => 'Chloe', 'portal_state' => 'ok', 'last_seen_at' => Carbon::now()]);
        CentreAvailability::create(['supply_node_id' => $cap->getKey(), 'next_available_on' => null, 'band' => null, 'confirmed_at' => Carbon::now(), 'expires_at' => Carbon::now()->addDays(7), 'checked_by' => 'Chloe', 'portal_state' => 'ok', 'last_seen_at' => Carbon::parse('2026-09-28 08:00')]);
    }

    public function test_hub_shows_check_block_when_market_has_no_snapshot(): void
    {
        $r = $this->get('/za/schengen-visa');
        $r->assertOk();
        $r->assertSee('we will check for you');
        $r->assertDontSee('<table class="mb-table"', false);
    }

    public function test_hub_renders_board_when_live(): void
    {
        $this->seedBoard();
        $r = $this->get('/za/schengen-visa');
        $r->assertOk();
        $r->assertSee('<table class="mb-table"', false);
        foreach (['Destination', 'Operator', 'Centre', 'Booking mode', 'Earliest date seen', 'Last checked', 'Official free booking link'] as $h) {
            $r->assertSee($h);
        }
        $r->assertSee('VFS Johannesburg');
        $r->assertSee('Limited');
        $r->assertSee(Carbon::today()->addDays(9)->format('j M Y'));
        $r->assertSee('by Chloe');
        $r->assertSee('No dates seen');
        $r->assertSee('last seen 28 Sep');
        $r->assertSee('href="https://fr-za.capago.eu"', false);
        $r->assertSee('Book free on the official site');
        $r->assertSee('Checked by a person, not a bot');
        $r->assertSee('EUR 32');          // capago copy present
        $r->assertDontSee('BLS International (Spain)'); // no BLS node yet, so no BLS copy
        $r->assertSee('check for you');   // the not-yet-on-board line keeps the SP1 phrase
    }

    public function test_board_ignores_the_simulated_pool_even_when_dynamic_is_on(): void
    {
        config(['ukv.slots.dynamic' => true]);
        $this->seedBoard();
        $r = $this->get('/za/schengen-visa');
        $r->assertOk();
        $r->assertDontSee('slots open');
        $r->assertDontSee('data-slot-count', false);
        $r->assertDontSee('Near capacity');
    }

    public function test_board_copy_is_clean(): void
    {
        $this->seedBoard();
        $html = $this->get('/za/schengen-visa')->getContent();
        $this->assertStringNotContainsString("\u{2014}", $html);
        foreach (['guaranteed', 'fast-track', 'priority appointment', 'early appointment'] as $banned) {
            $this->assertStringNotContainsStringIgnoringCase($banned, $html);
        }
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketHubBoardTest`
Expected: FAIL (`test_hub_renders_board_when_live`: no table).

- [ ] **Step 3: Create the partial**

Create `resources/views/partials/market-board.blade.php`:

```blade
{{-- Per-market availability board (SP4 spec 4.5). Rows come from App\Support\MarketBoard::rows():
     real timestamped snapshots only. Never a pool count, never a placeholder date. --}}
@php
  $m = $market ?? \App\Support\Market::current();
  $rows = $rows ?? [];
  $copy = $copy ?? [];
  $onBoard = array_unique(array_column($rows, 'destination'));
@endphp
<section class="mb" aria-label="Appointment availability board">
  <h2 class="mb-h">Appointment availability from {{ $m->label() }}</h2>
  <p class="mb-lede">{{ $copy['_all'] ?? '' }}</p>
  <div class="mb-wrap">
    <table class="mb-table">
      <thead>
        <tr>
          <th>Destination</th><th>Operator</th><th>Centre</th><th>Booking mode</th>
          <th>Earliest date seen</th><th>Last checked</th><th>Official free booking link</th>
        </tr>
      </thead>
      <tbody>
      @foreach ($rows as $r)
        <tr>
          <td><b>{{ $r['destination'] }}</b></td>
          <td>{{ $r['operator'] }}</td>
          <td>{{ $r['centre'] }}</td>
          <td>{{ $r['booking_mode'] }}</td>
          <td>
            <span class="mb-st mb-{{ $r['state'] }}">{{ $r['state_label'] }}</span>
            @if ($r['earliest'])<div class="mb-dt">{{ $r['earliest'] }}</div>@endif
            @if ($r['last_seen'])<div class="mb-ls">last seen {{ $r['last_seen'] }}</div>@endif
          </td>
          <td>
            @if ($r['last_checked'])
              {{ $r['last_checked'] }}@if ($r['checked_by']) by {{ $r['checked_by'] }}@endif
            @else
              <span class="mb-muted">Not yet checked</span>
            @endif
          </td>
          <td>
            @if ($r['booking_url'])
              <a href="{{ $r['booking_url'] }}" target="_blank" rel="noopener nofollow">Book free on the official site</a>
            @else
              <span class="mb-muted">{{ $r['operator'] }}</span>
            @endif
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @if (count($copy) > 1)
    <div class="mb-copy">
      <h3>What to expect, by operator</h3>
      <ul>
        @foreach ($copy as $k => $line)@if ($k !== '_all')<li>{{ $line }}</li>@endif @endforeach
      </ul>
    </div>
  @endif
  <p class="mb-foot">Destinations not yet on this board: we'll check for you. <a href="{{ $wa }}" target="_blank" rel="noopener">Ask on WhatsApp</a> and a named consultant replies with what we see and the time we saw it.</p>
</section>
@once
<style>
.mb{max-width:1100px;margin:0 auto;padding:0 24px 32px;font-family:"Outfit",system-ui,sans-serif;color:#16222E}
.mb-h{font-size:22px;margin:0 0 6px}.mb-lede{margin:0 0 14px;color:#2a3a47;max-width:72ch;line-height:1.5}
.mb-wrap{overflow-x:auto;border:1px solid #dde3ec;border-radius:14px;background:#fff}
.mb-table{width:100%;border-collapse:collapse;font-size:14px;min-width:860px}
.mb-table th,.mb-table td{padding:10px 12px;border-bottom:1px solid #eef1f5;text-align:left;vertical-align:top}
.mb-table th{font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#5d6b76;background:#f6f9fb}
.mb-st{display:inline-block;border-radius:100px;padding:2px 10px;font-weight:700;font-size:12px;background:#f6f9fb;color:#2a3a47}
.mb-available{background:rgba(46,154,140,.12);color:#1F6E63}.mb-filling,.mb-limited{background:rgba(245,158,11,.12);color:#B45309}
.mb-waitlist,.mb-allocation{background:rgba(11,21,40,.06);color:#0B1528}.mb-portal_down{background:rgba(220,38,38,.1);color:#b91c1c}
.mb-dt{font-weight:700;margin-top:4px}.mb-ls,.mb-muted{color:#5d6b76;font-size:12px}
.mb-copy{margin-top:18px}.mb-copy h3{font-size:16px;margin:0 0 6px}.mb-copy ul{margin:0;padding-left:18px;line-height:1.55;font-size:14px}
.mb-foot{margin-top:14px;font-size:14px;color:#2a3a47}.mb-foot a{color:#155E7A;font-weight:600}
@media (max-width:560px){.mb{padding:0 16px 24px}}
</style>
@endonce
```

- [ ] **Step 4: Update the controller**

Replace the `__invoke` method in `app/Http/Controllers/Market/MarketHubController.php` with:

```php
    public function __invoke(): Response
    {
        $market = Market::current();
        $live = MarketBoard::isLive($market);
        $rows = $live ? MarketBoard::rows($market) : [];

        return response()->view('market.hub', [
            'market' => $market,
            'destinations' => self::SCHENGEN,
            'boardLive' => $live,
            'rows' => $rows,
            'copy' => $live ? MarketBoard::copyFor($market, $rows) : [],
        ]);
    }
```

Add `use App\Support\MarketBoard;` and update the class docblock to: `Hub for /{market}/schengen-visa. Renders the snapshot-only MarketBoard when the market has at least one snapshot (SP4 spec 4.6), else the honest "we will check for you" block. Never calls the UK simulated pool.`

- [ ] **Step 5: Update the hub view**

In `resources/views/market/hub.blade.php` wrap the existing `<div class="hb-check">...</div>` block:

```blade
  @if ($boardLive)
    @include('partials.market-board', ['market' => $market, 'rows' => $rows, 'copy' => $copy, 'wa' => $wa])
  @else
  <div class="hb-check">
    ... (existing block unchanged) ...
  </div>
  @endif
```

- [ ] **Step 6: Run to verify it passes**

Run: `php artisan test --filter='MarketHubBoardTest|MarketHubPageTest|MarketBoardGuardTest|MarketSeoTest'`
Expected: PASS. The SP1 `test_hub_renders_check_for_you_block_and_29_destinations` still passes (empty DB = not live).

- [ ] **Step 7: Commit**

```bash
git add resources/views/partials/market-board.blade.php app/Http/Controllers/Market/MarketHubController.php resources/views/market/hub.blade.php tests/Feature/MarketHubBoardTest.php
git commit -m "feat(board): market-board partial; hub renders snapshot board when live, check block otherwise"
```

---

### Task 6: UpdateAvailability: checker name, all markets, new tokens

**Files:**
- Modify: `app/Filament/Pages/UpdateAvailability.php`
- Modify: `resources/views/filament/pages/update-availability.blade.php` (helper text only)
- Test: `tests/Feature/UpdateAvailabilityMarketBoardTest.php`

**Interfaces:**
- Produces: form state keys `market`, `checker`, `input`; `prefillRows(string $market)` emits `none` / `down` / mode tokens; `applyBulk()` passes `checkedBy` + `portalState` and updates `booking_mode`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/UpdateAvailabilityMarketBoardTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\UpdateAvailability;
use App\Models\CentreAvailability;
use App\Models\Destination;
use App\Models\SupplyNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class UpdateAvailabilityMarketBoardTest extends TestCase
{
    use RefreshDatabase;

    private function seed(): SupplyNode
    {
        $d = Destination::create(['name' => 'Germany', 'slug' => 'germany', 'visa_type' => 'Schengen', 'govt_fee_gbp' => 0, 'tier_standard_gbp' => 39, 'tier_express_gbp' => 59, 'tier_premium_gbp' => 89, 'passport_validity_months' => 6]);
        $n = SupplyNode::create(['node_key' => 'za-tls', 'type' => 'centre', 'market' => 'za', 'name' => 'TLS Centurion', 'we_book_here' => true, 'operator' => 'tlscontact']);
        $d->supplyNodes()->attach($n->getKey());

        return $n;
    }

    public function test_market_options_cover_uk_and_every_configured_market(): void
    {
        $this->assertSame(['uk', 'za', 'ae', 'us', 'ca'], array_keys(UpdateAvailability::marketOptions()));
        $this->assertSame('South Africa', UpdateAvailability::marketOptions()['za']);
    }

    public function test_prefill_emits_none_down_and_mode_tokens(): void
    {
        $n = $this->seed();
        $page = new UpdateAvailability();

        CentreAvailability::create(['supply_node_id' => $n->getKey(), 'confirmed_at' => Carbon::now(), 'expires_at' => Carbon::now()->addDays(7), 'portal_state' => 'ok']);
        $this->assertSame('germany: none', $page->prefillRows('za')->first()['line']);

        $n->availability->update(['portal_state' => 'unavailable']);
        $this->assertSame('germany: down', $page->prefillRows('za')->first()['line']);

        $n->update(['booking_mode' => 'waitlist']);
        $n->availability->update(['portal_state' => 'ok', 'next_available_on' => '2026-12-01', 'band' => 'filling']);
        $this->assertSame('germany: 2026-12-01 filling waitlist', $page->prefillRows('za')->first()['line']);
    }

    public function test_apply_writes_checker_portal_state_and_booking_mode(): void
    {
        $n = $this->seed();
        $page = new UpdateAvailability();
        $page->applyRows($page->parse("germany: none allocation", 'za'), 'Chloe');

        $a = $n->fresh()->availability;
        $this->assertSame('Chloe', $a->checked_by);
        $this->assertSame('ok', $a->portal_state);
        $this->assertNull($a->next_available_on);
        $this->assertSame('allocation', $n->fresh()->booking_mode);

        $page->applyRows($page->parse("germany: down", 'za'), 'Sam');
        $this->assertSame('unavailable', $n->fresh()->availability->portal_state);

        $page->applyRows($page->parse("germany: ask", 'za'), 'Sam');
        $this->assertSame('unknown', $n->fresh()->availability->portal_state);

        $page->applyRows($page->parse("germany: 2026-12-01 good", 'za'), 'Sam');
        $this->assertSame('good', $n->fresh()->availability->band);
        $this->assertSame('allocation', $n->fresh()->booking_mode, 'no mode token leaves the mode alone');
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=UpdateAvailabilityMarketBoardTest`
Expected: FAIL (`marketOptions`, `parse`, `applyRows` undefined).

- [ ] **Step 3: Edit the page class**

In `app/Filament/Pages/UpdateAvailability.php`:

Add `use App\Support\Market;` and `use Filament\Forms\Components\TextInput;`.

Add after `canWrite()`:

```php
    /** @return array<string,string> uk + every configured market, code => label (SP4 spec 4.7). */
    public static function marketOptions(): array
    {
        $out = ['uk' => 'UK'];
        foreach (Market::codes() as $code) {
            $out[$code] = Market::fromCode($code)->label();
        }

        return $out;
    }

    /** Thin wrapper so tests and the Livewire actions share one parse path. */
    public function parse(string $input, string $market): array
    {
        return app(AvailabilityService::class)->parseBulk($input, $market);
    }

    /**
     * Write every valid row through the service with the checker's name. Reset -> portal unknown;
     * none -> portal ok, no date; down -> portal unavailable; dated -> date + band. A mode token
     * updates the node's booking_mode first. Returns the number of rows written.
     */
    public function applyRows(array $result, string $checker): int
    {
        $service = app(AvailabilityService::class);
        $applied = 0;

        foreach ($result['rows'] as $row) {
            if ($row['error'] !== null || $row['node_id'] === null) {
                continue;
            }
            if (! empty($row['mode'])) {
                SupplyNode::query()->whereKey((int) $row['node_id'])->update(['booking_mode' => $row['mode']]);
            }

            match ($row['state']) {
                'reset' => $service->setSnapshot((int) $row['node_id'], null, null, 'manual', null, $checker, 'unknown'),
                'none' => $service->setSnapshot((int) $row['node_id'], null, null, 'manual', null, $checker, 'ok'),
                'down' => $service->setSnapshot((int) $row['node_id'], null, null, 'manual', null, $checker, 'unavailable'),
                default => $service->setSnapshot(
                    (int) $row['node_id'],
                    Carbon::createFromFormat('Y-m-d', (string) $row['next_available_on'])->startOfDay(),
                    $row['band'],
                    'manual',
                    null,
                    $checker,
                    'ok',
                ),
            };

            $applied++;
        }

        return $applied;
    }
```

Replace `mount()` fill with `$this->form->fill(['input' => '', 'market' => 'uk', 'checker' => auth()->user()?->name ?? '']);`.

In `form()` replace the market Select's `->options(['uk' => 'UK', 'za' => 'South Africa'])` with `->options(self::marketOptions())`, and insert before `Textarea::make('input')`:

```php
                TextInput::make('checker')
                    ->label('Checked by')
                    ->helperText('Your first name. Market boards print "Last checked ... by {name}".')
                    ->required()
                    ->maxLength(60),
```

Update the Textarea helper text to: `'One line per centre: "spain: 2026-06-12 good", "germany: 2026-11-02 limited waitlist", "france: none", "italy: down", or "italy: ask" to reset. Bands: good, filling, limited. Optional last token sets booking mode: calendar, waitlist, allocation, embassy, email.'`

In `prefillRows()`, replace the `else { ... }` line-building branch so the line is computed as:

```php
                    if ($availability->next_available_on !== null && $availability->band !== null) {
                        $line = sprintf('%s: %s %s', $slug, $availability->next_available_on->format('Y-m-d'), $availability->band);
                    } elseif ($availability->portal_state === 'unavailable') {
                        $line = "{$slug}: down";
                    } elseif ($availability->portal_state === 'ok') {
                        $line = "{$slug}: none";
                    } else {
                        $line = "{$slug}: ask";
                    }
                    if (($node->booking_mode ?? 'calendar') !== 'calendar') {
                        $line .= ' '.$node->booking_mode;
                    }
```

In `applyBulk()`, replace the block from `$service = app(AvailabilityService::class);` through the end of the `foreach` with:

```php
        $checker = trim((string) ($state['checker'] ?? '')) ?: (auth()->user()?->name ?? 'ops');
        $applied = $this->applyRows($result, $checker);
```

and in the final `fill` keep the checker: `$this->form->fill(['input' => '', 'market' => $market, 'checker' => $checker]);`.

- [ ] **Step 4: Run to verify it passes**

Run: `php artisan test --filter='UpdateAvailabilityMarketBoardTest|UpdateAvailabilityPageTest|AdminPanelSmokeTest'`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Filament/Pages/UpdateAvailability.php resources/views/filament/pages/update-availability.blade.php tests/Feature/UpdateAvailabilityMarketBoardTest.php
git commit -m "feat(ops): Update availability gains checker name, all markets, none/down/mode tokens"
```

---

### Task 7: Filament fields for board data on supply nodes and snapshots

**Files:**
- Modify: `app/Filament/Resources/SupplyNodeResource.php`
- Modify: `app/Filament/Resources/CentreAvailabilityResource.php`

- [ ] **Step 1: Locate the insertion points**

Run: `grep -n "TextInput::make('name')" app/Filament/Resources/SupplyNodeResource.php; grep -n "TextColumn::make('band')\|columns(\[" app/Filament/Resources/CentreAvailabilityResource.php`
Expected: one line number each.

- [ ] **Step 2: Add the supply node fields**

Directly after the `TextInput::make('name')...` component (after its closing `,`) add:

```php
                Select::make('market')
                    ->label('Market')
                    ->options(\App\Filament\Pages\UpdateAvailability::marketOptions())
                    ->default('uk')
                    ->required(),
                Select::make('operator')
                    ->label('Operator')
                    ->options(\App\Models\SupplyNode::OPERATORS)
                    ->nullable()
                    ->helperText('Leave empty until the operator is verified on the official site; the board then prints "Operator pending verification" and no operator copy.'),
                Select::make('booking_mode')
                    ->label('Booking mode')
                    ->options(\App\Models\SupplyNode::BOOKING_MODES)
                    ->default('calendar')
                    ->required(),
                TextInput::make('booking_url')
                    ->label('Official free booking link')
                    ->url()
                    ->maxLength(255)
                    ->helperText('The operator or consulate page where the applicant books for free. Never a reseller.'),
```

Ensure `use Filament\Forms\Components\Select;` and `use Filament\Forms\Components\TextInput;` exist at the top.

- [ ] **Step 3: Add the snapshot columns**

In `CentreAvailabilityResource::table()` columns array, after the `band` column add:

```php
                TextColumn::make('checked_by')->label('Checked by')->placeholder('not set'),
                TextColumn::make('portal_state')->label('Portal')->badge(),
                TextColumn::make('last_seen_at')->label('Last seen')->dateTime('j M Y')->placeholder('not set'),
```

- [ ] **Step 4: Verify**

Run: `php artisan test --filter='AdminPanelSmokeTest' && php -l app/Filament/Resources/SupplyNodeResource.php && php -l app/Filament/Resources/CentreAvailabilityResource.php`
Expected: PASS and "No syntax errors".

- [ ] **Step 5: Commit**

```bash
git add app/Filament/Resources/SupplyNodeResource.php app/Filament/Resources/CentreAvailabilityResource.php
git commit -m "feat(admin): market/operator/booking-mode/link on supply nodes; checker + portal columns on snapshots"
```

---

### Task 8: Full suite, playbook, runbook, spec status

**Files:**
- Modify: `docs/appointment-availability-playbook.md` (new section 5b), `docs/GO-LIVE-RUNBOOK.md` (board stage gate), spec status line.

- [ ] **Step 1: Run the whole suite**

Run: `php artisan test`
Expected: all PASS.

- [ ] **Step 2: Playbook section**

Insert after section 5 of `docs/appointment-availability-playbook.md`:

```markdown
## 5b. Market boards (.com): tokens and the stage gate

Market boards (`/za/schengen-visa` etc.) read ONLY snapshots for supply nodes with that `market`.
Each paste line now carries who checked (the "Checked by" box) and may carry a state token:

```
germany: 2026-11-02 limited waitlist   date + band (good|filling|limited) + optional booking mode
france: none                           checked, no dates seen (board: "No dates seen, last seen DD Mon")
italy: down                            portal unavailable / login blocked
italy: ask                             reset (board: "We'll check for you")
```

Stage gate: a market board appears on the site only after its first snapshot row exists. Before
pasting the first row for a market: (1) create the centre in Admin > Supply nodes with market,
operator, booking mode and the OFFICIAL booking link; (2) do ONE real check on that tenant (§0b)
and log the error codes in Portal Accounts; (3) paste the result with your name. Nothing else
flips the board on.
```

- [ ] **Step 3: Runbook**

Append to the "International markets (beyondpassports.com)" section of `docs/GO-LIVE-RUNBOOK.md`:

```markdown
6. Boards: a market's availability board renders only once Admin > Update availability has one
   snapshot row for a node in that market (SP4 spec 4.6). `UKV_SLOTS_DYNAMIC` has no effect on
   `.com`; the simulated pool throws if any market page tries to read it.
```

- [ ] **Step 4: Spec status**

In the spec change `**Status:** draft for owner review.` to `**Status:** implemented on branch (plan 2026-10-06-sp4-market-availability.md); awaiting owner acceptance against section 10.`

- [ ] **Step 5: Commit**

```bash
git add docs/appointment-availability-playbook.md docs/GO-LIVE-RUNBOOK.md docs/superpowers/specs/2026-10-06-sp4-market-availability-design.md
git commit -m "docs(board): playbook tokens + stage gate, runbook note, spec status"
```

---

## Self-review (done at writing time)

- Spec coverage: 4.1 Task 1; 4.2 Task 2; 4.3 Task 3; 4.4 Task 4; 4.5 Tasks 4 (copy config), 5 (partial); 4.6 Tasks 4 (`isLive`), 5 (hub branch); 4.7 Tasks 6, 7; 4.8 exposed by Task 4 (`rows($market, $only)`), consumed in SP3; 5 (error handling) Tasks 2, 4; 6, 7 no code beyond Task 5 markup; 8 Tasks 1, 4; 10 items 1-9 map to Tasks 1, 2, 2, 3, 4, 4, 4, 5, 6.
- Review Focus: 1 Task 4 `test_state_check_when_date_is_in_the_past`; 2 Tasks 2 and 4; 3 Task 2; 4 Task 1; 5 Task 4; 6 Task 3.
- Type consistency: `setSnapshot(int, ?Carbon, ?string, string, ?int, ?string, string)` used identically in Tasks 2 and 6; row keys from `MarketBoard::row()` match the partial and tests; `SupplyNode::BOOKING_MODES` keys are the paste tokens.
- Judgement calls left to the executor: Task 4 Step 5 timezone of the test clock; Task 7 exact component names in `SupplyNodeResource` (grep step provided).
