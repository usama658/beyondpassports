<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Destination;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Daily freshness check (Module B).
 *
 * Flags destinations whose facts review is overdue — never reviewed, or last verified more than
 * `review_interval_days` ago (see Destination::scopeOverdueForReview). The result is logged and
 * collected here; the owner digest (#94) consumes the same scope to surface "data due for review".
 * This command does not email — it is the daily signal/heartbeat and a manual triage tool.
 *
 * Always exits SUCCESS (a scheduled heartbeat must not fail just because data is stale).
 */
class DestinationsFreshnessCommand extends Command
{
    /** @var string */
    protected $signature = 'destinations:freshness';

    /** @var string */
    protected $description = 'Flag destinations whose facts review is overdue (Module B freshness)';

    public function handle(): int
    {
        $now = Carbon::now();

        $overdue = Destination::query()
            ->overdueForReview()
            ->orderBy('facts_checked_at')
            ->get(['id', 'name', 'facts_checked_at', 'review_interval_days']);

        $count = $overdue->count();

        if ($count === 0) {
            $this->info('No destinations are overdue for review.');

            return self::SUCCESS;
        }

        $this->warn("{$count} destination(s) overdue for facts review:");

        $payload = [];
        foreach ($overdue as $destination) {
            $checked = $destination->facts_checked_at;
            $detail = $checked instanceof Carbon
                ? 'last reviewed '.$checked->diffForHumans($now, ['parts' => 1, 'syntax' => Carbon::DIFF_ABSOLUTE]).' ago'
                : 'never reviewed';

            $this->line("  - {$destination->name} ({$detail}, interval {$destination->review_interval_days}d)");

            $payload[] = [
                'id' => (int) $destination->getKey(),
                'name' => (string) $destination->name,
                'detail' => $detail,
            ];
        }

        // Structured log line so the owner digest / ops monitoring can pick it up.
        \Illuminate\Support\Facades\Log::info('Destinations overdue for facts review.', [
            'count' => $count,
            'destinations' => $payload,
        ]);

        return self::SUCCESS;
    }
}
