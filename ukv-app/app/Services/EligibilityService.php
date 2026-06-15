<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EligibilityLane;
use App\Enums\OrderStatus;
use App\Enums\ResidencyStatus;
use App\Enums\TripPurpose;
use App\Models\Order;

/**
 * Eligibility router + clearance gate.
 *
 * from: wp-content/mu-plugins/ukv-eligibility.php
 *   - ukv_eligibility_evaluate()  -> evaluate()
 *   - ukv_eligibility_apply()     -> apply()
 *   - ukv_order_is_cleared()      -> isCleared()
 *   - ukv_eligibility_gate_enforce() (the "past paid" guard) -> canAdvancePastPaid()
 *   - ukv_is_uk()                 -> isUk()
 *
 * Standard lane rule (confirmed against WP code, doc section 1.2):
 *   STANDARD iff isUK(nationality) AND isUK(residence_country)
 *            AND residency_status == citizen AND trip_purpose == tourist
 *            AND NOT prior_refusal AND NOT is_minor.
 *   Everything else => MANUAL_REVIEW.
 *
 * IMPORTANT: visa_entries and dual_nationality are captured on the order but, per the
 * doc's explicit flag, are NOT inputs to the routing decision. They are deliberately
 * absent from evaluate().
 */
final class EligibilityService
{
    /**
     * UK aliases for the is-UK check.
     * from: ukv_is_uk() match list (slug-normalised, case-insensitive).
     *
     * @var list<string>
     */
    private const UK_ALIASES = [
        'uk',
        'gb',
        'gbr',
        'united kingdom',
        'great britain',
        'britain',
        'england',
        'scotland',
        'wales',
        'northern ireland',
    ];

    /**
     * Decide the auto lane from the six router axes.
     *
     * Accepted axis keys (extra keys are ignored — notably visa_entries / dual_nationality):
     *   - nationality       (string)
     *   - residence_country (string)
     *   - residency_status  (string|ResidencyStatus)  default ''
     *   - trip_purpose      (string|TripPurpose)      default 'tourist'
     *   - prior_refusal     (bool-ish)                default false
     *   - is_minor          (bool-ish)                default false
     *
     * from: ukv_eligibility_evaluate()
     *
     * @param  array<string, mixed>  $axes
     */
    public function evaluate(array $axes): EligibilityLane
    {
        $nationality = (string) ($axes['nationality'] ?? '');
        $residence = (string) ($axes['residence_country'] ?? '');
        $status = $this->scalarOf($axes['residency_status'] ?? '');
        // Missing trip_purpose defaults to 'tourist' — matches WP `?? 'tourist'`.
        $purpose = $this->scalarOf($axes['trip_purpose'] ?? TripPurpose::Tourist->value);
        $refusal = $this->boolish($axes['prior_refusal'] ?? false);
        $minor = $this->boolish($axes['is_minor'] ?? false);

        $standard = $this->isUk($nationality)
            && $this->isUk($residence)
            && $status === ResidencyStatus::Citizen->value
            && $purpose === TripPurpose::Tourist->value
            && ! $refusal
            && ! $minor;

        return $standard ? EligibilityLane::Standard : EligibilityLane::ManualReview;
    }

    /**
     * Sanitise + store the intake axes on the order, then (re)compute the auto lane.
     *
     * Never overwrites an agent decision: if the current lane is already cleared or
     * referred, the lane is left untouched (only the axes are stored).
     *
     * from: ukv_eligibility_apply() — recompute only when current lane ∉ {cleared, referred}.
     *
     * @param  array<string, mixed>  $axes
     */
    public function apply(Order $order, array $axes): void
    {
        // --- Sanitise + store the captured axes (router + captured-only) ---
        if (array_key_exists('nationality', $axes)) {
            $order->nationality = $this->cleanString($axes['nationality']);
        }
        if (array_key_exists('residence_country', $axes)) {
            $order->residence_country = $this->cleanString($axes['residence_country']);
        }
        if (array_key_exists('residency_status', $axes)) {
            $order->residency_status = $this->residencyStatus($axes['residency_status']);
        }
        if (array_key_exists('trip_purpose', $axes)) {
            $order->trip_purpose = $this->tripPurpose($axes['trip_purpose']);
        }
        if (array_key_exists('prior_refusal', $axes)) {
            $order->prior_refusal = $this->boolish($axes['prior_refusal']);
        }
        if (array_key_exists('is_minor', $axes)) {
            $order->is_minor = $this->boolish($axes['is_minor']);
        }
        // Captured-only axes — stored but never routed on.
        if (array_key_exists('visa_entries', $axes)) {
            $order->visa_entries = $this->cleanString($axes['visa_entries']);
        }
        if (array_key_exists('dual_nationality', $axes)) {
            $order->dual_nationality = $this->cleanString($axes['dual_nationality']);
        }
        if (array_key_exists('insurance_required', $axes)) {
            $order->insurance_required = $this->boolish($axes['insurance_required']);
        }

        // --- Recompute the auto lane, but NEVER overwrite an agent decision ---
        $existing = $order->eligibility;
        if (! in_array($existing, [EligibilityLane::Cleared, EligibilityLane::Referred], true)) {
            $order->eligibility = $this->evaluate($this->routerAxesFrom($order, $axes));
        }
    }

