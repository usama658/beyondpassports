<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\ChecklistNurture;
use App\Models\ChecklistRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * One-shot nurture for checklist-takers who consented to marketing: a couple of days after they built
 * a checklist, nudge them toward applying. Idempotent — each lead is stamped nurture_sent_at and never
 * emailed again. A 30-day floor stops a first run from blasting the whole back catalogue.
 */
class NurtureChecklists extends Command
{
    protected $signature = 'ukv:nurture-checklists {--delay=2 : Days to wait after a checklist before nurturing}
                                                    {--limit=200 : Max leads to process per run}';

    protected $description = 'Email consented checklist-takers a follow-up nudging them to apply';

    public function handle(): int
    {
        $delay = max(0, (int) $this->option('delay'));
        $limit = max(1, (int) $this->option('limit'));

        $olderThan = now()->subDays($delay);   // created at or before this (the wait window elapsed)
        $noOlderThan = now()->subDays(30);      // created no earlier than this (don't spam old rows)

        $leads = ChecklistRequest::query()
            ->whereNotNull('email')->where('email', '!=', '')
            ->where('marketing_consent', true)
            ->whereNull('nurture_sent_at')
            ->whereBetween('created_at', [$noOlderThan, $olderThan])
            ->limit($limit)
            ->get();

        $sent = 0;
        foreach ($leads as $lead) {
            Mail::to($lead->email)->queue(new ChecklistNurture($lead));
            $lead->forceFill(['nurture_sent_at' => now()])->save();
            $sent++;
        }

        $this->info("Nurture queued for {$sent} checklist lead(s).");

        return self::SUCCESS;
    }
}
