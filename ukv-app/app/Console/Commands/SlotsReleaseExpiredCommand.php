<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SlotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Return lapsed held slots to the available pool (Wave 2 / B3).
 *
 * When a customer starts an application we temporarily HOLD a centre slot
 * (SlotService::hold, status=held + hold_expires_at). If that hold lapses without the order
 * completing, the slot must be released back to the pool so it can be offered again. This is the
 * scheduled janitor for that: it delegates entirely to SlotService::releaseExpired(), which flips
 * each expired hold back to 'available' and returns how many it freed.
 *
 * Intended to run on a short cadence (e.g. every five minutes) so abandoned holds don't strand
 * inventory. Logs the count for ops visibility. Always exits SUCCESS — a scheduled heartbeat must
 * not fail the scheduler just because zero (or many) slots were released.
 */
class SlotsReleaseExpiredCommand extends Command
{
    /** @var string */
    protected $signature = 'slots:release-expired';

    /** @var string */
    protected $description = 'Release expired held appointment slots back to the available pool';

    public function handle(SlotService $slots): int
    {
        $released = $slots->releaseExpired();

        if ($released > 0) {
            $this->info("Released {$released} expired held slot(s) back to the pool.");
        } else {
            $this->line('No expired held slots to release.');
        }

        // Structured log line so ops monitoring / the owner digest can pick it up.
        Log::info('Released expired held slots.', ['released' => $released]);

        return self::SUCCESS;
    }
}
