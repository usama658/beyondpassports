<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ChecklistRequest;
use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ChecklistRequestPaymentTest extends TestCase
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

    private function make(array $attrs = []): ChecklistRequest
    {
        $dest = $this->makeDestination();

        return ChecklistRequest::create(array_merge([
            'destination_id' => $dest->id,
            'inputs' => [],
            'items' => [
                ['document_key' => 'passport', 'label' => 'Valid passport', 'note' => '6 months', 'category' => 'Identity', 'mandatory' => true],
                ['document_key' => 'photo', 'label' => 'Passport photo', 'note' => null, 'category' => 'Identity', 'mandatory' => true],
                ['document_key' => 'bank', 'label' => 'Bank statements', 'note' => 'last 3 months', 'category' => 'Finance', 'mandatory' => false],
            ],
        ], $attrs));
    }

    public function test_is_paid_reflects_paid_at(): void
    {
        $this->assertFalse($this->make()->isPaid());
        $this->assertTrue($this->make(['paid_at' => now()])->isPaid());
    }

    public function test_peek_redacts_real_labels_except_one_teaser(): void
    {
        $peek = $this->make()->peek();

        $this->assertSame(3, $peek['count']);
        $this->assertEqualsCanonicalizing(['Identity', 'Finance'], $peek['categories']);
        // Exactly one real label is exposed as the teaser; the rest are withheld.
        $this->assertSame('Valid passport', $peek['teaser']['label']);
    }

    public function test_casts(): void
    {
        $r = $this->make(['amount_gbp' => 35, 'immediate_delivery_consent' => 1, 'consent_at' => now(), 'paid_at' => now()]);
        $r->refresh();

        $this->assertSame('35.00', (string) $r->amount_gbp);
        $this->assertTrue($r->immediate_delivery_consent);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $r->consent_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $r->paid_at);
    }
}
