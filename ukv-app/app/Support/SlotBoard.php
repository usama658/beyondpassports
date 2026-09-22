<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Destination;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Dynamic weekly appointment-slots board (LOCKED spec: docs/appointment-slots-dynamic.md v1.1).
 *
 * 70 total slots/week split across the Schengen countries the board renders. The split is a
 * weighted RANDOM allocation reseeded every week (so almost every country's number changes
 * week to week) but STABLE within the week — deterministic from the "slot week" key, which
 * rolls over each Saturday 00:00 Europe/London. No cron: a new Saturday changes the key, which
 * changes the seed AND the cache/DB namespace, so counts reset automatically.
 *
 * Decrement is PER INQUIRY (server-side, from the three lead controllers), keyed to the lead's
 * destination country, deduped per visitor per country per week. Country -1 and total -1.
 * Never below 0; a country at 0 is reported as 'ask' so the board renders it exactly like a
 * no-availability country today.
 *
 * Storage: Laravel Cache (fast, read on render) backed by a durable DB table (slot_decrements).
 * Everything is wrapped so a cache/DB hiccup degrades to the seed allocation and NEVER breaks a
 * page render or a lead capture.
 *
 * Gated by config('ukv.slots.dynamic') (default off) — callers check the flag before using this.
 */
final class SlotBoard
{
    /** Weekly pool shared across all countries. */
    public const TOTAL = 70;

    /** Minimum slots every country shows at seed. (1 = more weekly per-country variance.) */
    public const FLOOR = 1;

    /** Higher-demand countries get proportionally more of the leftover pool. */
    private const POPULAR = ['France', 'Spain', 'Italy', 'Germany', 'Greece', 'Netherlands', 'Portugal'];

    private const POPULAR_WEIGHT = 3;

    /** Cache TTL for decrement/dedup keys — comfortably longer than one slot week. */
    private const TTL_SECONDS = 10 * 24 * 60 * 60;

    /** Test seam: override the country list without touching the DB. */
    private static ?array $countriesOverride = null;

    /**
     * The "slot week" key — the date (Y-m-d) of the most recent Saturday in Europe/London.
     * Rolls over every Saturday 00:00; drives both the random seed and the storage namespace.
     */
    public static function slotWeek(?CarbonImmutable $now = null): string
    {
        $now = ($now ?? CarbonImmutable::now())->setTimezone('Europe/London')->startOfDay();
        // Carbon: Saturday = 6. Step back to the most recent Saturday (today if it is Saturday).
        $daysSinceSat = ($now->dayOfWeek - CarbonImmutable::SATURDAY + 7) % 7;

        return $now->subDays($daysSinceSat)->format('Y-m-d');
    }

