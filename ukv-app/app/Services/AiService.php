<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BarrierStatus;
use App\Enums\OrderBlocker;
use App\Enums\OrderStatus;
use App\Enums\OrderTier;
use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Optional Anthropic / Claude "AI Assist" layer for the UK visa app.
 *
 * Ports the leak-gated behaviour of the WP mu-plugins:
 *   - wp-content/mu-plugins/ukv-ai.php       (ukv_ai_next_best_action + the ukv_ai gateway)
 *   - wp-content/mu-plugins/ukv-doc-review.php (advisory document review)
 *
 * DESIGN / COMPLIANCE — read before changing:
 *   - Advisory only. Nothing this class returns is ever auto-applied: results are DRAFTS for a
 *     human operator. It NEVER changes order status, never approves/rejects, never emails a client.
 *   - LEAK GATE (the critical part). The methods build a NON-PII, order-summary prompt. They send
 *     ONLY the whitelisted fields enumerated in summaryFields()/docFields() below. They NEVER send
 *     passport numbers, document bytes/scans/images, full customer PII (email, full name beyond a
 *     first-name greeting is not even sent), government references, or anything else on the order.
 *     This mirrors the WP redaction: the WP version sent only destination/tier/status (+ redacted
 *     journey notes); here we send a slightly richer but still PII-free operational summary.
 *   - Honesty rules (from ukv_ai_brand_rules): we are an independent service, NOT a government site;
 *     "express" speeds our HANDLING not the government decision; never guarantee approval.
 *
 * KEY HANDLING:
 *   - The Anthropic key comes ONLY from config('services.anthropic.key'). Never hardcoded, never logged.
 *   - Empty key => every method is a logged no-op returning null. Safe to ship pre-launch.
 *
 * Model: config('services.anthropic.model'), defaulting to 'claude-opus-4-8'.
 *
 * All HTTP failures are logged (without secrets) and swallowed (return null) so the AI layer can
 * never break the order pipeline.
 */
