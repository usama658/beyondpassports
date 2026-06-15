<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Order;
use App\Services\HubSpotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Async CRM sync for a single order: upsert the contact, then the deal, and optionally append
 * a timeline note. Wraps HubSpotService so the order pipeline never blocks on (or is broken by)
 * a HubSpot call.
 *
 * Dispatch this AFTER a committed order change (create / status transition). See reply notes for
 * the exact OrderService seams. Example:
 *
 *   SyncOrderToHubSpot::dispatch($order);
 *   SyncOrderToHubSpot::dispatch($order, "Status changed: {$from} -> {$to}.");
 *
 * Safety:
 *   - SerializesModels stores only the order id and re-fetches fresh on handle().
 *   - When no HubSpot token is configured, HubSpotService no-ops, so this job is a cheap success
 *     pre-launch.
 *   - The note text is the caller's responsibility; pass ONLY non-sensitive summaries (never
 *     passport numbers or document contents).
 */
final class SyncOrderToHubSpot implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** Retry the whole sync a few times — HubSpot 429/5xx are transient. */
    public int $tries = 3;

    /** Exponential-ish backoff between attempts (seconds). */
    public int $backoff = 30;

    /** Give up after this long regardless of attempts. */
    public int $timeout = 60;

    public function __construct(
        public readonly Order $order,
        public readonly ?string $note = null,
    ) {}

    public function handle(HubSpotService $hubspot): void
    {
        // Contact first so the deal/note can associate to it.
        $hubspot->upsertContact($this->order);
        $hubspot->upsertDeal($this->order);

        if ($this->note !== null && trim($this->note) !== '') {
            $hubspot->addTimelineNote($this->order, $this->note);
        }
    }

    /**
     * Final-failure hook (after all retries). HubSpotService already logs per-call failures;
     * this records that the whole order sync gave up, so it can be reconciled later.
     */
    public function failed(Throwable $e): void
    {
        Log::error('SyncOrderToHubSpot permanently failed.', [
            'order_id' => $this->order->getKey(),
            'order_ref' => $this->order->order_ref,
            'error' => $e->getMessage(),
        ]);
    }

    /**
     * Coalesce rapid repeat syncs for the same order onto one key (requires ShouldBeUnique to
     * activate; left as a helper so it can be opted into without changing the dispatch sites).
     */
    public function uniqueId(): string
    {
        return 'hubspot-sync-'.$this->order->getKey();
    }
}