    /** Schengen countries the board renders, in a stable order. Cached; DB-backed. */
    public static function countries(): array
    {
        if (self::$countriesOverride !== null) {
            return self::$countriesOverride;
        }

        try {
            return Cache::remember('slots:countries', 3600, function (): array {
                return Destination::query()
                    ->where('visa_type', 'Schengen')
                    ->orderBy('name')
                    ->pluck('name')
                    ->all();
            });
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** @param array<int,string>|null $countries */
    public static function setCountriesForTesting(?array $countries): void
    {
        self::$countriesOverride = $countries;
    }

    /**
     * Weighted-random weekly allocation, sum == TOTAL, every country >= FLOOR.
     * Pure + deterministic for a given (countries, slotWeek): floor everyone, then hand out the
     * leftover one point at a time by weighted-random draw (popular countries weighted heavier),
     * reseeded per week so the winners rotate. Largest bucket absorbs any final rounding.
     *
     * @param  array<int,string>  $countries
     * @return array<string,int>
     */
    public static function allocationFor(array $countries, string $slotWeek): array
    {
        $countries = array_values(array_unique(array_filter($countries)));
        $n = count($countries);
        if ($n === 0) {
            return [];
        }

        // If the floor alone would exceed the pool, spread the pool as evenly as possible instead
        // (keeps the invariant sum==TOTAL and avoids negative leftovers on very large country sets).
        if ($n * self::FLOOR >= self::TOTAL) {
            $alloc = [];
            $base = intdiv(self::TOTAL, $n);
            $rem = self::TOTAL % $n;
            foreach ($countries as $i => $c) {
                $alloc[$c] = $base + ($i < $rem ? 1 : 0);
            }

            return $alloc;
        }

        $alloc = array_fill_keys($countries, self::FLOOR);
        $leftover = self::TOTAL - ($n * self::FLOOR);

        // Weighted lottery: each country appears in the draw pool proportional to its weight.
        $pool = [];
        foreach ($countries as $c) {
            $w = in_array($c, self::POPULAR, true) ? self::POPULAR_WEIGHT : 1;
            for ($i = 0; $i < $w; $i++) {
                $pool[] = $c;
            }
        }

        mt_srand(crc32('bp-slots:'.$slotWeek));
        $poolSize = count($pool);
        for ($i = 0; $i < $leftover; $i++) {
            $alloc[$pool[mt_rand(0, $poolSize - 1)]]++;
        }
        mt_srand(); // restore non-deterministic RNG for the rest of the request

        return $alloc;
    }

    /** Live weekly allocation for the current slot week. @return array<string,int> */
    public static function allocation(): array
    {
        return self::allocationFor(self::countries(), self::slotWeek());
    }

    /**
     * Remaining slots per country = allocation - decrements, floored at 0.
     * Reads decrements from cache (fast); rebuilds cache from the durable table on a miss.
     * @return array<string,int>
     */
    public static function remaining(): array
    {
        $alloc = self::allocation();
        $dec = self::decrements();
        $out = [];
        foreach ($alloc as $country => $seed) {
            $out[$country] = max(0, $seed - ($dec[$country] ?? 0));
        }

        return $out;
    }

    /** Remaining slots for one country (0 if unknown). */
    public static function remainingFor(string $country): int
    {
        return self::remaining()[$country] ?? 0;
    }

    /** Total remaining slots across all countries. */
    public static function total(): int
    {
        return array_sum(self::remaining());
    }

    /**
     * Deterministic "next available" date for a country's board tile — always a WEEKDAY within the
     * NEXT 7 DAYS (locked rule), stable per country + slot-week. Picks from the weekdays that fall in
     * now+1..now+7, so it can never be a weekend, in the past, or beyond the 7-day window.
     */
    public static function nextDate(string $country, ?CarbonImmutable $now = null): CarbonImmutable
    {
        $now = $now ?? CarbonImmutable::now();
        $days = [];
        for ($i = 1; $i <= 7; $i++) {
            $d = $now->addDays($i);
            if ($d->isWeekday()) {
                $days[] = $d;
            }
        }
        if ($days === []) {
            $days[] = $now->addDay();
        }
        $seed = crc32($country.'|'.self::slotWeek($now));

        return $days[$seed % count($days)];
    }

    /**
     * Per-inquiry decrement: country -1 (reflected in the total), deduped per visitor per country
     * per slot week. Fire-and-forget — swallows all errors so it can never break lead capture.
     */
    public static function decrement(string $country, string $visitorHash): void
    {
        try {
            $country = trim($country);
            if ($country === '' || ! in_array($country, self::countries(), true)) {
                return;
            }

            $week = self::slotWeek();
            $slug = self::slug($country);
            $seenKey = "slots:$week:seen:$slug:".$visitorHash;

            // Dedup: first writer wins. Cache::add is atomic (returns false if key already set).
            if (! Cache::add($seenKey, 1, self::TTL_SECONDS)) {
                return;
            }

            // Durable count. Bump the existing row; if there is none yet, insert at 1. (No upsert —
            // some drivers REPLACE the row on conflict, which would reset the running count.)
            $updated = DB::table('slot_decrements')
                ->where('slot_week', $week)
                ->where('country', $country)
                ->increment('count', 1, ['updated_at' => now()]);

            if ($updated === 0) {
                try {
                    DB::table('slot_decrements')->insert([
                        'slot_week' => $week, 'country' => $country, 'count' => 1,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                    // Lost an insert race — the row now exists, so just bump it.
                    DB::table('slot_decrements')
                        ->where('slot_week', $week)
                        ->where('country', $country)
                        ->increment('count', 1, ['updated_at' => now()]);
                }
            }
        } catch (\Throwable $e) {
            // never break the lead path
        }
    }

    /**
     * Current decrement counts keyed by country for the live slot week — read straight from the
     * durable table (one small indexed query; always consistent, no cache staleness). Empty on any
     * failure so a DB hiccup degrades to the full seed allocation rather than breaking a render.
     * @return array<string,int>
     */
    private static function decrements(): array
    {
        try {
            return DB::table('slot_decrements')
                ->where('slot_week', self::slotWeek())
                ->pluck('count', 'country')
                ->map(fn ($v) => (int) $v)
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Resolve a board country from free text (a 'dest' field, a landing path like
     * /schengen-visa/france, or a WhatsApp message). Returns the canonical country name or null.
     * Longest names first so "Czech Republic" wins over a stray "Republic" substring.
     */
    public static function matchCountry(?string $text): ?string
    {
        $text = strtolower((string) $text);
        if ($text === '') {
            return null;
        }
        $countries = self::countries();
        usort($countries, fn ($a, $b) => strlen($b) <=> strlen($a));
        foreach ($countries as $c) {
            $needle = strtolower($c);
            if (str_contains($text, $needle) || str_contains($text, self::slug($c))) {
                return $c;
            }
        }

        return null;
    }

    /** Stable per-visitor hash for dedup (IP + User-Agent). Never contains raw PII in storage. */
    public static function visitorHash(\Illuminate\Http\Request $request): string
    {
        return substr(sha1(($request->ip() ?? '').'|'.(string) $request->userAgent()), 0, 20);
    }

    /**
     * Per-inquiry entry point for the lead controllers. No-op unless the dynamic board is on and a
     * real country resolves. Fully guarded — never throws into the lead path.
     */
    public static function recordInquiry(?string $destOrText, \Illuminate\Http\Request $request): void
    {
        try {
            if (! config('ukv.slots.dynamic')) {
                return;
            }
            $country = self::matchCountry($destOrText);
            if ($country !== null) {
                self::decrement($country, self::visitorHash($request));
            }
        } catch (\Throwable $e) {
            // never break lead capture
        }
    }

    private static function slug(string $country): string
    {
        return preg_replace('/[^a-z0-9]+/', '-', strtolower($country)) ?: 'x';
    }
}
