<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Destination;
use App\Services\ChecklistPricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ChecklistPricingTest extends TestCase
{
    use RefreshDatabase;

    private function makeDestination(array $overrides = []): Destination
    {
        return Destination::create(array_merge([
            'name' => 'Turkey',
            'slug' => 'turkey-'.uniqid(),
            'visa_type' => 'evisa',
            'govt_fee_gbp' => 20.00,
            'tier_standard_gbp' => 35.00,
            'tier_express_gbp' => 55.00,
            'tier_premium_gbp' => 85.00,
            'passport_validity_months' => 6,
        ], $overrides));
    }

    public function test_price_for_returns_destination_tier_service_fee(): void
    {
        $d = $this->makeDestination([
            'tier_standard_gbp' => 35, 'tier_express_gbp' => 55, 'tier_premium_gbp' => 85,
        ]);
        $p = app(ChecklistPricing::class);

        $this->assertSame(35.0, $p->priceFor($d, 'standard'));
        $this->assertSame(85.0, $p->priceFor($d, 'premium'));
    }

    public function test_price_for_throws_on_unknown_tier(): void
    {
        $d = $this->makeDestination(['tier_standard_gbp' => 35]);
        $this->expectException(\InvalidArgumentException::class);
        app(ChecklistPricing::class)->priceFor($d, 'gold');
    }

    public function test_cards_lists_only_positive_priced_tiers(): void
    {
        $d = $this->makeDestination([
            'tier_standard_gbp' => 0, 'tier_express_gbp' => 55, 'tier_premium_gbp' => 85,
        ]);
        $cards = app(ChecklistPricing::class)->cards($d);

        $this->assertArrayNotHasKey('standard', $cards);
        $this->assertSame(55.0, $cards['express']);
        $this->assertSame(85.0, $cards['premium']);
    }
}
