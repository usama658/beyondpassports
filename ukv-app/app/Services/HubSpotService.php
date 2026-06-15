<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\OrderTier;
use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HubSpot CRM sync for the UK visa app (v3 API, private-app token).
 *
 * Mirrors the WP source's intent (wp-content/mu-plugins/ukv-zapier.php + ukv-quicknote.php):
 *   - ukv-zapier pushed an order-summary payload (ref/name/email/destination/tier/status/total)
 *     on create + status change. Here that becomes a HubSpot CONTACT (by email) + a DEAL.
 *   - ukv-quicknote pushed journey notes to the linked HubSpot deal/contact timeline. Here that
 *     becomes addTimelineNote(): a Note object associated to the contact.
 *
 * SAFETY:
 *   - Token comes ONLY from config('services.hubspot.token') — never hardcoded.
 *   - Empty token => every method is a logged no-op, so this is safe to ship pre-launch.
 *   - PRIVACY: passport numbers, documents, and intake/eligibility axes are NEVER sent. Only
 *     contact identity (name/email/phone) + order-summary fields (ref/destination/tier/status/
 *     total/travel date) leave the building. See $contactProperties()/$dealProperties().
 *
 * Idempotency:
 *   - Contacts are upserted by email via the v3 search API (find-then-create/update).
 *   - Deals are upserted via the stored Order->hubspot_deal_id when present, otherwise created
 *     and the new id is written back to the order (so later syncs update in place).
 *
 * All HTTP failures are logged and swallowed (return null/false) so a CRM outage never breaks
 * the order pipeline. Retries are the queued job's responsibility (SyncOrderToHubSpot).
 */
final class HubSpotService
{
    private const BASE_URL = 'https://api.hubapi.com';

    private const TIMEOUT = 15;

    /**
     * Map our pipeline status -> a HubSpot deal stage id.
     *
     * NOTE: these are the DEFAULT HubSpot "sales pipeline" internal stage ids. They are an
     * ASSUMPTION — if a custom pipeline is configured in the portal, override this map (or move
     * it to config) with the real internal stage ids from Settings > Objects > Deals > Pipelines.
     * Unmapped/unknown statuses fall back to self::DEFAULT_STAGE.
     *
     * @var array<string, string>
     */
    private const STAGE_MAP = [
        OrderStatus::Paid->value => 'qualifiedtobuy',
        OrderStatus::AwaitingDocs->value => 'presentationscheduled',
        OrderStatus::DocReview->value => 'presentationscheduled',
        OrderStatus::Submitted->value => 'decisionmakerboughtin',
        OrderStatus::AwaitingDecision->value => 'contractsent',
        OrderStatus::Delivered->value => 'contractsent',
        OrderStatus::Won->value => 'closedwon',
        OrderStatus::Rejected->value => 'closedlost',
        OrderStatus::Refunded->value => 'closedlost',
    ];

    private const DEFAULT_STAGE = 'qualifiedtobuy';

    // -----------------------------------------------------------------------------------
    // Public API
    // -----------------------------------------------------------------------------------

    /**
     * Create or update a HubSpot contact for the order's customer, keyed by email.
     *
     * Sends only contact identity + the order's destination (a useful CRM filter). Returns the
     * HubSpot contact id on success, or null on no-op / failure.
     */
    public function upsertContact(Order $order): ?string
    {
        if (! $this->enabled()) {
            return $this->disabledNoop('upsertContact', $order);
        }

        $email = $this->clean($order->email);
        if ($email === null) {
            Log::warning('HubSpot upsertContact skipped: order has no email.', [
                'order_ref' => $order->order_ref,
            ]);

            return null;
        }

        $properties = $this->contactProperties($order);

        $existingId = $this->findContactIdByEmail($email);

        if ($existingId !== null) {
            $res = $this->client()->patch("/crm/v3/objects/contacts/{$existingId}", [
                'properties' => $properties,
            ]);

            return $this->idFromResponse($res, 'upsertContact(update)', $order) ? $existingId : null;
        }

        $res = $this->client()->post('/crm/v3/objects/contacts', [
            'properties' => $properties,
        ]);

        return $this->idFromResponse($res, 'upsertContact(create)', $order);
    }

