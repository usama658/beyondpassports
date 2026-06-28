<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SlotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Stock future appointment-slot inventory across every "we book here" centre in one go.
 *
 * Wave 2 ops helper: instead of the per-centre "Bulk add slots" action in the admin, this fills
 * the same window for ALL appointment-holding centres at once so the hold/auto-hold flow has slots
 * to offer when orders arrive. Idempotent — re-running extends the window without duplicating, and
 * (unless --keep-past) it first sweeps stale past-dated available slots. Delegates to
 * SlotService::provision(); logs counts for ops visibility.
 *
 * Examples:
 *   php artisan slots:provision            # next 4 weeks of weekday slots
 *   php artisan slots:provision 8          # next 8 weeks
 *   php artisan slots:provision 4 --keep-past
 */
class SlotsProvisionCommand extends Command
{
    /** @var string */
    protected $signature = 'slots:provision {weeks=4 : Upcoming weeks of weekday slots to fill} {--keep-past : Do not delete stale past-dated available slots first}';

    /** @var string */
    protected $description = 'Provision future available appointment slots across all "we book here" centres';

    public function handle(SlotService $slots): int
    {
        $weeks = max(1, (int) $this->argument('weeks'));

        $r = $slots->provision($weeks, cleanPast: ! $this->option('keep-past'));

        $this->info("Provisioned {$r['created']} new slot(s) across {$r['centres']} centre(s) for the next {$weeks} week(s); cleaned {$r['cleaned']} stale slot(s).");
        Log::info('Provisioned appointment slots.', $r + ['weeks' => $weeks]);

        return self::SUCCESS;
    }
}
