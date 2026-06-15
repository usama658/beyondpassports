<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ChecklistRequest;
use App\Services\HubSpotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Async CRM lead sync for a public document-checklist request. Upserts a HubSpot contact tagged
 * source=document-checklist via HubSpotService::upsertLead(). Wraps the call so the public tool never
 * blocks on (or breaks because of) a HubSpot call.
 *
 * CONSENT SPLIT (transactional vs marketing):
 *   - We sync the lead whenever we have an email AND the user asked us to deliver something (i.e. they
 *     submitted a contact for the checklist). Identity + the lead-source tag are TRANSACTIONAL record-
 *     keeping of a request they made.
 *   - MARKETING fields (the HubSpot marketing-consent property that gates nurture #23) are passed
 *     ONLY when $request->marketing_consent === true. With consent false, upsertLead() sends nothing
 *     marketing-related, so the lead can never be enrolled into nurture without an explicit opt-in.
 *
 * Safety:
 *   - SerializesModels stores only the request id and re-fetches fresh on handle().
 *   - When no HubSpot token is configured, upsertLead() no-ops, so this job is a cheap success pre-launch.
 *   - NO travel intent / inputs / passport data is sent — only contact identity + destination + source.
 */
final class SyncChecklistLead implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** HubSpot 429/5xx are transient — retry a few times. */
    public int $tries = 3;

    public int $backoff = 30;

    public int $timeout = 60;

    public function __construct(public readonly ChecklistRequest $request) {}

    public function handle(HubSpotService $hubspot): void
    {
        $email = trim((string) ($this->request->email ?? ''));
        if ($email === '') {
            // No email captured => nothing to sync (e.g. WhatsApp-only delivery without an email).
            return;
        }

        $hubspot->upsertLead(
            email: $email,
            name: null, // the public tool captures no name — identity is email/phone only
            phone: $this->request->phone,
            destination: $this->request->destination?->name,
            source: 'document-checklist',
            marketingConsent: (bool) $this->request->marketing_consent,
        );
    }

    public function failed(Throwable $e): void
    {
        Log::error('SyncChecklistLead permanently failed.', [
            'checklist_id' => $this->request->getKey(),
            'error' => $e->getMessage(),
        ]);
    }
}
