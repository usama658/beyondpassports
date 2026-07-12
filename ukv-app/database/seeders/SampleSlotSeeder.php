<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CentreSlot;
use App\Models\SupplyNode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * TEMPORARY illustrative held-slot inventory for the /schengen-visa per-centre picker.
 *
 * slots:provision fills every "we book here" centre with the SAME uniform weekday grid, so all
 * centres show identical dates — which reads as fake. This replaces that with a per-centre pattern:
 * each centre gets a distinct opening offset, cadence and time-of-day set, so London / Manchester /
 * Edinburgh of the same country differ (as real centres do).
 *
 * Still illustrative, NOT verified portal availability. The picker frames every slot as
 * "we confirm it live with the centre and book it for you", and the board carries the
 * "Indicative only" disclaimer. Replace with real bookable availability before paid traffic
 * (#95 / #96, DMCCA). Deterministic (variance from node id) and re-run safe: it clears each
 * centre's future available slots first, so no duplicates and no drift.
 *
 * Only touches `available` future slots — held/booked inventory tied to orders is left alone.
 */
class SampleSlotSeeder extends Seeder
{
    /** Time-of-day sets a centre can run (varied so centres do not all open at 09:00). */
    private const TIME_SETS = [
        ['09:00', '11:30'],
        ['10:00', '14:00'],
        ['09:30', '13:30', '15:30'],
        ['08:30', '10:30', '13:00'],
    ];

    public function run(): void
    {
        $centres = SupplyNode::query()->where('we_book_here', true)->get();
        $today = Carbon::today();
        $slots = 0;

        foreach ($centres as $node) {
            // Reset this centre's upcoming available slots so re-runs stay clean.
            CentreSlot::query()
                ->where('supply_node_id', $node->getKey())
                ->where('status', 'available')
                ->where('slot_at', '>', Carbon::now())
                ->delete();

            $seed = (int) $node->getKey();
            $startOffset = 2 + ($seed % 12);          // first opening 2–13 days out
            $gap = 1 + ($seed % 3);                   // then every 1–3 weekdays
            $dateCount = 4 + ($seed % 4);             // 4–7 distinct dates
            $times = self::TIME_SETS[$seed % count(self::TIME_SETS)];

            $day = $today->copy()->addDays($startOffset);
            $made = 0;

            while ($made < $dateCount) {
                if (! $day->isWeekday()) {
                    $day->addDay();

                    continue;
                }

                foreach ($times as $time) {
                    [$h, $m] = array_pad(explode(':', $time), 2, '0');
                    CentreSlot::create([
                        'supply_node_id' => $node->getKey(),
                        'slot_at' => $day->copy()->setTime((int) $h, (int) $m, 0),
                        'status' => 'available',
                    ]);
                    $slots++;
                }

                $made++;
                $day->addDays($gap);
            }
        }

        $this->command?->info("SampleSlotSeeder: {$slots} varied illustrative slots across {$centres->count()} centres.");
    }
}
