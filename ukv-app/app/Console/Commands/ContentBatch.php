<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Content batch — the producer half of the cycle in ONE prompt
 * (docs/social-automation-cycle.md stages 1-2).
 *
 *   php artisan content:batch
 *
 * Runs, in order and bounded well under 5 minutes:
 *   1. content:research   — ranked topic list (our data + market)  [~2s]
 *   2. content:carousels  — brand carousel PNGs for the top topics [browser render]
 *
 * Lands a ready-to-schedule asset folder from the simplest possible prompt.
 * The downstream half (schedule -> capture -> nurture -> measure) is
 * event-driven / external and runs on its own — it is NOT part of this batch.
 */
final class ContentBatch extends Command
{
    protected $signature = 'content:batch
        {--limit=30 : Topics to rank}
        {--carousels=8 : Top topics to render as carousels}';

    protected $description = 'One-prompt producer: research -> ranked topics -> brand carousel assets';

    public function handle(): int
    {
        $t0 = microtime(true);

        $this->line('1/2  Research…');
        $this->call('content:research', ['--limit' => (int) $this->option('limit')]);

        $this->line('2/2  Carousels…');
        $this->call('content:carousels', ['--count' => (int) $this->option('carousels')]);

        $secs = round(microtime(true) - $t0, 1);
        $this->newLine();
        $this->info("Producer batch done in {$secs}s (SLA: <300s).");
        if ($secs > 300) {
            $this->warn('Over the 5-minute SLA — check browser render time.');
        }

        return self::SUCCESS;
    }
}
