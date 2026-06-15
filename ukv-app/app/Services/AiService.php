<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BarrierStatus;
use App\Enums\OrderBlocker;
use App\Enums\OrderStatus;
use App\Enums\OrderTier;
use App\Models\Document;
use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    /**
     * MIME types we will send to the vision endpoint. Anthropic accepts jpeg/png/gif/webp image
     * blocks. HEIC and PDF are NOT image blocks the API understands here, so we never send them
     * (reviewDocumentImage no-ops for them) — that also keeps the leak surface to plain raster
     * images only.
     *
     * @var array<int, string>
     */
    private const VISION_MIMES = ['image/jpeg', 'image/png'];

    /** Vision payloads can be larger; give them a little more room than text calls. */
    private const VISION_TIMEOUT = 45;

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

    /**
     * VISION-based advisory review of a SINGLE uploaded document IMAGE (Phase-2 #99).
     *
     * This is the ONE method in the whole app where a document's image BYTES leave the building.
     * Because of that the leak gate here is deliberately tight:
     *
     *   WHAT IS SENT (and nothing else):
     *     - exactly ONE image block: the base64 of the single $document's stored file;
     *     - a GENERIC, case-agnostic instruction asking only for document-QUALITY signals.
     *
     *   WHAT IS NEVER SENT:
     *     - NO customer PII (no name, email, phone, passport number);
     *     - NO order reference, order id, status, destination, or any other order field;
     *     - NO filename (a client filename can itself be PII, e.g. "John-Smith-passport.jpg");
     *     - NO other document, and never more than one image per request;
     *     - NO disk path or storage URL.
     *   The model is told NOT to transcribe or extract any personal data — only to judge quality.
     *
     * Guards:
     *   - No Anthropic key  => logged no-op, returns null (safe pre-launch).
     *   - Non-image document (HEIC/PDF/unknown) => returns null (we only do raster vision here).
     *   - Missing/empty/unreadable file on disk => returns null (logged).
     *
     * ADVISORY ONLY: the returned string is a checklist for a human reviewer. It never changes the
     * order status, never approves/rejects, never emails anyone.
     *
     * @return string|null A short advisory note, or null (no key / not an image / file gone / AI failure).
     */
    public function reviewDocumentImage(Document $document): ?string
    {
        if (! $this->enabled()) {
            $this->logDoc('info', 'reviewDocumentImage no-op: no Anthropic key configured.', $document);

            return null;
        }

        $mime = $this->enumValue($document->mime);

        if ($mime === null || ! in_array($mime, self::VISION_MIMES, true)) {
            // Only plain raster images go to the vision endpoint. PDFs/HEIC are out of scope here.
            $this->logDoc('info', 'reviewDocumentImage skipped: document is not a reviewable image.', $document, [
                'mime' => $mime ?? 'unknown',
            ]);

            return null;
        }

        $bytes = $this->readDocumentBytes($document);

        if ($bytes === null || $bytes === '') {
            // Missing/empty/unreadable file — already logged in readDocumentBytes().
            return null;
        }

        // GENERIC instruction — contains NO order/customer specifics whatsoever.
        $system = 'You are a meticulous document-quality assistant for an independent UK visa support '
            .'service. You ADVISE a human reviewer who makes the final decision — you never approve or '
            .'reject anything and you never change any record. You are shown ONE document image and '
            .'NOTHING else about the case. Do NOT transcribe, extract, or repeat any personal data from '
            .'the image (no names, numbers, dates of birth, or passport/reference numbers) — comment only '
            .'on document QUALITY. Produce a short checklist-style note (one bullet per point) covering: '
            .'(1) legibility — is the whole document sharp, well-lit, and fully in frame, with no glare, '
            .'blur, shadow, or cropped edges? (2) type — does this look like the kind of document a visa '
            .'application expects (e.g. a passport bio-data page, a photo, or a supporting letter), and if '
            .'so which? (3) expiry/validity — if a passport, is the machine-readable zone (MRZ) or an '
            .'expiry date visible, and does the expiry appear to be in the future rather than obviously '
            .'past? State plainly when something is not visible rather than guessing. End with a one-line '
            .'advisory verdict such as "Looks usable" or "Ask the customer to re-upload". Be concise.';

        $userText = 'Please assess the quality of the single attached document image using the checklist. '
            .'Remember: do not read out any personal data; comment only on whether the image is usable.';

        return $this->callVision($system, $userText, $mime, $bytes, 500, 'reviewDocumentImage', $document);
    }

    /**
     * Draft a country-guide BODY (HTML prose) around injected ground-truth facts.
     *
     * LEAK SURFACE — destination/topic facts ONLY:
     *   The $facts array is a label => value map of PUBLIC destination data (fees, stay length,
     *   passport validity, processing time, document list, official source URLs, etc.) assembled
     *   by GuideContentService from the destinations / requirements engine. It contains NO customer
     *   data — no order, no name, no email, no passport number, no document bytes. This method does
     *   not receive an Order or a Document and so cannot leak one. As belt-and-braces, every fact
     *   value is PII-redacted before it leaves the building.
     *
     * FACTUALITY — the prompt is the first line of the no-invention defence:
     *   The model is told it may ONLY state figures (£-amounts, day-counts, dates) that appear in
     *   the injected facts, and must never invent or estimate a number, fee, timescale, or date that
     *   was not provided. GuideContentService additionally runs flagUnsourcedFacts() over the result
     *   and the publish gate blocks until a human verifies the facts — so this prompt is necessary
     *   but not sufficient, by design.
     *
     * Honesty rules mirror the rest of the class: independent service, NOT a government site;
     * "express" speeds OUR handling not the government decision; never guarantee approval.
     *
     * Guarded no-op: with no Anthropic key configured this returns '' (empty string) — never throws,
     * never partially drafts. Callers treat '' as "AI unavailable, leave the body for a human".
     *
     * @param  array<string, string>  $facts  Label => value map of destination ground-truth facts.
     * @param  string  $type  The GuideType value (e.g. "cost_fees") — steers the topic focus.
     * @return string  Draft HTML body, or '' when disabled / on any failure.
     */
    public function draftGuide(array $facts, string $type): string
    {
        if (! $this->enabled()) {
            Log::info('AiService draftGuide no-op: no Anthropic key configured.', ['guide_type' => $type]);

            return '';
        }

        // Redact defensively even though facts are already meant to be PII-free public data.
        $clean = [];
        foreach ($facts as $label => $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }
            $clean[(string) $label] = $this->redactPii($value);
        }

        $factsBlock = $clean === []
            ? '(No specific facts were supplied. Do NOT state any figure, fee, timescale, or date.)'
            : $this->renderSummary($clean);

        $system = 'You are a careful content writer for an independent UK visa support service, drafting '
            .'a single informational guide article for UK travellers. '
            .'CRITICAL FACTUALITY RULE: you may ONLY state a specific figure — any £-amount, number of '
            .'days, processing timescale, passport-validity period, or date — if that exact figure appears '
            .'in the FACTS block below. You must NEVER invent, estimate, round, infer, or "remember" any '
            .'fee, cost, timescale, day-count, or date that is not in the FACTS. If a fact is not provided, '
            .'write the prose without a number (e.g. "the government fee, shown at checkout") rather than '
            .'guessing one. '
            .'HONESTY RULES: we are an independent service, NOT a government website; an express or priority '
            .'option speeds up OUR handling and paperwork, it does NOT make the government decide faster and '
            .'never guarantees a faster or successful decision; NEVER guarantee or imply a guaranteed '
            .'approval or a specific outcome; entry rules depend on nationality and residence and change over '
            .'time, so tell the reader to confirm the current rule at the official source. '
            .'OUTPUT: return clean HTML body content only — use <h2>/<h3>, <p>, <ul>/<li>, <strong>. Do NOT '
            .'include <html>, <head>, <body>, a top-level <h1>, inline styles, scripts, or Markdown fences. '
            .'Write for the guide topic "'.$type.'". Be concise, accurate, and reassuring.';

        $user = "Draft the body of a UK-traveller guide on the topic \"{$type}\".\n\n"
            ."FACTS (the ONLY figures you may state — do not introduce any other number, fee, or date):\n"
            .$factsBlock
            ."\n\nWrite several short HTML sections around these facts. Where a relevant figure is not in "
            .'the FACTS, describe it qualitatively instead of inventing a value.';

        return $this->callText($system, $user, 1500, 'draftGuide', ['guide_type' => $type]) ?? '';
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

    /**
     * Order-agnostic sibling of call(): send a system + user prompt and return the text, or null.
     *
     * Used by draftGuide(), which operates on PUBLIC destination facts and has no Order/Document to
     * scope logging to. Same null-safe contract as call(): returns null on any non-2xx, transport
     * error, or missing field; never throws. The request body (the prompt) is NEVER logged.
     *
     * @param  array<string, scalar>  $logContext  Non-PII context for log lines (e.g. guide_type).
     */
    private function callText(string $system, string $user, int $maxTokens, string $context, array $logContext = []): ?string
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
            Log::warning("AiService {$context} transport error.", array_merge($logContext, [
                'error' => $e->getMessage(),
            ]));

            return null;
        }

        if (! $res->successful()) {
            Log::warning("AiService {$context} non-2xx response.", array_merge($logContext, [
                'status' => $res->status(),
            ]));

            return null;
        }

        $text = $res->json('content.0.text');

        if (! is_string($text) || trim($text) === '') {
            Log::warning("AiService {$context} returned no usable text.", $logContext);

            return null;
        }

        return trim($text);
    }

    /**
     * Send ONE image block + a generic instruction to the Anthropic Messages API and return the
     * advisory text, or null. This is the vision sibling of call().
     *
     * The request body is built HERE and contains ONLY: the model, the generic system prompt, the
     * generic user text, and a single base64 image block of the given MIME. No order/customer data
     * is in scope of this method — it receives only $mime + $bytes + the generic strings above.
     *
     * Null-safe by design: returns null on any non-2xx response, transport error, or missing field.
     * Never throws. The API key is read from config and attached as the x-api-key header only. The
     * request body (which contains the image bytes) is NEVER logged.
     */
    private function callVision(
        string $system,
        string $userText,
        string $mime,
        string $bytes,
        int $maxTokens,
        string $context,
        Document $document,
    ): ?string {
        $body = [
            'model' => $this->model(),
            'max_tokens' => $maxTokens,
            'system' => $system,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => $mime,
                                'data' => base64_encode($bytes),
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => $userText,
                        ],
                    ],
                ],
            ],
        ];

        try {
            $res = $this->client(self::VISION_TIMEOUT)->post(self::API_URL, $body);
        } catch (\Throwable $e) {
            // NEVER log the body (it contains the image bytes) or the key.
            $this->logDoc('warning', "AiService {$context} transport error.", $document, [
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $res->successful()) {
            $this->logDoc('warning', "AiService {$context} non-2xx response.", $document, [
                'status' => $res->status(),
            ]);

            return null;
        }

        $text = $res->json('content.0.text');

        if (! is_string($text) || trim($text) === '') {
            $this->logDoc('warning', "AiService {$context} returned no usable text.", $document);

            return null;
        }

        return trim($text);
    }

    /**
     * Read the stored bytes for a document from its (private) disk. Returns null + logs when the
     * disk/path is missing or the file cannot be read. Never throws.
     */
    private function readDocumentBytes(Document $document): ?string
    {
        $disk = (string) $document->disk;
        $path = (string) $document->path;

        if ($disk === '' || $path === '') {
            $this->logDoc('warning', 'reviewDocumentImage skipped: document has no stored file.', $document);

            return null;
        }

        try {
            $storage = Storage::disk($disk);

            if (! $storage->exists($path)) {
                $this->logDoc('warning', 'reviewDocumentImage skipped: stored file is missing.', $document);

                return null;
            }

            $bytes = $storage->get($path);
        } catch (\Throwable $e) {
            $this->logDoc('warning', 'reviewDocumentImage could not read the stored file.', $document, [
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (! is_string($bytes) || $bytes === '') {
            $this->logDoc('warning', 'reviewDocumentImage skipped: stored file is empty.', $document);

            return null;
        }

        return $bytes;
    }

    /**
     * Log a document-scoped line WITHOUT any PII or bytes. We log only the document id + order id —
     * never the filename, the path, or the file contents.
     *
     * @param  array<string, mixed>  $extra
     */
    private function logDoc(string $level, string $message, Document $document, array $extra = []): void
    {
        Log::{$level}($message, array_merge([
            'document_id' => $document->getKey(),
            'order_id' => $document->order_id,
        ], $extra));
    }

    /** A configured HTTP client for the Anthropic Messages API. */
    private function client(?int $timeout = null): PendingRequest
    {
        return Http::withHeaders([
            'x-api-key' => $this->key(),
            'anthropic-version' => self::ANTHROPIC_VERSION,
        ])
            ->acceptJson()
            ->asJson()
            ->timeout($timeout ?? self::TIMEOUT);
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
