<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Async WhatsApp customer notification for a single order stage change. Queued wrapper around
 * WhatsAppService::notifyStageChange so the order pipeline never blocks on a Meta Graph API call.
 *
 * Dispatch AFTER a committed transition (see OrderService::transition()):
 *
 *   SendWhatsAppUpdate::dispatch($order, $from->value, $to->value)->afterCommit();
 *
 * Safety:
 *   - SerializesModels stores only the order id and re-fetches fresh on handle().
 *   - WhatsAppService no-ops when credentials are unset or the order has no phone, so this job is a
 *     cheap success pre-launch.
 *   - The service's own (order, event) guard makes retries idempotent — no double-send.
 */
final class SendWhatsAppUpdate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** WhatsApp 429/5xx are transient — retry the send a few times. */
    public int $tries = 3;

    /** Backoff between attempts (seconds). */
    public int $backoff = 30;

    /** Give up after this long regardless of attempts. */
    public int $timeout = 30;

    public function __construct(
        public readonly Order $order,
        public readonly string $from,
        public readonly string $to,
    ) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $whatsapp->notifyStageChange(
            $this->order,
            OrderStatus::from($this->from),
            OrderStatus::from($this->to),
        );
    }

    /**
     * Final-failure hook (after all retries). WhatsAppService logs per-call failures already; this
     * records that the whole notification gave up, so it can be reconciled later.
     */
    public function failed(Throwable $e): void
    {
        Log::error('SendWhatsAppUpdate permanently failed.', [
            'order_id' => $this->order->getKey(),
            'order_ref' => $this->order->order_ref,
            'from' => $this->from,
            'to' => $this->to,
            'error' => $e->getMessage(),
        ]);
    }
}
