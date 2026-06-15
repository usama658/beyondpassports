<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderTier;
use App\Enums\QuoteStatus;
use App\Models\Destination;
use App\Models\Order;
use App\Models\Quote;
use Illuminate\Support\Carbon;

/**
 * Pricing for the fixed-tier (standard self-serve) lane + bespoke-quote (manual-review) lane,
 * plus the passport-validity rule.
 *
 * from:
 *   - ukv-hubspot.php (charge hook) + per-destination Pods fields (ukv-forminator-glue.php)
 *       -> tiers()        [fixed tiers, PER-DESTINATION]
 *   - ukv-quote.php (ukv_set_quote + ukv_quote_send)
 *       -> bespokeQuote()
 *   - ukv-barriers.php Rule 2 (value seeded by ukv-passport-validity.php)
 *       -> passportValidityOk()
 */
final class PricingService
{
    /**
     * Placeholder Stripe Payment Link.
     *
     * from: ukv-quote.php UKV_QUOTE_PLACEHOLDER_LINK ('https://buy.stripe.com/PLACEHOLDER').
     *
     * TODO(stripe): replace with a real per-order Stripe Payment Link created via the live
     * Stripe Payment Links API for `amount`, and store the returned URL on the Quote. Do NOT
     * invent a live URL here. The `paid` status is set when payment confirms (webhook not
     * wired in the WP source).
     */
    public const QUOTE_PLACEHOLDER_LINK = 'https://buy.stripe.com/PLACEHOLDER';

    /**
     * Default passport-validity requirement (months) when a destination has no value.
     *
     * from: ukv-passport-validity.php seeds passport_validity_months = 6.
     */
    public const DEFAULT_PASSPORT_VALIDITY_MONTHS = 6;

    /**
     * Seconds per "month" used by the WP barrier rule (30.44-day average), NOT calendar months.
     *
     * from: ukv-barriers.php Rule 2 — `$req_months * 2629800`.
     */
    private const SECONDS_PER_MONTH = 2629800;

    /**
     * The three fixed tiers for a destination, priced from the destination's OWN tier fields.
     *
     * Tier NAME is taken directly from the field it came from (standard/express/premium) — we
     * deliberately do NOT reverse-map a service fee through the fragile hardcoded 9-number
     * table the WP code used (doc flags that as a bug). Each tier total = service fee + the
     * destination's government fee.
     *
     * from: per-destination Pods tier_standard_gbp / tier_express_gbp / tier_premium_gbp
     *       + govt_fee_gbp; total = tierP + govt (ukv-hubspot.php).
     *
     * @return array<string, array{tier: OrderTier, service_fee: float, govt_fee: float, total: float}>
     *         keyed by tier value (standard|express|premium)
     */
    public function tiers(Destination $destination): array
    {
        $govt = (float) ($destination->govt_fee_gbp ?? 0.0);

        $map = [
            OrderTier::Standard->value => $destination->tier_standard_gbp,
            OrderTier::Express->value => $destination->tier_express_gbp,
            OrderTier::Premium->value => $destination->tier_premium_gbp,
        ];

        $tiers = [];
        foreach ($map as $key => $serviceFeeRaw) {
            $serviceFee = (float) ($serviceFeeRaw ?? 0.0);
            $tiers[$key] = [
                'tier' => OrderTier::from($key),
                'service_fee' => $serviceFee,
                'govt_fee' => $govt,
                'total' => $serviceFee + $govt,
            ];
        }

        return $tiers;
    }

    /**
     * Bespoke-quote path (manual_review / cleared lane): create or update the order's quote
     * and mark it sent.
     *
     * Behaviour (composes ukv_set_quote + ukv_quote_send):
     *   - amount must be > 0, else throw (mirrors ukv_quote_send returning false on amount<=0).
     *   - stores the amount; status none -> sent; stamps sent_at; writes the placeholder link.
     *   - status is only promoted to `sent` from `none` (a `paid` quote is never reopened).
     *
     * from: ukv-quote.php ukv_set_quote() (status none only-if-unset) + ukv_quote_send().
     */
    public function bespokeQuote(Order $order, float $amount): Quote
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Bespoke quote amount must be greater than zero.');
        }

        /** @var Quote $quote */
        $quote = $order->quotes()->firstOrNew([]);

        $quote->order_id = $order->getKey();
        $quote->amount = $amount;

        // ukv_quote_send: a paid quote stays paid; otherwise it becomes sent.
        if ($quote->status !== QuoteStatus::Paid) {
            $quote->status = QuoteStatus::Sent;
            $quote->sent_at = Carbon::now();
            $quote->payment_link = self::QUOTE_PLACEHOLDER_LINK; // TODO(stripe): real Payment Link
        }

        $quote->save();

        return $quote;
    }

    /**
     * Passport-validity check (doc section 5 / barriers Rule 2).
     *
     * Requires the passport to stay valid for N months BEYOND the travel date, where N comes
     * from the destination's passport_validity_months (default 6). A "month" is the WP
     * 30.44-day average (2,629,800 s), not a calendar month, for exact parity with the source.
     *
     * Null-safe / silent-no-op behaviour: the WP rule fires only when expiry, requirement AND
     * travel date are all present; a missing data point raises NO barrier. We mirror that here
     * by returning TRUE ("nothing to flag / cannot evaluate") when any of the three is absent.
     * This is the documented choice: missing inputs => not a failure, just unevaluable.
     *
     * @return bool true if validity is OK or cannot be evaluated; false only if it is
     *              definitively insufficient.
     */
    public function passportValidityOk(Order $order): bool
    {
        $expiry = $order->passport_expiry;   // Carbon|null (date cast)
        $travel = $order->travel_date;       // Carbon|null (date cast)

        if ($expiry === null || $travel === null) {
            return true; // cannot evaluate -> no-op (matches WP: no barrier)
        }

        $reqMonths = $this->requiredValidityMonths($order);
        if ($reqMonths <= 0) {
            return true; // no requirement configured -> no-op
        }

        // need = travel_date + req_months * 30.44-day average (epoch seconds).
        // passport_expiry and travel_date are DATE columns, so compare at day
        // granularity: the boundary date itself counts as valid (the sub-day
        // remainder of the 30.44-day average must not fail an on-boundary date).
        $need = $travel->getTimestamp() + ($reqMonths * self::SECONDS_PER_MONTH);
        $needDate = \Carbon\Carbon::createFromTimestamp($need)->startOfDay();

        // Barrier fires when expiry < need; OK is the inverse.
        return $expiry->copy()->startOfDay()->greaterThanOrEqualTo($needDate);
    }

    /**
     * Resolve the required validity months for an order: destination value, else default 6.
     *
     * from: ukv_dest_value($slug,'passport_validity_months') with the seeded-6 default.
     */
    private function requiredValidityMonths(Order $order): int
    {
        $months = $order->destination?->passport_validity_months;

        if ($months === null || (int) $months <= 0) {
            return self::DEFAULT_PASSPORT_VALIDITY_MONTHS;
        }

        return (int) $months;
    }
}
