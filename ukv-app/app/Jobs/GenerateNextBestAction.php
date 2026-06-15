<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\EventChannel;
use App\Enums\EventType;
use App\Models\Order;
use App\Services\AiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Queued job: run AiService::nextBestAction for one order and record the (advisory) result as an
 * OrderEvent.
 *
 * The recorded event is clearly marked AI-generated and advisory:
 *   - type    = system   (not a human note / stage change)
 *   - channel = internal (never customer-facing)
 *   - agent   = 'ai'     (so it's never attributed to a human operator)
 *   - meta.advisory = true, meta.source = 'ai_next_best_action'
 *
 * Safety:
 *   - SerializesModels stores only the order id and re-fetches fresh on handle().
 *   - When no Anthropic key is configured, AiService no-ops (returns null) and this job records
 *     nothing — a cheap success pre-launch.
 *   - Nothing here changes the order status or any customer-facing field; it only appends an event.
 *
 * Dispatch this AFTER a committed order change where a fresh recommendation is useful — e.g. on a
 * status transition, when a barrier opens, or when documents land. See the reply notes for the
 * exact OrderService seams. Example:
 *
 *   GenerateNextBestAction::dispatch($order);
 */
final class GenerateNextBestAction implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** A couple of retries — Anthropic 429/5xx are transient. */
    public int $tries = 3;

    /** Backoff between attempts (seconds). */
    public int $backoff = 30;

    /** Give up after this long regardless of attempts. */
    public int $timeout = 60;

    public function __construct(
        public readonly Order $order,
    ) {}

    public function handle(AiService $ai): void
    {
        // Load only the relations AiService reads, so the SAFE summary can be built without lazy
        // queries. (No PII relations are touched.)
        $this->order->loadMissing(['documents', 'barriers']);

        $recommendation = $ai->nextBestAction($this->order);

        if ($recommendation === null) {
            // No key (no-op) or an AI failure already logged by AiService — record nothing.
            return;
        }

        $this->order->events()->create([
            'occurred_at' => now(),
            'agent' => 'ai',
            'channel' => EventChannel::Internal,
            'type' => EventType::System,
            'text' => 'AI suggested next action (advisory draft): '.$recommendation,
            'meta' => [
                'source' => 'ai_next_best_action',
                'advisory' => true,
                'ai_generated' => true,
            ],
        ]);
    }

    /**
     * Final-failure hook (after all retries). AiService already logs per-call failures; this records
     * that the whole job gave up, so it can be reconciled later. No secrets / PII logged.
     */
    public function failed(Throwable $e): void
    {
        Log::error('GenerateNextBestAction permanently failed.', [
            'order_id' => $this->order->getKey(),
            'order_ref' => $this->order->order_ref,
            'error' => $e->getMessage(),
        ]);
    }

    /**
     * Coalesce rapid repeat dispatches for the same order onto one key (requires ShouldBeUnique to
     * activate; left as a helper so it can be opted into without changing dispatch sites).
     */
    public function uniqueId(): string
    {
        return 'ai-next-best-action-'.$this->order->getKey();
    }
}
