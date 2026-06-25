<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\CentreAvailability;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * CentreAvailability honesty-by-construction decay.
 *
 * status() is the single source of truth and decays at read time: a stale (expired) or dateless
 * snapshot reports "ask" regardless of stored band, so the public board can never show a fabricated
 * date. These are pure model tests — no DB write needed; we build unsaved models and set casts via
 * the same fillable/cast pipeline the app uses.
 */
final class CentreAvailabilityTest extends TestCase
{
    private function snapshot(array $attrs = []): CentreAvailability
    {
        $a = new CentreAvailability;
        $a->forceFill(array_merge([
            'next_available_on' => Carbon::now()->addDays(10)->toDateString(),
            'band' => 'good',
            'source' => 'manual',
            'confirmed_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addDays(5),
        ], $attrs));

        return $a;
    }

    public function test_status_is_ok_for_fresh_good(): void
    {
        $this->assertSame('ok', $this->snapshot(['band' => 'good'])->status());
    }

    public function test_status_is_lim_for_fresh_limited(): void
    {
        $this->assertSame('lim', $this->snapshot(['band' => 'limited'])->status());
    }

    public function test_status_decays_to_ask_when_expired(): void
    {
        $expired = $this->snapshot([
            'band' => 'good',
            'expires_at' => Carbon::now()->subDay(),
        ]);

        $this->assertSame('ask', $expired->status(), 'An expired snapshot must report ask.');
    }

    public function test_status_is_ask_when_date_is_null(): void
    {
        $this->assertSame('ask', $this->snapshot(['next_available_on' => null])->status());
    }

    public function test_status_is_ask_for_unknown_band(): void
    {
        // A non-stale, dated snapshot with no recognised band still falls through to ask.
        $this->assertSame('ask', $this->snapshot(['band' => null])->status());
    }

    public function test_is_stale_when_expires_at_past_or_null(): void
    {
        $this->assertTrue($this->snapshot(['expires_at' => Carbon::now()->subSecond()])->isStale());
        $this->assertTrue($this->snapshot(['expires_at' => null])->isStale());
        $this->assertFalse($this->snapshot(['expires_at' => Carbon::now()->addDay()])->isStale());
    }

    public function test_is_expiring_within_window_but_not_when_stale(): void
    {
        // Lapses inside the 2-day window -> expiring.
        $this->assertTrue($this->snapshot(['expires_at' => Carbon::now()->addDay()])->isExpiring(2));
        // Lapses well outside the window -> not expiring.
        $this->assertFalse($this->snapshot(['expires_at' => Carbon::now()->addDays(10)])->isExpiring(2));
        // Already lapsed -> stale, NOT "expiring".
        $this->assertFalse($this->snapshot(['expires_at' => Carbon::now()->subDay()])->isExpiring(2));
    }
}
