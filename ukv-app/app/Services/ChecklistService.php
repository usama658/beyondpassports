<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ChecklistRequest;
use App\Models\Destination;

/**
 * Public document-checklist tool (build wave 1: core).
 *
 * Wraps the Document Requirements Engine (RequirementService) for the self-serve wizard:
 *   - build()  maps the wizard inputs to the engine evaluation context and returns the
 *              tailored checklist items (the on-screen, value-first render).
 *   - create() does the same, then SNAPSHOTS the computed items onto a persisted
 *              ChecklistRequest (with a minted token) so the saved link / PDF / email
 *              stay stable even if the admin-editable rules change afterwards.
 *
 * The returned item shape is the engine's:
 *   list<array{document_key:string,label:string,note:?string,category:string,mandatory:bool}>
 */
final class ChecklistService
{
    /**
     * Wizard input keys passed through to the RequirementService context. Listed
     * explicitly so unrelated form fields (contact, consent, CSRF, etc.) never leak
     * into rule evaluation. preview() supplies destination_slug + passport months.
     */
    private const CTX_KEYS = [
        'trip_purpose',
        'is_minor',
        'residency_status',
        'employment_status',
        'accommodation_type',
        'funding_source',
        'travel_date',
        'return_date',
        'visa_entries',
        'prior_refusal',
    ];

    public function __construct(private readonly RequirementService $requirements) {}

    /**
     * Compute the tailored checklist for a destination + wizard inputs. No persistence.
     *
     * @param  array<string, mixed>  $inputs
     * @return list<array{document_key:string,label:string,note:?string,category:string,mandatory:bool}>
     */
    public function build(Destination $destination, array $inputs): array
    {
        return $this->requirements->preview($destination, $this->toContext($inputs));
    }

    /**
     * Build the checklist and persist it as a ChecklistRequest. The computed items are
     * stored as a stable snapshot; contact + delivery preferences are recorded for the
     * delivery wave (email/WhatsApp opt-in) and lead capture.
     *
     * Recognised $contact keys: email, phone, channels (array), marketing_consent (bool), ip.
     *
     * @param  array<string, mixed>  $inputs
     * @param  array<string, mixed>  $contact
     */
    public function create(Destination $destination, array $inputs, array $contact = []): ChecklistRequest
    {
        $items = $this->build($destination, $inputs);

        return ChecklistRequest::create([
            'destination_id' => $destination->id,
            'inputs' => $inputs,
            'items' => $items,
            'email' => $contact['email'] ?? null,
            'phone' => $contact['phone'] ?? null,
            'channels' => $contact['channels'] ?? null,
            'marketing_consent' => (bool) ($contact['marketing_consent'] ?? false),
            'ip' => $contact['ip'] ?? null,
        ]);
    }

    /**
     * Map wizard inputs to the engine evaluation context (whitelisted keys only).
     *
     * @param  array<string, mixed>  $inputs
     * @return array<string, mixed>
     */
    private function toContext(array $inputs): array
    {
        $ctx = [];

        foreach (self::CTX_KEYS as $key) {
            if (array_key_exists($key, $inputs)) {
                $ctx[$key] = $inputs[$key];
            }
        }

        return $ctx;
    }
}
