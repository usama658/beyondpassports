<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CentreAvailability;
use App\Models\SupplyNode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * TEMPORARY illustrative availability for the public board, so it does not sit fully empty
 * before ops start maintaining real snapshots. Writes a realistic mix across regions; the rest
 * stay "Ask us".
 *
 * Honest by construction: confirmed_at = now, expires_at = now + FRESHNESS_DAYS, so every row
 * auto-decays to "Ask us" within the freshness window unless ops refresh it. The board always
 * carries "Indicative only. We confirm live availability with the centre before you pay."
 *
 * Keyed strictly on the schengen-centre-{slug} nodes (SchengenCentreSeeder) so it never touches
 * other supply nodes. Remove from ProductionSeeder once ops own the data (decay then takes over).
 */
class SampleAvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        // [slug, days-from-now, band]
        $samples = [
            ['spain', 10, 'good'],
            ['germany', 12, 'good'],
            ['portugal', 9, 'good'],
            ['denmark', 14, 'good'],
            ['poland', 11, 'good'],
            ['france', 18, 'limited'],
            ['italy', 25, 'limited'],
            ['netherlands', 22, 'limited'],
            ['greece', 28, 'limited'],
            ['austria', 15, 'limited'],
            ['czechia', 20, 'limited'],
        ];

        $now = Carbon::now();
        $count = 0;

        foreach ($samples as [$slug, $days, $band]) {
            $node = SupplyNode::firstWhere('node_key', 'schengen-centre-'.$slug);
            if (! $node) {
                continue;
            }

            // Centres do not operate at weekends — nudge a weekend date to the next weekday so the
            // board's "Next available" matches the picker's soonest bookable slot (which is weekday-only).
            $nextAvailable = $now->copy()->addDays($days);
            if (! $nextAvailable->isWeekday()) {
                $nextAvailable = $nextAvailable->nextWeekday();
            }

            CentreAvailability::updateOrCreate(
                ['supply_node_id' => $node->getKey()],
                [
                    'next_available_on' => $nextAvailable->toDateString(),
                    'band' => $band,
                    'source' => 'manual',
                    'note' => 'Sample data — replace with confirmed availability.',
                    'confirmed_at' => $now,
                    'expires_at' => $now->copy()->addDays(CentreAvailability::FRESHNESS_DAYS),
                ],
            );
            $count++;
        }

        $this->command?->info("Sample availability set for {$count} centres (decays in ".CentreAvailability::FRESHNESS_DAYS." days).");
    }
}
