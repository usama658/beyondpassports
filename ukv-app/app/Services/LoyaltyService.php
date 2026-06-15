<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DiscountContext;
use App\Models\Discount;
use App\Models\Order;
use Illuminate\Support\Str;

/**
 * Loyalty + review-incentive logic (L2.7 / #177).
 *
 * Ports the WP mu-plugin ukv-discounts.php:
 *   - returning-customer detection (prior orders by the same email)
 *   - a loyalty discount for repeat customers (#83)
 *   - review-incentive code issuance for the NEXT order (#87)
 *
 * Design choices vs WP:
 *   - The WP store was a flat-£10 single option array; here each code is a Discount row
 *     (code / amount / context / email / used / order_ref) — see the discounts migration.
 *   - WP only ever issued a fixed £10. We keep that as the default but make the loyalty
 *     reward expressible as a FIXED amount (£) or a PERCENTAGE of the service fee, so the
 *     business can tune it without a schema change. `amount` always stores the resolved £
 *     value off the order's service fee, never a percentage (the column is a money column).
 *
 * This service is pure logic + Discount writes. It never mutates the Order's fees itself —
 * OrderService owns that (it calls applyReturningCustomerDiscount() during intake).
 */
final class LoyaltyService
{
    /**
     * Loyalty reward type. 'fixed' = a flat £ amount; 'percent' = a share of the service fee.
     * WP parity default is a flat £10 (UKV_LOYALTY_AMOUNT).
     */
    public const LOYALTY_TYPE = 'fixed';

    /** Fixed £ off when LOYALTY_TYPE === 'fixed' (WP: UKV_LOYALTY_AMOUNT = 10.0). */
    public const LOYALTY_FIXED_GBP = 10.0;

    /** Percent off the service fee when LOYALTY_TYPE === 'percent' (e.g. 10.0 = 10%). */
    public const LOYALTY_PERCENT = 10.0;

    /** Flat £ value of a review-incentive code (WP: UKV_REVIEW_AMOUNT = 10.0). */
    public const REVIEW_AMOUNT_GBP = 10.0;

    // -----------------------------------------------------------------------------------
    // Returning-customer detection
    // -----------------------------------------------------------------------------------

    /**
     * True when this email already has at least one OTHER persisted order.
     *
     * Mirrors WP ukv_is_returning_customer() but counts orders OTHER than the given one
     * (so calling it during/after the current order's creation still answers "has prior
     * history?"). An order without an id (not yet saved) simply counts every match.
     */
    public function isReturningCustomer(string $email, ?Order $excluding = null): bool
    {
        $email = trim($email);
        if ($email === '') {
            return false;
        }

        $query = Order::query()->where('email', $email);

        if ($excluding !== null && $excluding->exists) {
            $query->whereKeyNot($excluding->getKey());
        }

        return $query->exists();
    }

    // -----------------------------------------------------------------------------------
    // Loyalty discount — compute + apply to the standard-lane order
    // -----------------------------------------------------------------------------------

    /**
     * Resolve the £ loyalty discount for an order's CURRENT service fee.
     *
     * - fixed:   min(LOYALTY_FIXED_GBP, service_fee)        (never below £0)
     * - percent: round(service_fee * LOYALTY_PERCENT / 100, 2)
     *
     * Returns 0.0 when there is no positive service fee to discount.
     */
    public function loyaltyDiscountFor(Order $order): float
    {
        $serviceFee = (float) ($order->service_fee ?? 0.0);
        if ($serviceFee <= 0.0) {
            return 0.0;
        }

        $discount = match (self::LOYALTY_TYPE) {
            'percent' => round($serviceFee * self::LOYALTY_PERCENT / 100, 2),
            default => self::LOYALTY_FIXED_GBP,
        };

        // Never discount more than the service fee itself (govt fee is untouchable).
        return (float) min($discount, $serviceFee);
    }

    /**
     * Apply a returning-customer loyalty discount to the order IN MEMORY (no save here —
     * the caller persists). Reduces service_fee and total by the same £ amount; govt_fee
     * is never touched. Idempotent-guarded: only acts for a returning customer with a
     * positive resolvable discount, and returns the £ applied (0.0 when nothing applied).
     *
     * A loyalty Discount row (context=loyal) is minted so the reward is auditable/redeemable
     * the same way as any other code, with order_ref pointing at this order.
     */
    public function applyReturningCustomerDiscount(Order $order): float
    {
        $email = (string) ($order->email ?? '');
        if (! $this->isReturningCustomer($email, $order)) {
            return 0.0;
        }

        $discount = $this->loyaltyDiscountFor($order);
        if ($discount <= 0.0) {
            return 0.0;
        }

        $order->service_fee = round((float) $order->service_fee - $discount, 2);
        $order->total = round((float) $order->total - $discount, 2);

        // Mint the auditable loyalty code, already redeemed against this order.
        $this->mintCode(
            amount: $discount,
            context: DiscountContext::Loyal,
            email: $email,
            orderRef: $order->order_ref,
            used: true,
        );

        return $discount;
    }

    // -----------------------------------------------------------------------------------
    // Review-incentive code issuance (#87)
    // -----------------------------------------------------------------------------------

    /**
     * Mint a next-order review-incentive code tied to an order's email.
     * Returns the unused Discount row (the code is on ->code), ready to be emailed.
     *
     * from: WP ukv_issue_review_discount() (flat UKV_REVIEW_AMOUNT, context=review).
     */
    public function issueReviewIncentive(Order $order): Discount
    {
        return $this->mintCode(
            amount: self::REVIEW_AMOUNT_GBP,
            context: DiscountContext::Review,
            email: (string) ($order->email ?? ''),
            orderRef: null,   // valid on a FUTURE order; not redeemed yet
            used: false,
        );
    }

    // -----------------------------------------------------------------------------------
    // Code factory
    // -----------------------------------------------------------------------------------

    /**
     * Create + persist a Discount row with a unique CONTEXT-XXXXXXXX code.
     *
     * Code shape mirrors WP (PREFIX-8HEX) but uniqueness is guaranteed against the table
     * rather than an in-memory option array: we widen / re-seed until the code is free.
     */
    public function mintCode(
        float $amount,
        DiscountContext $context,
        string $email = '',
        ?string $orderRef = null,
        bool $used = false,
    ): Discount {
        return Discount::create([
            'code' => $this->uniqueCode($context),
            'amount' => round($amount, 2),
            'context' => $context->value,
            'email' => trim($email) !== '' ? trim($email) : null,
            'used' => $used,
            'order_ref' => $orderRef,
        ]);
    }

    /** A table-unique CONTEXT-XXXXXXXX code (8 hex chars, widened on the rare collision). */
    private function uniqueCode(DiscountContext $context): string
    {
        $prefix = strtoupper($context->value);

        do {
            $code = $prefix.'-'.strtoupper(substr(md5(Str::uuid()->toString()), 0, 8));
        } while (Discount::query()->where('code', $code)->exists());

        return $code;
    }
}
