<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\GuideType;
use App\Models\Destination;
use App\Models\Guide;
use Illuminate\Support\Str;

/**
 * Drafts country-guide bodies from STRUCTURED destination facts (factuality layer, spec §"factuality").
 *
 * DESIGN / COMPLIANCE — read before changing:
 *   - Facts come from structured data (the destinations table), NEVER AI invention. This service
 *     assembles a ground-truth fact set from a Destination and hands it to AiService::draftGuide(),
 *     which drafts prose AROUND those facts and is forbidden from stating any unprovided figure.
 *   - LEAK-SAFE: the fact set built here contains DESTINATION facts ONLY — public catalogue data
 *     (fees, stay length, passport validity, document list, official source). It contains NO customer
 *     data: no Order, no name, email, passport number, or document bytes ever enters this path.
 *   - ADVISORY / DRAFT ONLY: a drafted guide is always status=draft. This service never publishes —
 *     the GuideResource publish gate (human "facts verified" tick) is the only path to published.
 *   - NO-INVENTION VALIDATOR: flagUnsourcedFacts() scans a body for £-amounts / day-counts / dates
 *     that are NOT present in the injected fact set, so a reviewer sees any number the model produced
 *     that we cannot trace to ground truth.
 */
final class GuideContentService
{
    public function __construct(private readonly AiService $ai) {}

    /**
     * Draft (or re-draft) a guide body for one destination × guide type.
     *
     * Assembles the destination fact set, asks AiService to draft HTML prose around it, and writes the
     * result onto a DRAFT guide row (creating it if missing). Returns the Guide with its body + status
     * set. When AI is unavailable (no key / failure) the body is left untouched and status stays draft —
     * the operator can still write the body by hand.
     *
     * @return array{guide: Guide, body: string, facts: array<string, string>, flags: list<string>, drafted: bool}
     */
    public function draftFor(Destination $destination, GuideType $type): array
    {
        $facts = $this->factsFor($destination, $type);

        $guide = Guide::query()->firstOrNew([
            'destination_id' => $destination->id,
            'guide_type' => $type->value,
        ]);

        // Sensible defaults for a freshly created shell; never overwrite operator-edited identity.
        if (! $guide->exists) {
            $guide->title = $destination->name.' — '.$type->label();
            $guide->slug = $this->slugFor($destination, $type);
            $guide->excerpt = $type->label().' for travelling to '.$destination->name.'.';
            $guide->sort_order = 0;
        }

        $body = $this->ai->draftGuide($facts, $type->value);
        $drafted = $body !== '';

        if ($drafted) {
            $guide->body = $body;
        }

        // A drafted guide is ALWAYS a draft — publishing is a separate, human-gated action.
        $guide->status = 'draft';
        $guide->save();

        return [
            'guide' => $guide,
            'body' => $body,
            'facts' => $facts,
            'flags' => $drafted ? $this->flagUnsourcedFacts($body, $facts) : [],
            'drafted' => $drafted,
        ];
    }

    /**
     * Assemble the ground-truth fact set for one destination × type.
     *
     * DESTINATION FACTS ONLY — public catalogue fields. No customer/order data. Only non-empty facts
     * are returned so the model is never handed a blank "Fee: " line it might be tempted to fill in.
     *
     * @return array<string, string>
     */
    public function factsFor(Destination $destination, GuideType $type): array
    {
        $facts = [
            'Destination' => (string) $destination->name,
            'Visa type' => $this->str($destination->visa_type),
            'UK visa required' => $destination->required_for_uk ? 'Yes' : 'No',
        ];

        if ($destination->govt_fee_gbp !== null) {
            $facts['Government fee (GBP)'] = '£'.number_format((float) $destination->govt_fee_gbp, 2);
        }
        if ($destination->tier_standard_gbp !== null) {
            $facts['Our standard service fee (GBP)'] = '£'.number_format((float) $destination->tier_standard_gbp, 2);
        }
        if ($destination->tier_express_gbp !== null) {
            $facts['Our express service fee (GBP)'] = '£'.number_format((float) $destination->tier_express_gbp, 2);
        }
        if ($destination->tier_premium_gbp !== null) {
            $facts['Our premium service fee (GBP)'] = '£'.number_format((float) $destination->tier_premium_gbp, 2);
        }
        if ($destination->max_stay_days !== null) {
            $facts['Maximum stay (days)'] = (string) ((int) $destination->max_stay_days).' days';
        }
        if ($destination->passport_validity_months !== null) {
            $facts['Passport validity required (months)'] = (string) ((int) $destination->passport_validity_months).' months';
        }

        $docs = $destination->required_docs;
        if (is_array($docs) && $docs !== []) {
            $facts['Required documents'] = implode(', ', array_map(static fn ($d): string => trim((string) $d), $docs));
        }

        if ($this->str($destination->idp_permit_type) !== '') {
            $facts['IDP permit convention'] = $this->str($destination->idp_permit_type);
        }

        return array_filter($facts, static fn (string $v): bool => $v !== '');
    }