final class AiService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';

    private const ANTHROPIC_VERSION = '2023-06-01';

    private const DEFAULT_MODEL = 'claude-opus-4-8';

    private const TIMEOUT = 30;

    // -----------------------------------------------------------------------------------
    // Public API
    // -----------------------------------------------------------------------------------

    /**
     * Propose ONE short, concrete next-best-action for an order.
     *
     * Builds a NON-PII operational summary (see summaryFields()) and asks Claude for a single
     * recommended action. Advisory only — a human acts on it.
     *
     * @return string|null A short recommendation, or null (no key / AI failure).
     */
    public function nextBestAction(Order $order): ?string
    {
        if (! $this->enabled()) {
            return $this->disabledNoop('nextBestAction', $order);
        }

        $summary = $this->renderSummary($this->summaryFields($order));

        $system = 'You are an operations adviser for an independent UK visa support service. '
            .'Given a non-personal summary of one case, suggest ONE short, concrete next-best-action the team '
            .'could take to improve the chance of a successful outcome and a happy client. '
            .'Be honest: we are an independent service, NOT a government website; an express or priority option '
            .'speeds up OUR handling and paperwork, it does NOT make the government decide faster and does not '
            .'guarantee a faster decision; NEVER guarantee or imply a guaranteed approval or a specific outcome. '
            .'Return a single short sentence, with no preamble, labels, or quotation marks.';

        return $this->call($system, $summary, 150, 'nextBestAction', $order);
    }

    /**
     * Advisory review of an order's documents — operating on METADATA ONLY.
     *
     * This version deliberately does NOT send file bytes, scans, or images (vision is a later task).
     * It sends only: required vs uploaded counts, and per-document filename + mime + size (see
     * docFields()). It returns a short checklist-style note for a human reviewer.
     *
     * ADVISORY ONLY: never changes order status, never approves/rejects.
     *
     * @return string|null A checklist-style note, or null (no key / AI failure).
     */
    public function reviewDocumentMeta(Order $order): ?string
    {
        if (! $this->enabled()) {
            return $this->disabledNoop('reviewDocumentMeta', $order);
        }

        $summary = $this->renderSummary($this->docFields($order));

        $system = 'You are a meticulous document-review assistant for an independent UK visa support service. '
            .'You ADVISE a human reviewer who makes the final decision — you never approve or reject anything, '
            .'and you never change the order status. '
            .'You are given ONLY document METADATA (filenames, MIME types, sizes, and required-vs-uploaded counts) '
            .'— you do NOT have the file contents or images in this version, so do not claim to have read them. '
            .'Produce a short checklist-style note (one bullet per point) flagging likely gaps a human should '
            .'verify: e.g. missing required documents, unexpected/unsupported file types, suspiciously small files, '
            .'or duplicates. Be concise and do not invent document contents you cannot see.';

        return $this->call($system, $summary, 400, 'reviewDocumentMeta', $order);
    }

    // -----------------------------------------------------------------------------------
    // Prompt builders — the ONLY place order fields are selected for export (the leak gate)
    // -----------------------------------------------------------------------------------

    /**
     * The SAFE order-summary fields for nextBestAction.
     *
     * WHITELIST ONLY. No passport number, no email, no document bytes, no government reference,
     * no eligibility intake axes, no free-text customer PII. Everything here is operational state.
     *
     * @return array<string, string>
     */
    private function summaryFields(Order $order): array
    {
        $status = $this->enumValue($order->status);
        $blocker = $this->enumValue($order->blocker);
        $tier = $this->enumValue($order->tier);

        $lane = $this->enumValue($order->eligibility);

        $fields = [
            'Destination' => $this->cleanDestination($order->destination_name),
            'Tier' => $tier !== null ? $tier : 'standard',
            'Status' => $status !== null ? $status : 'unknown',
            'Stage age' => $this->stageAge($order),
            'Eligibility lane' => $lane !== null ? $lane : 'unknown',
            'Active blocker' => ($blocker !== null && $blocker !== OrderBlocker::None->value) ? $blocker : 'none',
            'Outstanding documents' => (string) $this->outstandingDocs($order),
            'Open barriers' => (string) $this->openBarrierCount($order),
        ];

        // Open-barrier titles are staff-authored category labels (e.g. "Passport expiring soon"),
        // not customer PII — but redact defensively before they leave the building.
        $titles = $this->openBarrierTitles($order);
        if ($titles !== []) {
            $fields['Barrier notes'] = implode('; ', array_map(fn ($t): string => $this->redactPii($t), $titles));
        }

        // Travel date is an operational date (no name attached) — useful for urgency.
        if ($order->travel_date instanceof Carbon) {
            $fields['Travel date'] = $order->travel_date->toDateString();
        }

        return array_filter($fields, static fn ($v): bool => $v !== null && $v !== '');
    }

    /**
     * The SAFE document METADATA fields for reviewDocumentMeta.
     *
     * Per-document we send ONLY: original filename, MIME type, and size in bytes. We do NOT send
     * disk paths, storage URLs, or any file content. Purged documents are excluded.
     *
     * @return array<string, string>
     */
    private function docFields(Order $order): array
    {
        $required = (int) ($order->required_docs_count ?? 0);

        $documents = $order->documents
            ->filter(fn ($doc): bool => $doc->purged_at === null)
            ->values();

        $fields = [
            'Destination' => $this->cleanDestination($order->destination_name),
            'Status' => $this->enumValue($order->status) ?? 'unknown',
            'Required documents' => (string) $required,
            'Uploaded documents' => (string) $documents->count(),
        ];

        if ($documents->isEmpty()) {
            $fields['Documents'] = 'No documents are attached to this order yet.';

            return array_filter($fields, static fn ($v): bool => $v !== null && $v !== '');
        }

        $lines = [];
        foreach ($documents as $i => $doc) {
            $name = $this->redactPii((string) ($doc->original_name ?? 'unnamed'));
            $mime = $this->enumValue($doc->mime) ?? 'unknown';
            $size = $doc->size_bytes !== null ? (string) $doc->size_bytes.' bytes' : 'unknown size';
            $lines[] = sprintf('  %d. %s — %s, %s', $i + 1, $name, $mime, $size);
        }

        $fields['Documents'] = "\n".implode("\n", $lines);

        return array_filter($fields, static fn ($v): bool => $v !== null && $v !== '');
    }

    /**
     * Turn a label => value map into a plain "Label: value" block (the user message body).
     *
     * @param  array<string, string>  $fields
     */
    private function renderSummary(array $fields): string
    {
        $lines = [];
        foreach ($fields as $label => $value) {
            $lines[] = $label.': '.$value;
        }

        return implode("\n", $lines);
    }

    // -----------------------------------------------------------------------------------
    // Order-state helpers (derived, no PII)
    // -----------------------------------------------------------------------------------

    /**
     * Count of outstanding documents = required minus currently-uploaded (not purged). Never negative.
     */
    private function outstandingDocs(Order $order): int
    {
        $required = (int) ($order->required_docs_count ?? 0);

        $uploaded = $order->documents
            ->filter(fn ($doc): bool => $doc->purged_at === null)
            ->count();

        return max(0, $required - $uploaded);
    }

    /**
     * Number of still-open barriers on the order.
     */
    private function openBarrierCount(Order $order): int
    {
        return $order->barriers
            ->filter(fn ($b): bool => $this->enumValue($b->status) === BarrierStatus::Open->value)
            ->count();
    }

    /**
     * Titles of still-open barriers (staff category labels, redacted defensively).
     *
     * @return array<int, string>
     */
    private function openBarrierTitles(Order $order): array
    {
        return $order->barriers
            ->filter(fn ($b): bool => $this->enumValue($b->status) === BarrierStatus::Open->value)
            ->map(fn ($b): string => trim((string) $b->title))
            ->filter(fn (string $t): bool => $t !== '')
            ->values()
            ->all();
    }

    /**
     * Human-readable age in the current stage, derived from status_last (falls back to updated_at).
     * No PII — just a duration string for urgency context.
     */
    private function stageAge(Order $order): string
    {
        $since = $order->status_last ?? $order->updated_at;

        if (! $since instanceof Carbon) {
            return 'unknown';
        }

        return $since->diffForHumans(Carbon::now(), ['parts' => 1, 'syntax' => Carbon::DIFF_ABSOLUTE]);
    }

    // -----------------------------------------------------------------------------------
    // HTTP plumbing (Anthropic Messages API)
    // -----------------------------------------------------------------------------------

    /**
     * Send a system + user prompt to the Anthropic Messages API and return the text, or null.
     *
     * Null-safe by design: returns null on any non-2xx response, transport error, or missing field.
     * Never throws. The API key is read from config and attached as the x-api-key header only.
     */
    private function call(string $system, string $user, int $maxTokens, string $context, Order $order): ?string
    {
        $body = [
            'model' => $this->model(),
            'max_tokens' => $maxTokens,
            'system' => $system,
            'messages' => [
                ['role' => 'user', 'content' => $user],
            ],
        ];

        try {
            $res = $this->client()->post(self::API_URL, $body);
        } catch (\Throwable $e) {
            // Do NOT log the request body (it contains the order summary) or the key.
            Log::warning("AiService {$context} transport error.", [
                'order_ref' => $order->order_ref,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $res->successful()) {
            Log::warning("AiService {$context} non-2xx response.", [
                'order_ref' => $order->order_ref,
                'status' => $res->status(),
            ]);

            return null;
        }

        $text = $res->json('content.0.text');

        if (! is_string($text) || trim($text) === '') {
            Log::warning("AiService {$context} returned no usable text.", [
                'order_ref' => $order->order_ref,
            ]);

            return null;
        }

        return trim($text);
    }

    /** A configured HTTP client for the Anthropic Messages API. */
    private function client(): PendingRequest
    {
        return Http::withHeaders([
            'x-api-key' => $this->key(),
            'anthropic-version' => self::ANTHROPIC_VERSION,
        ])
            ->acceptJson()
            ->asJson()
            ->timeout(self::TIMEOUT);
    }

    private function key(): string
    {
        return (string) config('services.anthropic.key', '');
    }

    private function model(): string
    {
        $model = trim((string) config('services.anthropic.model', ''));

        return $model !== '' ? $model : self::DEFAULT_MODEL;
    }

    /** True when an Anthropic key is configured. When false, every public method is a no-op. */
    private function enabled(): bool
    {
        return trim($this->key()) !== '';
    }

    /**
     * Log + return null for the no-key case (keeps pre-launch safe).
     */
    private function disabledNoop(string $method, Order $order): null
    {
        Log::info("AiService {$method} no-op: no Anthropic key configured.", [
            'order_ref' => $order->order_ref,
        ]);

        return null;
    }

    // -----------------------------------------------------------------------------------
    // Small utilities
    // -----------------------------------------------------------------------------------

    /**
     * Resolve a backed-enum-or-scalar to its string value, or null.
     */
    private function enumValue(mixed $value): ?string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }
        if ($value === null) {
            return null;
        }
        $clean = trim((string) $value);

        return $clean === '' ? null : $clean;
    }

    private function cleanDestination(?string $name): string
    {
        $name = trim((string) $name);

        return $name !== '' ? $name : 'unknown destination';
    }

    /**
     * Defence-in-depth PII redaction, mirroring ukv_redact_pii from the WP source.
     * Strips emails, phone numbers, and long digit runs (passport-number-like) from any free text
     * before it leaves to the external API. Belt-and-braces — the whitelist already excludes PII.
     */
    private function redactPii(string $text): string
    {
        // Emails.
        $text = (string) preg_replace('/[\w.+-]+@[\w-]+\.[\w.-]+/', '[redacted-email]', $text);
        // Phone-number-like sequences (7+ digits, optional +, spaces, dashes, parens).
        $text = (string) preg_replace('/\+?[\d][\d\s().-]{6,}\d/', '[redacted-number]', $text);
        // Standalone long digit/alphanumeric runs (passport-/reference-like, 6+ chars).
        $text = (string) preg_replace('/\b[A-Z0-9]{6,}\b/i', '[redacted-ref]', $text);

        return trim($text);
    }
}
