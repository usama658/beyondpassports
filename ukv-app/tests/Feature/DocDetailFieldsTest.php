<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EligibilityLane;
use App\Enums\OrderStatus;
use App\Models\Destination;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Post-pay document-detail capture (POST /documents/details -> DocumentUploadController::detail).
 *
 * Auth is by order ref + email (same non-enumerating model as upload). The five detail fields are
 * all optional but, when present, must be canonical: a bad employment_status fails validation and
 * persists nothing. Mirrors DocumentUploadTest's direct-create order setup (no factory exists).
 */
final class DocDetailFieldsTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        $dest = Destination::create([
            'name' => 'Detailland',
            'slug' => 'detailland',
            'govt_fee_gbp' => 20.00,
            'tier_standard_gbp' => 39.00,
            'tier_express_gbp' => 59.00,
            'tier_premium_gbp' => 89.00,
            'passport_validity_months' => 6,
        ]);

        return Order::create(array_merge([
            'name' => 'Detail Customer',
            'applicant_name' => 'Detail Customer',
            'email' => 'detail.customer@example.com',
            'destination_id' => $dest->getKey(),
            'destination_name' => 'Detailland',
            'status' => OrderStatus::AwaitingDocs->value,
            'eligibility' => EligibilityLane::Standard->value,
        ], $overrides));
    }

    public function test_valid_detail_fields_persist_to_the_order(): void
    {
        $order = $this->makeOrder();

        $response = $this->post('/documents/details', [
            'ref' => $order->order_ref,
            'email' => $order->email,
            'employment_status' => 'self_employed',
            'accommodation_type' => 'host',
            'funding_source' => 'sponsored',
            'return_date' => '2026-09-01',
            'payer_is_applicant' => 'no',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $order->refresh();
        $this->assertSame('self_employed', $order->employment_status);
        $this->assertSame('host', $order->accommodation_type);
        $this->assertSame('sponsored', $order->funding_source);
        $this->assertSame('2026-09-01', $order->return_date->format('Y-m-d'));
        $this->assertFalse($order->payer_is_applicant);
    }

    public function test_invalid_employment_status_fails_validation_and_persists_nothing(): void
    {
        $order = $this->makeOrder();

        $response = $this->from('/documents')->post('/documents/details', [
            'ref' => $order->order_ref,
            'email' => $order->email,
            'employment_status' => 'astronaut', // not in the allowed set
        ]);

        $response->assertSessionHasErrors('employment_status');

        $order->refresh();
        $this->assertNull($order->employment_status);
    }

    public function test_wrong_email_is_a_generic_reject_and_persists_nothing(): void
    {
        $order = $this->makeOrder();

        $response = $this->from('/documents')->post('/documents/details', [
            'ref' => $order->order_ref,
            'email' => 'attacker@example.com',
            'employment_status' => 'employed',
        ]);

        $response->assertRedirect('/documents');
        $response->assertSessionHas('error');

        $order->refresh();
        $this->assertNull($order->employment_status);
    }
}
