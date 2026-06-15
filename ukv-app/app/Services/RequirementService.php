<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Destination;
use App\Models\DocumentRequirement;
use App\Models\Order;
use BackedEnum;
use Illuminate\Support\Carbon;

/**
 * Document-requirements engine (Approach B).
 *
 * Requirements are DATA (admin-editable `document_requirements` rows), not code. This
 * service loads the active rules, evaluates each rule's `conditions` against the subject
 * (an order, or a destination + an assumed context), and returns the tailored checklist.
 *
 * Matching semantics (per spec):
 *   - AND across condition keys; OR within an array value.
 *   - Empty/null conditions => the rule always matches.
 *   - A rule is EXCLUDED when a condition it relies on has an unknown source value
 *     (e.g. a string axis is null, or a computed value cannot be derived).
 *
 * The returned shape is intentionally flat + presentational:
 *   list<array{document_key:string,label:string,note:?string,category:string,mandatory:bool}>
 */
final class RequirementService
{
    /**
     * Personalised checklist for a concrete order.
     *
     * @return list<array{document_key:string,label:string,note:?string,category:string,mandatory:bool}>
     */
    public function for(Order $order): array
    {
        $ctx = $this->contextFromOrder($order);

        return $this->evaluate($ctx);
    }

    /**
     * Preview checklist for a destination + a context array of assumptions. No order needed.
     *
     * The caller's $ctx overrides the destination-derived defaults (slug, passport months).
     * Example $ctx: ['trip_purpose' => 'tourist', 'is_minor' => false].
     *
     * @param  array<string, mixed>  $ctx
     * @return list<array{document_key:string,label:string,note:?string,category:string,mandatory:bool}>
     */
    public function preview(Destination $destination, array $ctx = []): array
    {
        $base = [
            'destination_slug' => $destination->slug,
            'passport_validity_months' => $destination->passport_validity_months,
        ];

        return $this->evaluate(array_merge($base, $ctx));
    }

    /**
     * Load active rules, keep those whose conditions match, de-dupe by document_key, order.
     *
     * @param  array<string, mixed>  $ctx
     * @return list<array{document_key:string,label:string,note:?string,category:string,mandatory:bool}>
     */
    private function evaluate(array $ctx): array
    {
        $rules = DocumentRequirement::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        $byKey = [];

        foreach ($rules as $rule) {
            if (! $this->matchesRule((array) ($rule->conditions ?? []), $ctx)) {
                continue;
            }

            // De-dupe by document_key. First match wins, but a mandatory match always
            // upgrades a previously-recorded recommended item.
            if (isset($byKey[$rule->document_key])) {
                if ($rule->mandatory && ! $byKey[$rule->document_key]['mandatory']) {
                    $byKey[$rule->document_key]['mandatory'] = true;
                }

                continue;
            }

            $byKey[$rule->document_key] = [
                'document_key' => $rule->document_key,
                'label' => $rule->label,
                'note' => $rule->note,
                'category' => $rule->category,
                'mandatory' => $rule->mandatory,
            ];
        }

        return array_values($byKey);
    }

    /**
     * Does a single rule's conditions match the subject context?
     *
     * AND across keys. A rule that references an unknown/underivable value is excluded.
     *
     * @param  array<string, mixed>  $conditions
     * @param  array<string, mixed>  $ctx
     */
    private function matchesRule(array $conditions, array $ctx): bool
    {
        if ($conditions === []) {
            return true; // empty conditions => applies to all
        }

        foreach ($conditions as $key => $expected) {
            switch ($key) {
                case 'destinations':
                    if (! $this->matchStringIn($ctx['destination_slug'] ?? null, $expected)) {
                        return false;
                    }
                    break;

                case 'trip_purpose':
                case 'residency_status':
                case 'visa_entries':
                case 'employment_status':
                case 'accommodation_type':
                case 'funding_source':
                    if (! $this->matchStringIn($ctx[$key] ?? null, $expected)) {
                        return false;
                    }
                    break;

                case 'is_minor':
                case 'prior_refusal':
                case 'payer_is_applicant':
                    if (! $this->matchBool($ctx[$key] ?? null, $expected)) {
                        return false;
                    }
                    break;

                case 'min_stay_days':
                    $days = $this->computedStayDays($ctx);
                    if ($days === null || $days < (int) $expected) {
                        return false;
                    }
                    break;

                case 'max_stay_days':
                    $days = $this->computedStayDays($ctx);
                    if ($days === null || $days > (int) $expected) {
                        return false;
                    }
                    break;

                case 'passport_validity_short':
                    $short = $this->computedPassportShort($ctx);
                    if ($short === null || $short !== (bool) $expected) {
                        return false;
                    }
                    break;

                // Unknown condition keys are ignored (forward-compatible).
                default:
                    break;
            }
        }

        return true;
    }

