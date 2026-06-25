<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\CentreAvailability;
use App\Services\AvailabilityService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Tidy up long-expired marketing availability snapshots.
 *
 * CentreAvailability::status() already decays a stale snapshot to "ask" at read time, so the public
 * board never shows a fabricated date. This janitor is purely housekeeping: rows whose freshness
 * window lapsed a long time ago (well past — > 30 days) keep their stored next_available_on/band
 * around as dead data. We null those out so the stored row matches what the board actually reports
 * ("ask"), keeping ops screens and exports clean.
 *
 * Idempotent: rows already nulled are skipped (we only touch rows that still carry a date or band).
 * Always exits SUCCESS — a scheduled heartbeat must not fail the scheduler. Logs the swept count and
 * the count currently considered stale (via AvailabilityService::staleCentres()) for ops visibility.
 */
class SweepAvailability extends Command
{
    /** Rows must be expired by more than this many days before we tidy them. */
    private const STALE_GRACE_DAYS = 30;

    /** @var string */
    protected $signature = 'availability:sweep';

    /** @var string */
    protected $description = 'Null out long-expired centre availability snapshots (board already shows "ask")';

    public function handle(AvailabilityService $availability): int
    {
        $cutoff = Carbon::now()->subDays(self::STALE_GRACE_DAYS);

        // Only touch rows that are well past expiry AND still carry stored data to clear. This makes
        // the command idempotent: a second run finds nothing left to null.
        $swept = CentreAvailability::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $cutoff)
            ->where(function ($q): void {
                $q->whereNotNull('next_available_on')
                    ->orWhereNotNull('band');
            })
            ->update([
                'next_available_on' => null,
                'band' => null,
            ]);

        // Count of bookable Schengen centres currently needing attention (missing/stale/expiring).
        $stale = $availability->staleCentres()->count();

        if ($swept > 0) {
            $this->info("Swept {$swept} long-expired availability snapshot(s).");
        } else {
            $this->line('No long-expired availability snapshots to sweep.');
        }

        $this->line("Currently stale centres needing attention: {$stale}.");

        Log::info('Swept long-expired availability snapshots.', [
            'swept' => $swept,
            'stale_centres' => $stale,
            'grace_days' => self::STALE_GRACE_DAYS,
        ]);

        return self::SUCCESS;
    }
}
