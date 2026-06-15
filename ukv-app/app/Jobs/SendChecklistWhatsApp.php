<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ChecklistRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Async WhatsApp delivery of a public document-checklist request — OPT-IN ONLY.
 *
 * Mirrors WhatsAppService (#25) EXACTLY in shape (guarded, idempotent-by-construction, config-driven
 * creds, free-form text body via the Graph API) but is STANDALONE: WhatsAppService::send() is private
 * and Order-bound, and the contract forbids editing it. This job therefore reuses its PATTERN against
 * a ChecklistRequest instead of an Order.
 *
 * The full list lives at the saved link (template/body limits), so the message is a SUMMARY + link.
 *
 * SAFETY (safe to ship pre-launch — identical guards to WhatsAppService):
 *   - Credentials come ONLY from config('services.whatsapp.*'); empty token/phone_id => log + no-op.
 *   - OPT-IN: the controller only dispatches this when the user picked the 'whatsapp' channel AND
 *     gave a phone. We re-check the phone here; no phone => no-op. There is no fallback recipient.
 *   - SerializesModels stores only the request id and re-fetches fresh on handle().
 *   - 24h-window note: business-initiated sends outside Meta's 24h window need an approved TEMPLATE.
 *     This sends a free-form text body (same as WhatsAppService's default); swap to a template
 *     payload once config('services.whatsapp.template') is approved in Meta Business Manager.
 */
final class SendChecklistWhatsApp implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const GRAPH_VERSION = 'v20.0';

    private const TIMEOUT = 15;

    /** WhatsApp 429/5xx are transient — retry the send a few times. */
    public int $tries = 3;

    public int $backoff = 30;

    public int $timeout = 30;

    public function __construct(public readonly ChecklistRequest $request) {}

    public function handle(): void
    {
        // OPT-IN gate: only proceed if the request actually asked for the whatsapp channel.
        $channels = is_array($this->request->channels) ? $this->request->channels : [];
        if (! in_array('whatsapp', $channels, true)) {
            return;
        }

        $token = trim((string) config('services.whatsapp.token', ''));
        $phoneId = trim((string) config('services.whatsapp.phone_id', ''));

        // Empty credentials => safe no-op (pre-launch).
        if ($token === '' || $phoneId === '') {
            Log::info('Checklist WhatsApp send skipped: credentials not configured.', [
                'checklist_id' => $this->request->getKey(),
            ]);

            return;
        }

        // Phone is REQUIRED. No phone => no-op (no fallback).
        $to = $this->normalisePhone($this->request->phone);
        if ($to === null) {
            Log::info('Checklist WhatsApp send skipped: request has no phone.', [
                'checklist_id' => $this->request->getKey(),
            ]);

            return;
        }

        $body = $this->body();

        try {
            $response = Http::withToken($token)
                ->timeout(self::TIMEOUT)
                ->acceptJson()
                ->post(
                    sprintf('https://graph.facebook.com/%s/%s/messages', self::GRAPH_VERSION, $phoneId),
                    [
                        'messaging_product' => 'whatsapp',
                        'recipient_type' => 'individual',
                        'to' => $to,
                        'type' => 'text',
                        'text' => [
                            'preview_url' => true,
                            'body' => $body,
                        ],
                    ],
                );
        } catch (Throwable $e) {
            Log::warning('Checklist WhatsApp send failed (transport).', [
                'checklist_id' => $this->request->getKey(),
                'error' => $e->getMessage(),
            ]);

            // Let the queue retry transient transport errors.
            throw $e;
        }

        if (! $response->successful()) {
            Log::warning('Checklist WhatsApp send rejected by Graph API.', [
                'checklist_id' => $this->request->getKey(),
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            return;
        }

        Log::info('Checklist WhatsApp sent.', [
            'checklist_id' => $this->request->getKey(),
            'to' => $to,
        ]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('SendChecklistWhatsApp permanently failed.', [
            'checklist_id' => $this->request->getKey(),
            'error' => $e->getMessage(),
        ]);
    }

    /** Short summary + saved link (the full list lives at the link, to fit message limits). */
    private function body(): string
    {
        $dest = trim((string) ($this->request->destination?->name ?? ''));
        $dest = $dest !== '' ? $dest : 'your trip';

        $items = is_array($this->request->items) ? $this->request->items : [];
        $count = count($items);

        $link = rtrim((string) config('ukv.base_url', ''), '/').'/checklist/'.$this->request->token;

        $countLine = $count > 0
            ? "Your {$dest} document checklist ({$count} item".($count === 1 ? '' : 's').') is ready.'
            : "Your {$dest} document checklist is ready.";

        return $countLine."\n\n"
            ."See the full list, save or share it here:\n{$link}\n\n"
            .'Independent service — not a government website. When you are ready, we can prepare and submit the application for you.';
    }

    /** Digits-only MSISDN (Graph API format) — mirrors WhatsAppService::normalisePhone(). */
    private function normalisePhone(mixed $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) ($phone ?? ''));

        return ($digits === null || $digits === '') ? null : $digits;
    }
}