    /**
     * Is the order cleared for the pipeline? Lane ∈ {standard, cleared}.
     *
     * manual_review and referred are NOT cleared.
     *
     * from: ukv_order_is_cleared()
     */
    public function isCleared(Order $order): bool
    {
        return $order->eligibility instanceof EligibilityLane
            && $order->eligibility->isCleared();
    }

    /**
     * Eligibility gate: may a non-cleared order advance to the attempted status?
     *
     * A cleared order (standard/cleared) is never blocked. A non-cleared order
     * (manual_review/referred) may sit at 'paid' (the entry stage) but cannot advance
     * past it.
     *
     * from: ukv_eligibility_gate_enforce() — "allow if cleared; allow if attempted==paid;
     * else block (revert to status_last, fallback 'paid')".
     *
     * @param  OrderStatus|string  $attempted  the status the order is trying to move to
     */
    public function canAdvancePastPaid(Order $order, OrderStatus|string $attempted): bool
    {
        if ($this->isCleared($order)) {
            return true; // standard & cleared are never blocked
        }

        $attemptedValue = $attempted instanceof OrderStatus ? $attempted->value : (string) $attempted;

        // 'paid' is the entry stage; a non-cleared order may rest there.
        return $attemptedValue === OrderStatus::Paid->value;
    }

    /**
     * Slug-normalise a country string and test membership of the UK alias list.
     *
     * from: ukv_is_uk()
     */
    public function isUk(string $country): bool
    {
        $normalised = strtolower(trim($country));

        return $normalised !== '' && in_array($normalised, self::UK_ALIASES, true);
    }

    /**
     * Build the six-axis router input, preferring the just-supplied axes and falling
     * back to the values now persisted on the order. Ensures evaluate() sees the
     * sanitised state regardless of which keys the caller passed.
     *
     * @param  array<string, mixed>  $axes
     * @return array<string, mixed>
     */
    private function routerAxesFrom(Order $order, array $axes): array
    {
        return [
            'nationality' => $axes['nationality'] ?? $order->nationality ?? '',
            'residence_country' => $axes['residence_country'] ?? $order->residence_country ?? '',
            'residency_status' => $axes['residency_status'] ?? $order->residency_status ?? '',
            'trip_purpose' => $axes['trip_purpose'] ?? $order->trip_purpose ?? TripPurpose::Tourist->value,
            'prior_refusal' => $axes['prior_refusal'] ?? $order->prior_refusal ?? false,
            'is_minor' => $axes['is_minor'] ?? $order->is_minor ?? false,
        ];
    }

    /** Coerce an enum or scalar to its string value for comparison. */
    private function scalarOf(mixed $value): string
    {
        if ($value instanceof ResidencyStatus || $value instanceof TripPurpose) {
            return $value->value;
        }

        return is_scalar($value) ? (string) $value : '';
    }

    /** WP `! empty()` semantics: '', '0', 0, false, null are all falsey. */
    private function boolish(mixed $value): bool
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        return ! empty($value);
    }

    private function cleanString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = trim((string) $value);

        return $clean === '' ? null : $clean;
    }

    private function residencyStatus(mixed $value): ?ResidencyStatus
    {
        if ($value instanceof ResidencyStatus) {
            return $value;
        }

        return ResidencyStatus::tryFrom(strtolower(trim((string) $value)));
    }

    private function tripPurpose(mixed $value): TripPurpose
    {
        if ($value instanceof TripPurpose) {
            return $value;
        }

        return TripPurpose::tryFrom(strtolower(trim((string) $value))) ?? TripPurpose::Tourist;
    }
}
