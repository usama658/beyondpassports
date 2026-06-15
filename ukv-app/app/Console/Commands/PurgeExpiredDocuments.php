<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\EventChannel;
use App\Enums\EventType;
use App\Models\Document;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * GDPR document retention auto-purge.
 *
 * Ported from ukv-retention.php (Gap #71). The WP policy:
 *   - an order's uploaded documents are force-deleted (files + attachment posts) a configurable
 *     number of days AFTER the order CLOSES (ukv_closed_at);
 *   - the default window is 90 days (UKV_RETENTION_DEFAULT);
 *   - the ORDER RECORD itself is NEVER deleted — only its uploaded documents;
 *   - the run is idempotent (a `docs_purged` flag stops re-processing);
 *   - a system/internal journey note is appended: "Documents purged per retention policy";
 *   - runs daily via cron.
 *
 * Here: closure time is `orders.closed_at` (set by OrderService::transition when an order
 * enters a closed status). For every order closed longer ago than the window whose docs are not
 * yet purged, we delete each document's stored file, stamp `documents.purged_at` (the row is
 * kept for audit per the migration comment), set `orders.docs_purged = true`, and log the event.
 *
 * The window is config-driven: config('ukv.doc_retention_days') (env UKV_DOC_RETENTION_DAYS),
 * default 90 to match WP.
 */
class PurgeExpiredDocuments extends Command
{
    /** @var string */
    protected $signature = 'ukv:purge-documents
        {--dry-run : Report what would be purged without deleting anything}';

    /** @var string */
    protected $description = 'GDPR retention: purge uploaded documents for orders closed beyond the retention window';

    public function handle(OrderService $orders): int
    {
        $days = (int) config('ukv.doc_retention_days', 90);
        if ($days < 1) {
            $days = 90;
        }

        $cutoff = Carbon::now()->subDays($days);
        $dryRun = (bool) $this->option('dry-run');

        // Candidate orders: closed on/before the cutoff and not already purged. Eager-load the
        // not-yet-purged documents so we only touch files that still exist.
        $orders_query = Order::query()
            ->whereNotNull('closed_at')
            ->where('closed_at', '<=', $cutoff)
            ->where(function ($q) {
                $q->whereNull('docs_purged')->orWhere('docs_purged', false);
            })
            ->with(['documents' => fn ($q) => $q->whereNull('purged_at')]);

        $ordersPurged = 0;
        $filesPurged = 0;

        $orders_query->chunkById(100, function ($chunk) use ($orders, $dryRun, &$ordersPurged, &$filesPurged): void {
            foreach ($chunk as $order) {
                /** @var \Illuminate\Support\Collection<int, Document> $docs */
                $docs = $order->documents;

                foreach ($docs as $doc) {
                    /** @var Document $doc */
                    if ($dryRun) {
                        $this->line("  [dry-run] would purge document #{$doc->getKey()} ({$doc->original_name}) of order {$order->order_ref}");
                        $filesPurged++;

                        continue;
                    }

                    if ($doc->disk && $doc->path && Storage::disk($doc->disk)->exists($doc->path)) {
                        Storage::disk($doc->disk)->delete($doc->path);
                    }

                    // Keep the row for audit; clear the path + stamp purged_at.
                    $doc->forceFill([
                        'path' => '',
                        'purged_at' => Carbon::now(),
                    ])->save();

                    $filesPurged++;
                }

                if ($dryRun) {
                    $this->line("[dry-run] order {$order->order_ref}: {$docs->count()} document(s) eligible");
                    $ordersPurged++;

                    continue;
                }

                $order->docs_purged = true;
                $order->save();

                $orders->recordEvent(
                    $order,
                    EventType::System,
                    'Documents purged per retention policy',
                    channel: EventChannel::Internal,
                    agent: 'system',
                    meta: ['retention_days' => (int) config('ukv.doc_retention_days', 90), 'documents' => $docs->count()],
                );

                $ordersPurged++;
            }
        });

        $verb = $dryRun ? 'would purge' : 'purged';
        $this->info("Retention run complete ({$days}-day window): {$verb} {$filesPurged} document(s) across {$ordersPurged} order(s).");

        return self::SUCCESS;
    }
}
