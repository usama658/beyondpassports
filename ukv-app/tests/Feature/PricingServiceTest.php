<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderTier;
use App\Enums\QuoteStatus;
use App\Models\Destination;
use App\Models\Order;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Covers fixed-tier pricing derived from per-destination fees, the bespoke-quote path,
 * and the passport-validity boundary. Hits the DB (quotes persist; passport rule reads
 * the destination relation), so RefreshDatabase is used.
 */
final class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    private PricingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PricingService;
    }

    private function makeDestination(array $overrides = []): Destination
    {
        return Destination::create(array_merge([
            'name' => 'Egypt',
            'slug' => 'egypt',
            'govt_fee_gbp' => 25.00,
            'tier_standard_gbp' => 29.00,
            'tier_express_gbp' => 49.00,
            'tier_premium_gbp' => 79.00,
            'passport_validity_months' => 6,
        ], $overrides));
    }

    private function makeOrder(array $overrides = []): Order
    {
        return Order::create(array_merge([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ], $overrides));
    }

    public function test_tiers_computed_from_destination_fees(): void
    {
        $dest = $this->makeDestination([
            'govt_fee_gbp' => 25.00,
            'tier_standard_gbp' => 29.00,
            'tier_express_gbp' => 49.00,
            'tier_premium_gbp' => 79.00,
        ]);

        $tiers = $this->service->tiers($dest);

        // Service fee read straight off the destination; total = service + govt.
        $this->assertSame(OrderTier::Standard, $tiers['standard']['tier']);
        $this->assertEqualsWithDelta(29.00, $tiers['standard']['service_fee'], 0.001);
        $this->assertEqualsWithDelta(54.00, $tiers['standard']['total'], 0.001);

        $this->assertSame(OrderTier::Express, $tiers['express']['tier']);
        $this->assertEqualsWithDelta(49.00, $tiers['express']['service_fee'], 0.001);
        $this->assertEqualsWithDelta(74.00, $tiers['express']['total'], 0.001);

        $this->assertSame(OrderTier::Premium, $tiers['premium']['tier']);
        $this->assertEqualsWithDelta(79.00, $tiers['premium']['service_fee'], 0.001);
        $this->assertEqualsWithDelta(104.00, $tiers['premium']['total'], 0.001);
    }

    public function test_tier_name_derives_from_field_not_hardcoded_price_map(): void
    {
        // Prices OUTSIDE the WP 9-number map (29/49/79, 25/39/59, 35/55/85) must still
        // name correctly — the bug the doc flags. e.g. 100/200/300.
        $dest = $this->makeDestination([
            'govt_fee_gbp' => 10.00,
            'tier_standard_gbp' => 100.00,
            'tier_express_gbp' => 200.00,
            'tier_premium_gbp' => 300.00,
        ]);

        $tiers = $this->service->tiers($dest);

        $this->assertSame(OrderTier::Standard, $tiers['standard']['tier']);
        $this->assertSame(OrderTier::Express, $tiers['express']['tier']);
        $this->assertSame(OrderTier::Premium, $tiers['premium']['tier']);
        $this->assertEqualsWithDelta(110.00, $tiers['standard']['total'], 0.001);
        $this->assertEqualsWithDelta(310.00, $tiers['premium']['total'], 0.001);
    }

    public function test_tiers_handle_missing_fees_as_zero(): void
    {
        $dest = $this->makeDestination([
            'govt_fee_gbp' => null,
            'tier_standard_gbp' => null,
            'tier_express_gbp' => 49.00,
            'tier_premium_gbp' => null,
        ]);

        $tiers = $this->service->tiers($dest);

        $this->assertEqualsWithDelta(0.00, $tiers['standard']['service_fee'], 0.001);
        $this->assertEqualsWithDelta(0.00, $tiers['standard']['total'], 0.001);
        $this->assertEqualsWithDelta(49.00, $tiers['express']['total'], 0.001);
    }

    public function test_bespoke_quote_requires_amount_greater_than_zero(): void
    {
        $order = $this->makeOrder();

        $this->expectException(\InvalidArgumentException::class);
        $this->service->bespokeQuote($order, 0.0);
    }

    public function test_bespoke_quote_rejects_negative_amount(): void
    {
        $order = $this->makeOrder();

        $this->expectException(\InvalidArgumentException::class);
        $this->service->bespokeQuote($order, -10.0);
    }

    public function test_bespoke_quote_creates_sent_quote_with_placeholder_link(): void
    {
        $order = $this->makeOrder();

        $quote = $this->service->bespokeQuote($order, 149.50);

        $this->assertTrue($quote->exists);
        $this->assertEqualsWithDelta(149.50, (float) $quote->amount, 0.001);
        $this->assertSame(QuoteStatus::Sent, $quote->status);
        $this->assertNotNull($quote->sent_at);
        $this->assertSame(PricingService::QUOTE_PLACEHOLDER_LINK, $quote->payment_link);
        $this->assertSame($order->getKey(), $quote->order_id);
    }

    public function test_bespoke_quote_updates_existing_quote_in_place(): void
    {
        $order = $this->makeOrder();

        $first = $this->service->bespokeQuote($order, 100.00);
        $second = $this->service->bespokeQuote($order->fresh(), 200.00);

        $this->assertSame($first->getKey(), $second->getKey());
        $this->assertSame(1, $order->quotes()->count());
        $this->assertEqualsWithDelta(200.00, (float) $second->amount, 0.001);
    }

    public function test_bespoke_quote_does_not_reopen_a_paid_quote(): void
    {
        $order = $this->makeOrder();
        $quote = $this->service->bespokeQuote($order, 100.00);

        // Simulate payment confirmation.
        $quote->update(['status' => QuoteStatus::Paid]);

        $reissued = $this->service->bespokeQuote($order->fresh(), 250.00);

        // Amount updates but status stays paid (never silently reopened to sent).
        $this->assertSame(QuoteStatus::Paid, $reissued->status);
        $this->assertEqualsWithDelta(250.00, (float) $reissued->amount, 0.001);
    }

    public function test_passport_validity_ok_when_expiry_well_beyond_travel(): void
    {
        $dest = $this->makeDestination(['passport_validity_months' => 6]);
        $order = $this->makeOrder([
            'destination_id' => $dest->id,
            'travel_date' => '2026-09-01',
            'passport_expiry' => '2027-09-01', // ~12 months > 6 required
        ]);

        $this->assertTrue($this->service->passportValidityOk($order->fresh()));
    }

    public function test_passport_validity_fails_when_expiry_too_soon(): void
    {
        $dest = $this->makeDestination(['passport_validity_months' => 6]);
        $order = $this->makeOrder([
            'destination_id' => $dest->id,
            'travel_date' => '2026-09-01',
            'passport_expiry' => '2026-10-01', // ~1 month < 6 required
        ]);

        $this->assertFalse($this->service->passportValidityOk($order->fresh()));
    }

    public function test_passport_validity_boundary_uses_average_month_seconds(): void
    {
        $dest = $this->makeDestination(['passport_validity_months' => 6]);
        $travel = Carbon::parse('2026-09-01');

        // need = travel + 6 * 2,629,800 s.
        $needTs = $travel->getTimestamp() + (6 * 2629800);
        $needDate = Carbon::createFromTimestamp($needTs);

        // Exactly on the boundary => OK (expiry >= need).
        $onBoundary = $this->makeOrder([
            'destination_id' => $dest->id,
            'travel_date' => $travel->toDateString(),
            'passport_expiry' => $needDate->toDateString(),
        ]);
        $this->assertTrue($this->service->passportValidityOk($onBoundary->fresh()));

        // One day before the boundary => fails.
        $belowBoundary = $this->makeOrder([
            'destination_id' => $dest->id,
            'travel_date' => $travel->toDateString(),
            'passport_expiry' => $needDate->copy()->subDay()->toDateString(),
        ]);
        $this->assertFalse($this->service->passportValidityOk($belowBoundary->fresh()));
    }

    public function test_passport_validity_defaults_to_six_months_when_destination_unset(): void
    {
        // No destination -> default 6 months requirement.
        $order = $this->makeOrder([
            'travel_date' => '2026-09-01',
            'passport_expiry' => '2026-10-01', // < 6 months
        ]);

        $this->assertFalse($this->service->passportValidityOk($order->fresh()));
    }

    public function test_passport_validity_noop_when_travel_date_missing(): void
    {
        $dest = $this->makeDestination();
        $order = $this->makeOrder([
            'destination_id' => $dest->id,
            'passport_expiry' => '2026-10-01',
            // no travel_date
        ]);

        // Cannot evaluate -> returns true (silent no-op, matches WP no-barrier behaviour).
        $this->assertTrue($this->service->passportValidityOk($order->fresh()));
    }

    public function test_passport_validity_noop_when_expiry_missing(): void
    {
        $dest = $this->makeDestination();
        $order = $this->makeOrder([
            'destination_id' => $dest->id,
            'travel_date' => '2026-09-01',
            // no passport_expiry
        ]);

        $this->assertTrue($this->service->passportValidityOk($order->fresh()));
    }
}