    /**
     * NO-INVENTION VALIDATOR (spec §"factuality").
     *
     * Scan a guide body for any £-amount, day-count, or date that does NOT appear in the injected
     * fact set, and return a human-readable flag per unsourced figure. A reviewer uses this to catch
     * numbers the model produced that we cannot trace to ground truth. Empty list = nothing unsourced.
     *
     * Matching is deliberately lenient on the FACT side (a figure counts as sourced if its normalised
     * numeric token appears anywhere in the facts) and strict on the BODY side (every numeric token in
     * the body must be accounted for) — false positives are cheap (reviewer dismisses) but a missed
     * invented number is expensive.
     *
     * @param  array<string, string>  $facts  The exact fact set handed to the model.
     * @return list<string>  One message per unsourced figure found in $body.
     */
    public function flagUnsourcedFacts(string $body, array $facts): array
    {
        $text = $this->plainText($body);
        $haystack = $this->numericTokens(implode(' ', $facts));

        $flags = [];

        // 1. £-amounts, e.g. £35, £35.00, £1,250.50.
        if (preg_match_all('/£\s?\d[\d,]*(?:\.\d+)?/u', $text, $m)) {
            foreach ($m[0] as $hit) {
                $token = $this->normaliseNumber($hit);
                if ($token !== '' && ! in_array($token, $haystack, true)) {
                    $flags[] = "Unsourced amount in body not present in facts: \"{$this->tidy($hit)}\"";
                }
            }
        }

        // 2. Day/month/week counts, e.g. "10 days", "3 working days", "6 months", "2 weeks".
        if (preg_match_all('/\b(\d[\d,]*)\s*(?:working\s+|business\s+|calendar\s+)?(day|days|week|weeks|month|months|hour|hours|year|years)\b/iu', $text, $m, PREG_SET_ORDER)) {
            foreach ($m as $set) {
                $token = $this->normaliseNumber($set[1]);
                if ($token !== '' && ! in_array($token, $haystack, true)) {
                    $flags[] = 'Unsourced duration in body not present in facts: "'.$this->tidy($set[0]).'"';
                }
            }
        }

        // 3. Calendar dates: ISO (2026-06-16), DD/MM/YYYY, and "12 January 2026" style.
        $datePatterns = [
            '/\b\d{4}-\d{2}-\d{2}\b/u',
            '#\b\d{1,2}/\d{1,2}/\d{2,4}\b#u',
            '/\b\d{1,2}\s+(?:January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{4}\b/iu',
            '/\b(?:January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{4}\b/iu',
        ];
        $factsRaw = implode(' ', $facts);
        foreach ($datePatterns as $pattern) {
            if (preg_match_all($pattern, $text, $m)) {
                foreach ($m[0] as $hit) {
                    if (! str_contains($factsRaw, $hit)) {
                        $flags[] = 'Unsourced date in body not present in facts: "'.$this->tidy($hit).'"';
                    }
                }
            }
        }

        return array_values(array_unique($flags));
    }

    /**
     * Build the canonical guide slug for a destination × type, e.g. "turkey-cost".
     */
    public function slugFor(Destination $destination, GuideType $type): string
    {
        return Str::slug($destination->slug.'-'.$type->topicSlug());
    }

    /**
     * Normalised numeric tokens appearing anywhere in a string (digits only, commas/decimals stripped).
     * Used as the "sourced" set: a body figure is sourced if its numeric token appears here.
     *
     * @return list<string>
     */
    private function numericTokens(string $text): array
    {
        if (! preg_match_all('/\d[\d,]*(?:\.\d+)?/u', $text, $m)) {
            return [];
        }

        $tokens = array_map(fn (string $n): string => $this->normaliseNumber($n), $m[0]);

        return array_values(array_unique(array_filter($tokens, static fn (string $t): bool => $t !== '')));
    }

    /**
     * Reduce a numeric string to a bare comparable token: drop currency, separators, trailing ".00".
     */
    private function normaliseNumber(string $value): string
    {
        $value = preg_replace('/[^\d.]/u', '', $value) ?? '';
        if ($value === '') {
            return '';
        }
        // Strip a trailing zero-decimal so "£35.00" matches "35".
        if (str_contains($value, '.')) {
            $value = rtrim(rtrim($value, '0'), '.');
        }

        return $value === '' ? '0' : $value;
    }

    /** Strip HTML tags + collapse whitespace so regex scanning sees plain text. */
    private function plainText(string $html): string
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5);

        return (string) preg_replace('/\s+/u', ' ', $text);
    }

    private function tidy(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }

    private function str(mixed $value): string
    {
        return trim((string) ($value ?? ''));
    }
}
