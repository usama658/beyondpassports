<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Mail\OrderSubmitted;
use App\Mail\Refunded;
use App\Models\Destination;
use App\Services\EmailService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * L1.8 — OrderService now fires lifecycle emails (via EmailService::onStageChange)
 * and the refund() flow transitions to `refunded` + emails the customer.
 */
class OrderEventsTest extends TestCase
{
    use RefreshDatabase;

    private function makeDestination(): Destination
    {
        return Destination::create([
            'name' => 'Testlandia',
            'slug' => 'testlandia',
            'visa_type' => 'evisa',
            'tier_standard_gbp' => 39,
            'tier_express_gbp' => 59,
            'tier_premium_gbp' => 89,
            'govt_fee_gbp' => 20,
            'passport_validity_months' => 6,
        ]);
    }

    private function standardIntake(Destination $dest): array
    {
        return [
            'applicant_name' => 'Jane Traveller',
            'email' => 'jane@example.com',
            'destination' => $dest->slug,
            'tier' => 'standard',
            'nationality' => 'UK',
            'residence_country' => 'UK',
            'residency_status' => 'citizen',
            'trip_purpose' => 'tourist',
            'prior_refusal' => false,
            'is_minor' => false,
            'consent' => true,
            'begin_now' => true,
        ];
    }

    public function test_refund_transitions_and_emails(): void
    {
        Mail::fake();
        $dest = $this->makeDestination();
        $svc = app(OrderService::class);
        $order = $svc->createFromIntake($this->standardIntake($dest));

        $svc->refund($order->fresh(), 39.0, 'duplicate payment');

        $order->refresh();
        $this->assertSame(OrderStatus::Refunded->value, $order->status->value);
        $this->assertEquals(39.0, (float) $order->refund_amount);
        $this->assertNotNull($order->refunded_at);
        Mail::assertQueued(Refunded::class);
    }

    public function test_onstagechange_maps_submitted_to_email(): void
    {
        Mail::fake();
        $dest = $this->makeDestination();
        $order = app(OrderService::class)->createFromIntake($this->standardIntake($dest));

        app(EmailService::class)->onStageChange($order, OrderStatus::Paid, OrderStatus::Submitted);

        Mail::assertQueued(OrderSubmitted::class);
    }
}
