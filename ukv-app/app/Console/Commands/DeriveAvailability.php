<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Destination;
use App\Models\SupplyNode;
use App\Services\AvailabilityService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Derive marketing availability snapshots from real booked appointments.
 *
 * Reads upcoming confirmed appointments (status=booked, scheduled_at in the future) whose order is
 * for a Schengen destination that has a bookable centre, then publishes a "derived" availability
 * snapshot for that centre via AvailabilityService::setSnapshot(). This gives the public board a
 * real, evidence-backed date when ops haven't set one manually.
 *
 * MANUAL-WINS is enforced inside setSnapshot(): a derived write never clobbers a fresh manual
 * snapshot — those rows are returned untouched and counted as "skipped" here.
 *
 * Band heuristic (documented, deliberately simple): an appointment within ~21 days signals a tight
 * window => 'limited'; anything further out => 'good'. The soonest appointment per centre wins.
 *
 * Safe no-op: if the appointments data source is absent (table/column missing), the command logs
 * "no appointment data source" and exits SUCCESS rather than failing the scheduler.
 */
class DeriveAvailability extends Command
{
    /** Appointments at or within this many days are banded 'limited' rather than 'good'. */
    private const TIGHT_WINDOW_DAYS = 21;

    /** @var string */
    protected $signature = 'availability:derive';

    /** @var string */
    protected $description = 'Derive centre availability snapshots from upcoming booked appointments';

    public function handle(AvailabilityService $availability): int
    {
        // Safe no-op if the appointment data source isn't present.
        if (! Schema::hasTable('appointments') || ! Schema::hasColumn('appointments', 'scheduled_at')) {
            $this->line('No appointment data source — skipping derive.');
            Log::info('availability:derive skipped — no appointment data source.');

            return self::SUCCESS;
        }

        $now = Carbon::now();

        // Upcoming confirmed appointments, soonest first so the earliest date per centre wins.
        $appointments = Appointment::query()
            ->where('status', 'booked')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', $now->toDateString())
            ->with('order.destination')
            ->orderBy('scheduled_at')
            ->get();

        $written = 0;
        $skipped = 0;
        $seenNodes = [];

        foreach ($appointments as $appointment) {
            $order = $appointment->order;
            $destination = $order?->destination;

            // Only Schengen destinations feed the (Schengen-only) board.
            if (! $destination instanceof Destination || $destination->visa_type !== 'Schengen') {
                continue;
            }

            // Resolve the bookable centre for this destination (same rule as parseBulk/byDestination).
            /** @var SupplyNode|null $node */
            $node = $destination->supplyNodes()->where('we_book_here', true)->first();
            if (! $node) {
                continue;
            }

            $nodeId = (int) $node->getKey();

            // Soonest first; only the earliest appointment per centre should drive its snapshot.
            if (isset($seenNodes[$nodeId])) {
                continue;
            }
            $seenNodes[$nodeId] = true;

            $date = $appointment->scheduled_at;
            $band = $date->lessThanOrEqualTo($now->copy()->addDays(self::TIGHT_WINDOW_DAYS))
                ? 'limited'
                : 'good';

            $snapshot = $availability->setSnapshot(
                supplyNodeId: $nodeId,
                nextAvailableOn: $date,
                band: $band,
                source: 'derived',
            );

            // MANUAL-WINS: setSnapshot returns the untouched existing MANUAL row when it declines to
            // write. A row coming back as 'derived' was actually written (created or updated).
            if ($snapshot->source === 'derived') {
                $written++;
            } else {
                $skipped++;
            }
        }

        $this->info("Derived availability: {$written} written, {$skipped} skipped (manual-wins/no-op).");

        Log::info('Derived availability snapshots from appointments.', [
            'written' => $written,
            'skipped' => $skipped,
            'appointments_considered' => $appointments->count(),
        ]);

        return self::SUCCESS;
    }
}
