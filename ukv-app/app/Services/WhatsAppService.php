<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EventChannel;
use App\Enums\EventType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderEvent;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Business (Cloud API) lifecycle notifier.
 *
 * Mirrors EmailService: a guarded, idempotent dispatcher that sends a short status update to the
 * customer on the same customer-facing milestones the email pipeline covers (submitted, decision,
 * delivered/won, refunded). Invoked from OrderService::transition() via the queued
 * SendWhatsAppUpdate job, so the order pipeline never blocks on (or breaks because of) a Meta call.
 *
 * SAFETY (safe to ship pre-launch):
 *   - Credentials come ONLY from config('services.whatsapp.*') — never hardcoded.
 *   - Empty token OR phone_id => log + no-op (returns false). Nothing is sent, nothing recorded.
 *   - No customer phone on the order (the orders.phone column) => no-op. WhatsApp REQUIRES a
 *     destination MSISDN; there is no fallback.
 *
 * IDEMPOTENCY:
 *   - Once per (order, event) on channel=whatsapp. The guard reads order_events where
 *     channel=whatsapp and meta->event=<event>. We DELIBERATELY do NOT use the email_event column
 *     for the guard: order_events has a UNIQUE (order_id, email_event) index shared with the email
 *     pipeline, so writing email_event='submitted' here would collide with the email send for the
 *     same milestone. The whatsapp guard therefore lives in channel + meta->event and leaves
 *     email_event NULL (the unique index permits many NULLs per order).
 *   - An OrderEvent (channel=whatsapp, type=system) is recorded per successful send, acting as both
 *     audit log and journey note. A failed/no-op send records nothing, so a retry can try again.
 *
 * MESSAGE TEMPLATES / 24h WINDOW (IMPORTANT):
 *   Meta only allows free-form ("session") messages within 24h of the customer's last inbound
 *   message. Business-INITIATED messages outside that window (which is what these lifecycle pings
 *   are) MUST use a pre-approved message TEMPLATE. The template name is configurable via
 *   config('services.whatsapp.template').
 *   TODO: register the order-status template(s) in Meta Business Manager and confirm the body
 *   parameter order matches templateParameters() below before going live. Until templates are
 *   approved, real sends to out-of-window customers will be rejected by the Graph API (logged,
 *   swallowed — the pipeline is unaffected).
 */
final class WhatsAppService
{
    private const GRAPH_VERSION = 'v20.0';

    private const TIMEOUT = 15;

    // Canonical event keys (stored in order_events.meta->event for the whatsapp idempotency guard).
    public const EVENT_SUBMITTED = 'submitted';
    public const EVENT_DECISION = 'decision';
    public const EVENT_DELIVERED = 'delivered';
    public const EVENT_REFUNDED = 'refunded';

    /**
     * Stage-change hook called (via the queued job) on every real, allowed transition. Maps the
     * target status to a customer-facing WhatsApp update for the same milestones EmailService
     * covers. Statuses with no customer message (paid/awaiting_docs/doc_review/rejected) are
     * silent. Safe to call on every transition — guards live in send().
     */
    public function notifyStageChange(Order $order, OrderStatus $from, OrderStatus $to): bool
    {
        return match ($to) {
            OrderStatus::Submitted => $this->send(
                $order,
                self::EVENT_SUBMITTED,
                "Good news — your visa application ({$order->order_ref}) has been submitted to the authorities. We'll let you know the moment there's a decision.",
            ),
            OrderStatus::AwaitingDecision => $this->send(
                $order,
                self::EVENT_DECISION,
                "Your application ({$order->order_ref}) is now awaiting a decision. We're monitoring it for you and will update you as soon as we hear back.",
            ),
            OrderStatus::Delivered, OrderStatus::Won => $this->send(
                $order,
                self::EVENT_DELIVERED,
                "Your visa for {$order->destination_name} is ready and on its way to you (order {$order->order_ref}). Safe travels!",
            ),
            OrderStatus::Refunded => $this->send(
                $order,
                self::EVENT_REFUNDED,
                "We've processed a refund for your order {$order->order_ref}. It should reach your account within a few business days.",
            ),
            default => false,
        };
    }

    // -----------------------------------------------------------------------------------
    // Internals
    // -----------------------------------------------------------------------------------

