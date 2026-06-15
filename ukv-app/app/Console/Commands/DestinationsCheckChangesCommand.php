<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DataChangeService;
use Illuminate\Console\Command;

/**
 * Weekly AI change-detection (Module C, #138).
 *
 * Runs DataChangeService across every destination that has official `sources`: fetches each public
 * source page, asks the model to flag differences vs the stored facts, and creates `open`
 * DataChangeProposal rows (deduped) for a human to Accept/Dismiss in the Filament inbox.
 *
 * Safe by design: with no Anthropic key the service is a logged no-op; fetch/model failures are
 * logged per-source and never abort the run. The command always exits SUCCESS so a scheduled run
 * never reports failure for an external-service hiccup.
 */
class DestinationsCheckChangesCommand extends Command
{
    /** @var string */
    protected $signature = 'destinations:check-changes';

    /** @var string */
    protected $description = 'Detect changes between destination facts and their official sources (creates open proposals)';

    public function handle(DataChangeService $service): int
    {
        $stats = $service->run();

        $this->info(sprintf(
            'Change-detection complete: %d destination(s) scanned, %d source(s) checked, %d proposal(s) created, %d skipped.',
            $stats['destinations'],
            $stats['checked'],
            $stats['proposals_created'],
            $stats['skipped'],
        ));

        return self::SUCCESS;
    }
}