    /**
     * Create or update the deal for an order and associate it to the contact.
     *
     * - Deal stage is derived from the order status (STAGE_MAP).
     * - Amount is the order total.
     * - Reuses Order->hubspot_deal_id when set; otherwise creates and writes the id back.
     *
     * Returns the HubSpot deal id on success, or null on no-op / failure.
     */
    public function upsertDeal(Order $order): ?string
    {
        if (! $this->enabled()) {
            return $this->disabledNoop('upsertDeal', $order);
        }

        $properties = $this->dealProperties($order);
        $existingId = $this->clean($order->hubspot_deal_id);

        if ($existingId !== null) {
            $res = $this->client()->patch("/crm/v3/objects/deals/{$existingId}", [
                'properties' => $properties,
            ]);

            if (! $this->idFromResponse($res, 'upsertDeal(update)', $order)) {
                return null;
            }

            $this->associateDealToContact($order, $existingId);

            return $existingId;
        }

        $res = $this->client()->post('/crm/v3/objects/deals', [
            'properties' => $properties,
        ]);

        $dealId = $this->idFromResponse($res, 'upsertDeal(create)', $order);
        if ($dealId === null) {
            return null;
        }

        // Persist the new deal id so subsequent syncs update in place (idempotent).
        // Uses a quiet save to avoid re-triggering any model observers.
        $order->hubspot_deal_id = $dealId;
        $order->saveQuietly();

        $this->associateDealToContact($order, $dealId);

        return $dealId;
    }

    /**
     * Add a timeline note to the order's contact (and associate it to the deal when known).
     *
     * Mirrors ukv-quicknote.php: the note body is prefixed with the order ref. Never include
     * sensitive document/passport content here — the caller is responsible for the note text,
     * but this method does not pull any sensitive order fields.
     *
     * Returns true on success, false on no-op / failure.
     */
    public function addTimelineNote(Order $order, string $note): bool
    {
        if (! $this->enabled()) {
            $this->disabledNoop('addTimelineNote', $order);

            return false;
        }

        $note = trim($note);
        if ($note === '') {
            return false;
        }

        $ref = $this->clean($order->order_ref);
        $body = ($ref !== null ? "[{$ref}] " : '').$note;

        $res = $this->client()->post('/crm/v3/objects/notes', [
            'properties' => [
                'hs_note_body' => $body,
                // hs_timestamp must be epoch MILLISECONDS (matches the WP source).
                'hs_timestamp' => (string) (time() * 1000),
            ],
        ]);

        $noteId = $this->idFromResponse($res, 'addTimelineNote', $order);
        if ($noteId === null) {
            return false;
        }

        // Associate the note -> contact (timeline) and -> deal when we have one. Non-fatal.
        $contactId = $this->findContactIdByEmail((string) $this->clean($order->email));
        if ($contactId !== null) {
            $this->associate('note', $noteId, 'contact', $contactId);
        }

        $dealId = $this->clean($order->hubspot_deal_id);
        if ($dealId !== null) {
            $this->associate('note', $noteId, 'deal', $dealId);
        }

        return true;
    }

    // -----------------------------------------------------------------------------------
    // Property mappers (the ONLY place order fields are selected for export)
    // -----------------------------------------------------------------------------------

    /**
     * Contact properties — identity only, plus destination as a handy CRM dimension.
     * Name is split into first/last for HubSpot's standard fields.
     *
     * Field-mapping ASSUMPTIONS:
     *   - email/firstname/lastname/phone are HubSpot default contact properties.
     *   - `ukv_destination` is a CUSTOM contact property you must create in the portal
     *     (single-line text) for it to persist; HubSpot silently ignores unknown properties.
     *
     * @return array<string, string>
     */
    private function contactProperties(Order $order): array
    {
        [$first, $last] = $this->splitName($order->name);

        $props = array_filter([
            'email' => $this->clean($order->email),
            'firstname' => $first,
            'lastname' => $last,
            'phone' => $this->clean($order->phone ?? null),
            'ukv_destination' => $this->clean($order->destination_name),
        ], static fn ($v): bool => $v !== null && $v !== '');

        return $props;
    }

    /**
     * Deal properties — order summary only. NO passport/document/eligibility data.
     *
     * Field-mapping ASSUMPTIONS:
     *   - dealname/dealstage/amount/pipeline/closedate are HubSpot default deal properties.
     *   - `ukv_order_ref`, `ukv_destination`, `ukv_tier` are CUSTOM deal properties to create
     *     in the portal (single-line text) if you want them stored; unknown props are ignored.
     *   - pipeline is left to the portal default unless you set one. dealstage ids assume the
     *     DEFAULT sales pipeline (see STAGE_MAP note).
     *
     * @return array<string, string>
     */
    private function dealProperties(Order $order): array
    {
        $ref = $this->clean($order->order_ref);
        $destination = $this->clean($order->destination_name);

        $tier = $order->tier instanceof OrderTier
            ? $order->tier->value
            : $this->clean($order->tier);

        $status = $order->status instanceof OrderStatus
            ? $order->status->value
            : (string) $order->status;

        $dealName = trim(sprintf(
            'UK Visa%s%s',
            $ref !== null ? " — {$ref}" : '',
            $destination !== null ? " ({$destination})" : '',
        ));

        $props = [
            'dealname' => $dealName !== '' ? $dealName : 'UK Visa order',
            'dealstage' => self::STAGE_MAP[$status] ?? self::DEFAULT_STAGE,
        ];

        // Amount = order total (string, plain decimal — HubSpot expects a numeric string).
        $total = $order->total;
        if ($total !== null && $total !== '') {
            $props['amount'] = (string) $total;
        }

        // Optional custom props (only sent when present).
        if ($ref !== null) {
            $props['ukv_order_ref'] = $ref;
        }
        if ($destination !== null) {
            $props['ukv_destination'] = $destination;
        }
        if ($tier !== null && $tier !== '') {
            $props['ukv_tier'] = $tier;
        }

        // closedate (epoch ms) only once the order is actually closed.
        if ($order->status instanceof OrderStatus && $order->status->isClosed() && $order->closed_at !== null) {
            $props['closedate'] = (string) ($order->closed_at->getTimestamp() * 1000);
        }

        return $props;
    }

