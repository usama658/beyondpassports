<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CentreSlot;
use App\Models\Destination;
use App\Services\AvailabilityService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * TEMPORARY illustrative held-slot inventory for the /schengen-visa per-centre picker,
 * DERIVED FROM the public availability board so the tile and the modal cannot disagree.
 *
 * The board tile reads CentreAvailability (band + next-available date, via AvailabilityService).
 * The picker modal reads CentreSlot. Previously the two were seeded independently, so a "Limited"
 * tile could open a modal full of slots, on dates that did not match the tile. This seeder keys the
 * slots to each country's board status:
 *
 *   ask  -> no slots        (modal shows "No published slots — ask us")
 *   lim  -> few slots       (2-3 dates x 1 time per centre — reads as "Limited")
 *   ok   -> more slots      (5-6 dates x 2 times per centre — reads as "Available")
 *
 * Start dates key off the board's next_available_on: the FIRST centre opens exactly on that date
 * (so the tile's "Next available" == the modal's soonest slot), later centres open progressively
 * further out. Per-centre variance (offset/cadence/times) comes from the node id, so centres of a
 * country still differ. Weekend dates nudge to the next weekday.
 *
 * Still illustrative, NOT verified portal availability. Picker + board keep the "we confirm live" /
 * "indicative only" framing. Replace before paid traffic (#95 / #96, DMCCA). Deterministic and
 * re-run safe: clears each centre's future available slots first. Only touches `available` future
 * slots — held/booked inventory tied to orders is left alone.
 */
class SampleSlotSeeder extends Seeder
{
    private const TIME_SETS_OK = [
        ['09:00', '13:30'],
        ['10:00', '14:00'],
        ['09:30', '15:00'],
    ];

    private const TIME_SETS_LIM = [
        ['10:30'],
        ['14:00'],
        ['09:30'],
    ];

    public function run(AvailabilityService $availability): void
    {
        $board = $availability->byDestination('Schengen');

        $destinations = Destination::query()
            ->where('visa_type', 'Schengen')
            ->with(['supplyNodes' => fn ($q) => $q->where('we_book_here', true)])
            ->get();

        $now = Carbon::now();
        $slots = 0;

        foreach ($destinations as $destination) {
            $status = $board[$destination->getKey()]['status'] ?? 'ask';
            $baseDate = $board[$destination->getKey()]['next_available_on'] ?? null;

            foreach ($destination->supplyNodes->values() as $index => $node) {
                // Reset this centre's upcoming available slots so re-runs stay clean.
                CentreSlot::query()
                    ->where('supply_node_id', $node->getKey())
                    ->where('status', 'available')
                    ->where('slot_at', '>', $now)
                    ->delete();

                // No board availability (or no date) => no published slots for this country.
                if ($status === 'ask' || $baseDate === null) {
                    continue;
                }

                $seed = (int) $node->getKey();

                // Band drives how many slots a centre carries.
                if ($status === 'ok') {
                    $dateCount = 5 + ($seed % 2);                              // 5-6 dates
                    $times = self::TIME_SETS_OK[$seed % count(self::TIME_SETS_OK)];
                } else {                                                       // 'lim'
                    $dateCount = 2 + ($seed % 2);                              // 2-3 dates
                    $times = self::TIME_SETS_LIM[$seed % count(self::TIME_SETS_LIM)];
                }

                // First centre opens ON the board date (tile == modal soonest); others open later.
                $startOffset = $index === 0 ? 0 : ($index * 3) + ($seed % 4);
                $gap = 2 + ($seed % 3);                                        // every 2-4 weekdays

                $day = Carbon::parse($baseDate)->startOfDay()->addDays($startOffset);
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
        }

        $this->command?->info("SampleSlotSeeder: {$slots} board-synced illustrative slots seeded.");
    }
}
