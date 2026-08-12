<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CentreAvailability;
use App\Models\SupplyNode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * TEMPORARY dummy availability for EVERY Schengen application centre, so the public appointment
 * boards (/schengen-visa + the /schengen-visa-consultancy & /schengen-visa-agency landing pages)
 * show a live-looking slot for all 29 countries instead of "ask us" / 0-slot tiles.
 *
 * Same honesty guarantees as SampleAvailabilitySeeder: confirmed_at = now, expires_at = now +
 * FRESHNESS_DAYS, so every row auto-decays to "Ask us" within the freshness window unless ops
 * refresh it. The board always carries "Indicative only. We confirm live availability with the
 * centre before you pay."
 *
 * Covers all we_book_here `schengen-centre-*` nodes (SchengenCentreSeeder). Deterministic +
 * re-run safe (updateOrCreate keyed on supply_node_id). Pair with SampleSlotSeeder afterwards to
 * generate the matching per-centre slot inventory:
 *
 *   php artisan db:seed --class=DummyAllSchengenAvailabilitySeeder
 *   php artisan db:seed --class=SampleSlotSeeder
 */
class DummyAllSchengenAvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        $nodes = SupplyNode::query()
            ->where('we_book_here', true)
            ->where('node_key', 'like', 'schengen-centre-%')
            ->get();

        $now = Carbon::now();
        $count = 0;

        foreach ($nodes as $node) {
            // Deterministic spread from the node key: dates 5-27 days out, a realistic good/limited
            // mix (roughly two-thirds "Available", one-third "Limited"), so the board is not a wall
            // of identical green tiles.
            $seed = crc32((string) $node->node_key);
            $days = 5 + ($seed % 23);                 // 5..27 days out
            $band = ($seed % 3 === 0) ? 'limited' : 'good';

            $next = $now->copy()->addDays($days);
            if (! $next->isWeekday()) {                // centres are weekday-only
                $next = $next->nextWeekday();
            }

            CentreAvailability::updateOrCreate(
                ['supply_node_id' => $node->getKey()],
                [
                    'next_available_on' => $next->toDateString(),
                    'band' => $band,
                    'source' => 'manual',
                    'note' => 'Dummy availability (all Schengen) — indicative only, auto-decays; replace with confirmed data.',
                    'confirmed_at' => $now,
                    'expires_at' => $now->copy()->addDays(CentreAvailability::FRESHNESS_DAYS),
                ],
            );
            $count++;
        }

        $this->command?->info("DummyAllSchengenAvailabilitySeeder: availability set for {$count} Schengen centre nodes (decays in ".CentreAvailability::FRESHNESS_DAYS." days). Now run SampleSlotSeeder for matching slots.");
    }
}
