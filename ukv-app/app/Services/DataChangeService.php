<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DataChangeProposal;
use App\Models\Destination;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI change-detection for destination facts (Module C, #138).
 *
 * For each destination that has official `sources` URLs, this service:
 *   1. Fetches the public source page(s) via Laravel Http. This is PUBLIC government / issuing-
 *      authority data — ZERO customer data leaves the building, so it is leak-safe by construction.
 *      (Contrast with AiService, which is owned by W3 and gated tightly because it can touch order
 *      data; this service is deliberately separate and never reads an Order.)
 *   2. Asks the Anthropic Messages API to compare the page text against the destination's STORED
 *      facts (fees, max stay, passport validity, etc.) and return a structured list of differences
 *      as {field, current, proposed, evidence}.
 *   3. Creates `open` DataChangeProposal rows for each flagged difference, de-duplicated against
 *      existing open proposals for the same destination+field.
 *
 * The model only FLAGS — a human Accepts/Dismisses in the Filament inbox. Nothing is auto-applied.
 *
 * GUARDS (a scheduled run must never throw):
 *   - No Anthropic key                 => logged no-op, returns empty stats.
 *   - A destination has no sources     => skipped.
 *   - A source fetch fails / non-2xx   => logged + that source skipped (never fatal).
 *   - Any model/transport error        => logged + that destination skipped (never fatal).
 *
 * Model + key come ONLY from config('services.anthropic.*'). The key is never logged.
 */
final class DataChangeService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';

    private const ANTHROPIC_VERSION = '2023-06-01';

    private const DEFAULT_MODEL = 'claude-opus-4-8';

    /** Anthropic call timeout (seconds). */
    private const TIMEOUT = 60;

    /** Public source-page fetch timeout (seconds). */
    private const FETCH_TIMEOUT = 20;

    /** Cap the fetched page text we send to the model (chars) — keeps the prompt bounded. */
    private const MAX_PAGE_CHARS = 12000;

    /**
     * The stored destination fields we ask the model to verify against the official source.
     * Map of column => human label used in the prompt and stored on the proposal.
     *
     * @var array<string, string>
     */
    private const CHECKED_FIELDS = [
        'visa_type' => 'Visa type (evisa/eta/visa-free/sticker)',
        'required_for_uk' => 'Visa required for UK entry (true/false)',
        'max_stay_days' => 'Maximum stay (days)',
        'govt_fee_gbp' => 'Government fee (GBP)',
        'passport_validity_months' => 'Passport validity required (months)',
    ];

    /**
     * Run change-detection across all destinations that have sources. Returns a small stats array
     * for the calling command to report. Never throws.
     *
     * @return array{destinations: int, checked: int, proposals_created: int, skipped: int}
     */
    public function run(): array
    {
        $stats = ['destinations' => 0, 'checked' => 0, 'proposals_created' => 0, 'skipped' => 0];

        if (! $this->enabled()) {
            Log::info('DataChangeService run no-op: no Anthropic key configured.');

            return $stats;
        }

        Destination::query()
            ->whereNotNull('sources')
            ->orderBy('id')
            ->chunkById(50, function ($chunk) use (&$stats): void {
                foreach ($chunk as $destination) {
                    /** @var Destination $destination */
                    $stats['destinations']++;
                    $created = $this->checkDestination($destination, $stats);
                    $stats['proposals_created'] += $created;
                }
            });

        return $stats;
    }

    /**
     * Check a single destination against its sources. Returns the number of proposals created.
     * Never throws — every failure is logged and that destination/source is skipped.
     *
     * @param  array{destinations: int, checked: int, proposals_created: int, skipped: int}  $stats
     */
    public function checkDestination(Destination $destination, array &$stats): int
    {
        $urls = $this->sourceUrls($destination);
        if ($urls === []) {
            $stats['skipped']++;

            return 0;
        }

        $created = 0;

        foreach ($urls as $url) {
            $pageText = $this->fetchSource($url, $destination);
            if ($pageText === null) {
                $stats['skipped']++;

                continue;
            }

            $stats['checked']++;

            $diffs = $this->detectDifferences($destination, $pageText, $url);
            if ($diffs === null) {
                // model/transport error already logged
                continue;
            }

            foreach ($diffs as $diff) {
                if ($this->createProposal($destination, $diff, $url)) {
                    $created++;
                }
            }
        }

        return $created;
    }

    // -----------------------------------------------------------------------------------
    // Source fetch (public gov data — leak-safe)
    // -----------------------------------------------------------------------------------

    /**
     * Extract the list of source URLs from a destination's `sources` JSON. Accepts either
     * a list of strings or a list of {label, url} maps. Returns [] when none/invalid.
     *
     * @return array<int, string>
     */
    private function sourceUrls(Destination $destination): array
    {
        $sources = $destination->sources;
        if (! is_array($sources)) {
            return [];
        }

        $urls = [];
        foreach ($sources as $source) {
            $url = is_array($source) ? ($source['url'] ?? null) : $source;
            $url = is_string($url) ? trim($url) : '';
            if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL) !== false) {
                $urls[] = $url;
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * Fetch a public source page and return its plain-text content (HTML stripped, bounded),
     * or null on any failure. Never throws — public data, no customer data involved.
     */
    private function fetchSource(string $url, Destination $destination): ?string
    {
        try {
            $res = Http::timeout(self::FETCH_TIMEOUT)
                ->withHeaders(['User-Agent' => 'UKVisaSupport-DataChangeBot/1.0'])
                ->get($url);
        } catch (\Throwable $e) {
            Log::warning('DataChangeService source fetch transport error.', [
                'destination_id' => $destination->getKey(),
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $res->successful()) {
            Log::warning('DataChangeService source fetch non-2xx.', [
                'destination_id' => $destination->getKey(),
                'url' => $url,
                'status' => $res->status(),
            ]);

            return null;
        }

        return $this->htmlToText($res->body());
    }

    /**
     * Reduce raw HTML to bounded plain text: drop script/style, strip tags, collapse whitespace.
     */
    private function htmlToText(string $html): string
    {
        $html = (string) preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', ' ', $html);
        $text = trim((string) preg_replace('/\s+/', ' ', strip_tags($html)));

        if (mb_strlen($text) > self::MAX_PAGE_CHARS) {
            $text = mb_substr($text, 0, self::MAX_PAGE_CHARS);
        }

        return $text;
    }

    // -----------------------------------------------------------------------------------
    // Model diff (Anthropic Messages API)
    // -----------------------------------------------------------------------------------

    /**
     * Ask the model to flag differences between the destination's stored facts and the source page.
     * Returns a list of diff maps {field, current, proposed, evidence}, or null on failure.
     *
     * @return array<int, array{field: string, current: ?string, proposed: ?string, evidence: ?string}>|null
     */
    private function detectDifferences(Destination $destination, string $pageText, string $url): ?array
    {
        $storedFacts = $this->renderStoredFacts($destination);
        $fieldList = implode(', ', array_keys(self::CHECKED_FIELDS));

        $system = 'You compare the STORED facts of an independent UK visa support service against an '
            .'official government / issuing-authority source page, and flag only material differences. '
            .'You are an advisory tool: you FLAG differences for a human to review — you never decide. '
            ."Only consider these fields: {$fieldList}. "
            .'Return ONLY a JSON object of the shape {"differences":[{"field":"<one of the field keys>",'
            .'"current":"<stored value as a string>","proposed":"<value supported by the page as a string>",'
            .'"evidence":"<short quote or paraphrase from the page that supports the proposed value>"}]}. '
            .'If the page does not clearly contradict a stored value, do NOT include that field. '
            .'If nothing differs, return {"differences":[]}. Do not invent figures the page does not state. '
            .'No prose, no markdown, no code fences — JSON only.';

        $user = "STORED FACTS for this destination:\n{$storedFacts}\n\n"
            ."OFFICIAL SOURCE PAGE TEXT (from {$url}):\n{$pageText}";

        $body = [
            'model' => $this->model(),
            'max_tokens' => 1500,
            'system' => $system,
            'messages' => [
                ['role' => 'user', 'content' => $user],
            ],
        ];

        try {
            $res = $this->client()->post(self::API_URL, $body);
        } catch (\Throwable $e) {
            Log::warning('DataChangeService model transport error.', [
                'destination_id' => $destination->getKey(),
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $res->successful()) {
            Log::warning('DataChangeService model non-2xx response.', [
                'destination_id' => $destination->getKey(),
                'url' => $url,
                'status' => $res->status(),
            ]);

            return null;
        }

        $text = $res->json('content.0.text');
        if (! is_string($text) || trim($text) === '') {
            Log::warning('DataChangeService model returned no usable text.', [
                'destination_id' => $destination->getKey(),
                'url' => $url,
            ]);

            return null;
        }

        return $this->parseDifferences($text, $destination, $url);
    }

    /**
     * Parse the model's JSON response into a normalised list of diff maps. Tolerant of a stray code
     * fence; returns [] on unparseable output (logged) so the run continues.
     *
     * @return array<int, array{field: string, current: ?string, proposed: ?string, evidence: ?string}>
     */
    private function parseDifferences(string $text, Destination $destination, string $url): array
    {
        // Strip an optional ```json ... ``` fence the model may add despite instructions.
        $clean = trim($text);
        if (str_starts_with($clean, '```')) {
            $clean = (string) preg_replace('/^```[a-zA-Z]*\s*|\s*```$/', '', $clean);
        }

        $decoded = json_decode($clean, true);
        if (! is_array($decoded) || ! isset($decoded['differences']) || ! is_array($decoded['differences'])) {
            Log::warning('DataChangeService could not parse model JSON.', [
                'destination_id' => $destination->getKey(),
                'url' => $url,
            ]);

            return [];
        }

        $out = [];
        foreach ($decoded['differences'] as $diff) {
            if (! is_array($diff)) {
                continue;
            }
            $field = is_string($diff['field'] ?? null) ? trim($diff['field']) : '';

            // Only accept fields we actually asked about — ignore anything the model invented.
            if (! array_key_exists($field, self::CHECKED_FIELDS)) {
                continue;
            }

            $out[] = [
                'field' => $field,
                'current' => $this->scalarToString($diff['current'] ?? null),
                'proposed' => $this->scalarToString($diff['proposed'] ?? null),
                'evidence' => $this->scalarToString($diff['evidence'] ?? null),
            ];
        }

        return $out;
    }

    // -----------------------------------------------------------------------------------
    // Proposal persistence (dedup against existing open)
    // -----------------------------------------------------------------------------------

    /**
     * Create an `open` proposal for a flagged diff, unless an open proposal already exists for the
     * same destination+field (dedup). Returns true when a row was created.
     *
     * @param  array{field: string, current: ?string, proposed: ?string, evidence: ?string}  $diff
     */
    private function createProposal(Destination $destination, array $diff, string $url): bool
    {
        $exists = DataChangeProposal::query()
            ->where('destination_id', $destination->getKey())
            ->where('field', $diff['field'])
            ->where('status', DataChangeProposal::STATUS_OPEN)
            ->exists();

        if ($exists) {
            return false;
        }

        // Snapshot the live stored value at detection time (authoritative — not the model's echo).
        $current = $this->scalarToString($destination->getAttribute($diff['field']));

        DataChangeProposal::create([
            'destination_id' => $destination->getKey(),
            'field' => $diff['field'],
            'current_value' => $current ?? $diff['current'],
            'proposed_value' => $diff['proposed'],
            'source_url' => $url,
            'model_summary' => $diff['evidence'],
            'status' => DataChangeProposal::STATUS_OPEN,
        ]);

        return true;
    }

    /**
     * Render the destination's stored facts as a "field: value" block for the prompt.
     */
    private function renderStoredFacts(Destination $destination): string
    {
        $lines = [];
        foreach (self::CHECKED_FIELDS as $column => $label) {
            $value = $this->scalarToString($destination->getAttribute($column));
            $lines[] = "{$column} ({$label}): ".($value ?? 'not set');
        }

        return implode("\n", $lines);
    }

    // -----------------------------------------------------------------------------------
    // Plumbing
    // -----------------------------------------------------------------------------------

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

    /** True when an Anthropic key is configured. When false, run() is a no-op. */
    private function enabled(): bool
    {
        return trim($this->key()) !== '';
    }

    /**
     * Coerce a scalar/enum attribute to a trimmed string, or null when empty/absent.
     */
    private function scalarToString(mixed $value): ?string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if ($value === null) {
            return null;
        }
        $clean = trim((string) $value);

        return $clean === '' ? null : $clean;
    }
}