    // -----------------------------------------------------------------------------------
    // HubSpot helpers
    // -----------------------------------------------------------------------------------

    /**
     * Find a contact id by exact email via the v3 search API. Returns null if none / on error.
     */
    private function findContactIdByEmail(string $email): ?string
    {
        $email = $this->clean($email);
        if ($email === null) {
            return null;
        }

        $res = $this->client()->post('/crm/v3/objects/contacts/search', [
            'filterGroups' => [[
                'filters' => [[
                    'propertyName' => 'email',
                    'operator' => 'EQ',
                    'value' => $email,
                ]],
            ]],
            'properties' => ['email'],
            'limit' => 1,
        ]);

        if (! $res->successful()) {
            Log::warning('HubSpot contact search failed.', [
                'status' => $res->status(),
                'body' => $res->body(),
            ]);

            return null;
        }

        $id = $res->json('results.0.id');

        return $id !== null ? (string) $id : null;
    }

    /**
     * Associate the deal to the order's contact (by email lookup). Non-fatal.
     */
    private function associateDealToContact(Order $order, string $dealId): void
    {
        $email = $this->clean($order->email);
        if ($email === null) {
            return;
        }

        $contactId = $this->findContactIdByEmail($email);
        if ($contactId === null) {
            return;
        }

        $this->associate('deal', $dealId, 'contact', $contactId);
    }

    /**
     * Create a default v4 association between two objects. Non-fatal (logs on failure).
     */
    private function associate(string $fromType, string $fromId, string $toType, string $toId): void
    {
        $res = $this->client()->put(
            "/crm/v4/objects/{$fromType}/{$fromId}/associations/default/{$toType}/{$toId}"
        );

        if (! $res->successful()) {
            Log::warning('HubSpot association failed.', [
                'from' => "{$fromType}/{$fromId}",
                'to' => "{$toType}/{$toId}",
                'status' => $res->status(),
            ]);
        }
    }

    // -----------------------------------------------------------------------------------
    // Plumbing
    // -----------------------------------------------------------------------------------

    private function token(): string
    {
        return (string) config('services.hubspot.token', '');
    }

    /** True when a token is configured. When false, every public method is a no-op. */
    private function enabled(): bool
    {
        return trim($this->token()) !== '';
    }

    /** A configured, authenticated HTTP client for the HubSpot v3 API. */
    private function client(): PendingRequest
    {
        return Http::baseUrl(self::BASE_URL)
            ->withToken($this->token())
            ->acceptJson()
            ->asJson()
            ->timeout(self::TIMEOUT);
    }

    /**
     * Log + return null for the no-token case (keeps pre-launch safe).
     *
     * @return null always null (so callers can `return $this->disabledNoop(...)`)
     */
    private function disabledNoop(string $method, Order $order): null
    {
        Log::info("HubSpot {$method} no-op: no token configured.", [
            'order_ref' => $order->order_ref,
        ]);

        return null;
    }

    /**
     * Pull the object id from a write response, logging failures. Returns null on error.
     */
    private function idFromResponse(Response $res, string $context, Order $order): ?string
    {
        if (! $res->successful()) {
            Log::warning("HubSpot {$context} failed.", [
                'order_ref' => $order->order_ref,
                'status' => $res->status(),
                'body' => $res->body(),
            ]);

            return null;
        }

        $id = $res->json('id');

        return $id !== null ? (string) $id : null;
    }

    /**
     * Split a full name into [firstname, lastname]. Single-token names go to firstname.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function splitName(?string $name): array
    {
        $name = $this->clean($name);
        if ($name === null) {
            return [null, null];
        }

        $parts = preg_split('/\s+/', $name) ?: [];
        if (count($parts) === 1) {
            return [$parts[0], null];
        }

        $first = array_shift($parts);

        return [$first, implode(' ', $parts)];
    }

    private function clean(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $clean = trim((string) $value);

        return $clean === '' ? null : $clean;
    }
}