    /**
     * Send one WhatsApp update to the order's customer, once per (order, event), then record the
     * audit/journey order_event.
     *
     * @return bool true if sent + recorded; false if skipped (no creds / no phone / already sent /
     *              API rejected).
     */
    private function send(Order $order, string $event, string $body): bool
    {
        $token = trim((string) config('services.whatsapp.token', ''));
        $phoneId = trim((string) config('services.whatsapp.phone_id', ''));

        // Empty credentials => safe no-op (pre-launch).
        if ($token === '' || $phoneId === '') {
            Log::info('WhatsApp send skipped: credentials not configured.', [
                'order_id' => $order->getKey(),
                'event' => $event,
            ]);

            return false;
        }

        // Customer phone is REQUIRED (orders.phone column). No phone => no-op.
        $to = $this->normalisePhone($order->phone);
        if ($to === null) {
            Log::info('WhatsApp send skipped: order has no customer phone.', [
                'order_id' => $order->getKey(),
                'event' => $event,
            ]);

            return false;
        }

        // Idempotency pre-check: never resend an event for an order (channel=whatsapp guard).
        if ($this->alreadySent($order, $event)) {
            return false;
        }

        try {
            $response = Http::withToken($token)
                ->timeout(self::TIMEOUT)
                ->acceptJson()
                ->post(
                    sprintf('https://graph.facebook.com/%s/%s/messages', self::GRAPH_VERSION, $phoneId),
                    $this->payload($to, $body),
                );
        } catch (\Throwable $e) {
            Log::warning('WhatsApp send failed (transport).', [
                'order_id' => $order->getKey(),
                'event' => $event,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        if (! $response->successful()) {
            Log::warning('WhatsApp send rejected by Graph API.', [
                'order_id' => $order->getKey(),
                'event' => $event,
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            return false;
        }

        $this->record($order, $event, $to);

        return true;
    }

    /**
     * Build the Graph API message payload.
     *
     * NOTE: this sends a free-form `text` message, which Meta only delivers inside the 24h
     * customer-service window. For business-initiated sends outside that window, swap to a
     * `template` payload (see templatePayload()) once the template named by
     * config('services.whatsapp.template') is approved in Meta Business Manager.
     *
     * @return array<string, mixed>
     */
    private function payload(string $to, string $body): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $body,
            ],
        ];
    }

    /**
     * Template payload for business-initiated sends outside the 24h window.
     * TODO: register the template in Meta Business Manager, then route send() through this once the
     * body parameter order is confirmed to match the approved template.
     *
     * @param  list<string>  $params  ordered body parameters
     * @return array<string, mixed>
     */
    private function templatePayload(string $to, array $params): array
    {
        $template = trim((string) config('services.whatsapp.template', ''));

        return [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $template,
                'language' => ['code' => 'en'],
                'components' => [[
                    'type' => 'body',
                    'parameters' => array_map(
                        static fn (string $p): array => ['type' => 'text', 'text' => $p],
                        $params,
                    ),
                ]],
            ],
        ];
    }

    /** Has this event already been sent for this order over WhatsApp? */
    private function alreadySent(Order $order, string $event): bool
    {
        return $order->events()
            ->where('channel', EventChannel::Whatsapp->value)
            ->where('meta->event', $event)
            ->exists();
    }

    /**
     * Write the single order_events row that is both audit log and journey note. email_event is
     * left NULL so this never collides with the email pipeline's unique (order_id, email_event)
     * index. The whatsapp guard is (channel=whatsapp, meta->event).
     */
    private function record(Order $order, string $event, string $to): void
    {
        try {
            OrderEvent::create([
                'order_id' => $order->getKey(),
                'occurred_at' => Carbon::now(),
                'agent' => 'system',
                'channel' => EventChannel::Whatsapp,
                'type' => EventType::System,
                'text' => "WhatsApp sent: {$event}",
                'email_event' => null,
                'meta' => [
                    'event' => $event,
                    'to' => $to,
                ],
            ]);
        } catch (QueryException $e) {
            // A concurrent worker already recorded this send — log and move on.
            Log::warning('Duplicate WhatsApp event suppressed.', [
                'order_id' => $order->getKey(),
                'event' => $event,
            ]);
        }
    }

    /**
     * Normalise a customer phone to the digits-only MSISDN the Graph API expects (no '+', spaces
     * or punctuation). Returns null when there is nothing usable.
     */
    private function normalisePhone(mixed $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) ($phone ?? ''));

        return ($digits === null || $digits === '') ? null : $digits;
    }
}
