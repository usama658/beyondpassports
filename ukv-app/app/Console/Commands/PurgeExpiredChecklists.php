<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ChecklistRequest;
use Illuminate\Console\Command;

/**
 * GDPR retention (#71): delete checklist requests older than the retention window.
 * They hold the visitor's trip + situation inputs, so they expire like uploaded documents.
 */
final class PurgeExpiredChecklists extends Command
{
    protected $signature = 'ukv:purge-checklists';

    protected $description = 'GDPR retention: delete checklist requests older than the retention window';

    public function handle(): int
    {
        $days = (int) config('ukv.doc_retention_days', 90);
        $cutoff = now()->subDays($days);

        $deleted = ChecklistRequest::query()->where('created_at', '<', $cutoff)->delete();

        $this->info("Purged {$deleted} checklist request(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
