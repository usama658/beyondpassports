<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\SlotBoard;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Locked spec: docs/appointment-slots-dynamic.md v1.1.
 */
class SlotBoardTest extends TestCase
{
    use RefreshDatabase;

    private array $countries = [
        'France', 'Spain', 'Italy', 'Germany', 'Greece', 'Netherlands', 'Portugal', // popular
        'Austria', 'Belgium', 'Czechia', 'Poland', 'Sweden', 'Croatia', 'Malta',     // rest
    ];

    protected function tearDown(): void
    {
        SlotBoard::setCountriesForTesting(null);
        parent::tearDown();
    }

    public function test_allocation_sums_to_70_and_respects_floor(): void
    {
        $alloc = SlotBoard::allocationFor($this->countries, '2026-01-03');

        $this->assertSame(SlotBoard::TOTAL, array_sum($alloc), 'pool must total exactly 70');
        $this->assertCount(count($this->countries), $alloc);
        foreach ($alloc as $country => $n) {
            $this->assertGreaterThanOrEqual(SlotBoard::FLOOR, $n, "$country below floor");
        }
    }

    public function test_allocation_is_deterministic_within_a_week(): void
    {
        $a = SlotBoard::allocationFor($this->countries, '2026-01-03');
        $b = SlotBoard::allocationFor($this->countries, '2026-01-03');
        $this->assertSame($a, $b, 'same week must be stable');
    }

    public function test_allocation_differs_across_weeks(): void
    {
        $a = SlotBoard::allocationFor($this->countries, '2026-01-03');
        $b = SlotBoard::allocationFor($this->countries, '2026-01-10');
        $this->assertNotSame($a, $b, 'each week should reshuffle the split');
        // both still valid
        $this->assertSame(SlotBoard::TOTAL, array_sum($b));
    }

    public function test_popular_countries_get_more_on_average_over_many_weeks(): void
    {
        $france = 0;
        $malta = 0;
        for ($w = 0; $w < 30; $w++) {
            $week = CarbonImmutable::parse('2026-01-03')->addWeeks($w)->format('Y-m-d');
            $alloc = SlotBoard::allocationFor($this->countries, $week);
            $france += $alloc['France'];
            $malta += $alloc['Malta'];
        }
        $this->assertGreaterThan($malta, $france, 'popular countries weighted higher on average');
    }

    public function test_slot_week_rolls_over_on_saturday(): void
    {
        $sat = CarbonImmutable::parse('2026-01-03', 'Europe/London'); // a Saturday
        $this->assertTrue($sat->isSaturday());

        $this->assertSame('2026-01-03', SlotBoard::slotWeek($sat));
        $this->assertSame('2026-01-03', SlotBoard::slotWeek($sat->addDays(3)));  // Tue, same week
        $this->assertSame('2026-01-03', SlotBoard::slotWeek($sat->addDays(6)));  // Fri, same week
        $this->assertSame('2026-01-10', SlotBoard::slotWeek($sat->addDays(7)));  // next Sat, new week
        $this->assertTrue(CarbonImmutable::parse(SlotBoard::slotWeek($sat->addDays(2)))->isSaturday());
    }

    public function test_decrement_reduces_country_and_total_and_dedups(): void
    {
        SlotBoard::setCountriesForTesting($this->countries);

        $before = SlotBoard::remainingFor('France');
        $this->assertGreaterThan(0, $before);
        $beforeTotal = SlotBoard::total();

        SlotBoard::decrement('France', 'visitorAAA');
        $this->assertSame($before - 1, SlotBoard::remainingFor('France'), 'country -1');
        $this->assertSame($beforeTotal - 1, SlotBoard::total(), 'total -1');

        // Same visitor again → deduped, no further drop.
        SlotBoard::decrement('France', 'visitorAAA');
        $this->assertSame($before - 1, SlotBoard::remainingFor('France'), 'dedup: same visitor no double count');

        // Different visitor → another -1.
        SlotBoard::decrement('France', 'visitorBBB');
        $this->assertSame($before - 2, SlotBoard::remainingFor('France'));
    }

    public function test_never_below_zero(): void
    {
        SlotBoard::setCountriesForTesting($this->countries);

        $start = SlotBoard::remainingFor('Malta');
        for ($i = 0; $i < $start + 5; $i++) {
            SlotBoard::decrement('Malta', 'visitor'.$i);
        }
        $this->assertSame(0, SlotBoard::remainingFor('Malta'), 'floored at 0, never negative');
    }

    public function test_unknown_country_is_ignored(): void
    {
        SlotBoard::setCountriesForTesting($this->countries);
        $total = SlotBoard::total();
        SlotBoard::decrement('Narnia', 'visitorX');
        $this->assertSame($total, SlotBoard::total(), 'unknown country never decrements');
    }

    public function test_next_date_is_a_weekday_within_seven_days(): void
    {
        SlotBoard::setCountriesForTesting($this->countries);
        $now = CarbonImmutable::now();

        foreach ($this->countries as $c) {
            $d = SlotBoard::nextDate($c, $now);
            $this->assertTrue($d->isWeekday(), "$c date must be a weekday");
            $this->assertGreaterThanOrEqual(1, $now->startOfDay()->diffInDays($d->startOfDay()), "$c must be in the future");
            $this->assertLessThanOrEqual(7, $now->startOfDay()->diffInDays($d->startOfDay()), "$c must be within 7 days");
        }

        // Deterministic per country/week.
        $this->assertEquals(
            SlotBoard::nextDate('France', $now)->toDateString(),
            SlotBoard::nextDate('France', $now)->toDateString()
        );
    }

    public function test_match_country_resolves_exact_dest_and_path_only(): void
    {
        SlotBoard::setCountriesForTesting($this->countries);

        // Exact 'dest' field value (name or slug), case-insensitive.
        $this->assertSame('France', SlotBoard::matchCountry('France'));
        $this->assertSame('France', SlotBoard::matchCountry('france'));
        $this->assertSame('Czechia', SlotBoard::matchCountry('czechia'));

        // Landing path / referer URL: the country as a whole path segment resolves.
        $this->assertSame('France', SlotBoard::matchCountry('/schengen-visa/france'));
        $this->assertSame('Spain', SlotBoard::matchCountry('/schengen-visa/spain?gclid=abc123'));
        $this->assertSame('Germany', SlotBoard::matchCountry('https://beyondpassports.co.uk/schengen-visa/germany'));

        $this->assertNull(SlotBoard::matchCountry('/contact'));
        $this->assertNull(SlotBoard::matchCountry(''));
    }

    public function test_match_country_never_matches_a_substring_or_free_text(): void
    {
        SlotBoard::setCountriesForTesting($this->countries);

        // Free text that merely mentions a country must NOT decrement it (controllers pass
        // dest/path, not chat bodies) — this is the tightened, exact-match behaviour.
        $this->assertNull(SlotBoard::matchCountry('Hi, I want a Spain appointment'));
        $this->assertNull(SlotBoard::matchCountry('I love france and italy'));

        // Substring of a word that contains a country name must not match either.
        $this->assertNull(SlotBoard::matchCountry('francexyz'));
        $this->assertNull(SlotBoard::matchCountry('/blog/how-france-visas-work'));
    }
}