    /**
     * String-membership match. The source value must be present (non-null/non-empty);
     * a null/empty source EXCLUDES the rule. $expected may be a scalar or a list.
     *
     * @param  mixed  $expected
     */
    private function matchStringIn(mixed $value, mixed $expected): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        $allowed = array_map(
            static fn ($v): string => (string) $v,
            is_array($expected) ? $expected : [$expected],
        );

        return in_array((string) $value, $allowed, true);
    }

    /**
     * Boolean equals. A null source EXCLUDES the rule (the value is unknown).
     *
     * @param  mixed  $expected
     */
    private function matchBool(mixed $value, mixed $expected): bool
    {
        if ($value === null) {
            return false;
        }

        return (bool) $value === (bool) $expected;
    }

    /**
     * Computed stay length = (return_date − travel_date) in whole days.
     * Returns null when either date is missing (rules using it are then excluded).
     *
     * @param  array<string, mixed>  $ctx
     */
    private function computedStayDays(array $ctx): ?int
    {
        $travel = $this->toCarbon($ctx['travel_date'] ?? null);
        $return = $this->toCarbon($ctx['return_date'] ?? null);

        if ($travel === null || $return === null) {
            return null;
        }

        return (int) $travel->startOfDay()->diffInDays($return->startOfDay(), false);
    }

    /**
     * Computed "passport validity short": true when passport_expiry falls BEFORE
     * (travel_date + destination.passport_validity_months months).
     * Returns null when any input is missing (rules using it are then excluded).
     *
     * @param  array<string, mixed>  $ctx
     */
    private function computedPassportShort(array $ctx): ?bool
    {
        $expiry = $this->toCarbon($ctx['passport_expiry'] ?? null);
        $travel = $this->toCarbon($ctx['travel_date'] ?? null);
        $months = $ctx['passport_validity_months'] ?? null;

        if ($expiry === null || $travel === null || $months === null) {
            return null;
        }

        $required = $travel->copy()->addMonths((int) $months);

        return $expiry->lessThan($required);
    }

    /**
     * Build the evaluation context from an order, reading every field defensively.
     * Enum-cast values (trip_purpose, residency_status) are coerced to their string value.
     *
     * @return array<string, mixed>
     */
    private function contextFromOrder(Order $order): array
    {
        $destination = $order->destination;

        $slug = $destination?->slug;
        $passportMonths = $destination?->passport_validity_months;

        // Fall back to the snapshot name when the relation is absent (the order may
        // store destination_name as the display name rather than a slug).
        if ($slug === null && $order->destination_name !== null && $order->destination_name !== '') {
            $slug = $order->destination_name;
        }

        return [
            'destination_slug' => $slug,
            'passport_validity_months' => $passportMonths,
            'trip_purpose' => $this->enumValue($order->trip_purpose),
            'residency_status' => $this->enumValue($order->residency_status),
            'visa_entries' => $order->visa_entries,
            'employment_status' => $order->employment_status,
            'accommodation_type' => $order->accommodation_type,
            'funding_source' => $order->funding_source,
            'is_minor' => $order->is_minor,
            'prior_refusal' => $order->prior_refusal,
            'payer_is_applicant' => $order->payer_is_applicant,
            'travel_date' => $order->travel_date,
            'return_date' => $order->return_date,
            'passport_expiry' => $order->passport_expiry,
        ];
    }

    /** Coerce a backed enum (or scalar) to its comparable string value, preserving null. */
    private function enumValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        return (string) $value;
    }

    /** Parse a date-ish value to Carbon, returning null on empty/invalid input. */
    private function toCarbon(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
