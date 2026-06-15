<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EligibilityLane;
use App\Enums\OrderStatus;
use App\Models\Destination;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * End-to-end customer apply intake (POST /apply -> ApplyController::store -> OrderService).
 *
 * Contract assertions only (status code, lane, persisted order state) — no brittle HTML.
 * Mail::fake()/Queue::fake() stop the lifecycle email + HubSpot sync side-effects.
 */
final class ApplyFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeDestination(array $overrides = []): Destination
    {
        return Destination::create(array_merge([
            'name' => 'Testlandia',
            'slug' => 'testlandia',
            'visa_type' => 'evisa',
            'govt_fee_gbp' => 20.00,
            'tier_standard_gbp' => 39.00,
            'tier_express_gbp' => 59.00,
            'tier_premium_gbp' => 89.00,
            'passport_validity_months' => 6,
        ], $overrides));
    }

    /**
     * A fully-UK tourist intake routes to the STANDARD self-serve lane.
     * Display name of the destination is what the public apply form posts.
     */
    private function standardIntake(Destination $dest, array $overrides = []): array
    {
        return array_merge([
            'applicant_name' => 'Jane Traveller',
            'email' => 'jane@example.com',
            'phone' => '+44 7700 900000',
            'destination' => $dest->name,
            'tier' => 'standard',
            'trip_purpose' => 'tourist',
            'travel_date' => now()->addMonths(3)->toDateString(),
            'nationality' => 'United Kingdom',
            'residence_country' => 'United Kingdom',
            'residency_status' => 'citizen',
            'is_minor' => false,
            'prior_refusal' => false,
            'consent' => true,
        ], $overrides);
    }

    public function test_standard_uk_intake_returns_201_standard_lane_and_creates_paid_order(): void
    {
        Mail::fake();
        Queue::fake();

        $dest = $this->makeDestination();

        $response = $this->postJson('/apply', $this->standardIntake($dest));

        $response->assertStatus(201);
        $response->assertJsonPath('lane', EligibilityLane::Standard->value);
        $response->assertJsonPath('next', 'checkout');

        $ref = $response->json('order_ref');
        $this->assertNotEmpty($ref);

        $order = Order::query()->where('order_ref', $ref)->firstOrFail();

        // Order created at the entry stage `paid`.
        $this->assertSame(OrderStatus::Paid, $order->status);
        // Auto-routed to the standard eligibility lane.
        $this->assertSame(EligibilityLane::Standard, $order->eligibility);
        // Destination resolved + snapshotted; chosen tier priced from the destination fees.
        $this->assertSame($dest->getKey(), $order->destination_id);
        $this->assertSame('standard', $order->tier->value);
        $this->assertEqualsWithDelta(39.00, (float) $order->service_fee, 0.001);
        $this->assertEqualsWithDelta(20.00, (float) $order->govt_fee, 0.001);
        $this->assertEqualsWithDelta(59.00, (float) $order->total, 0.001);

        // The standard-lane payload carries a presentational checkout hint with the total.
        $response->assertJsonPath('checkout_hint.total', '59.00');
    }

    public function test_non_uk_intake_routes_to_manual_review_with_no_fixed_tier_or_fees(): void
    {
        Mail::fake();
        Queue::fake();

        $dest = $this->makeDestination();

        // Non-UK nationality -> not eligible for the standard self-serve lane.
        $response = $this->postJson('/apply', $this->standardIntake($dest, [
            'email' => 'kofi@example.com',
            'nationality' => 'Ghana',
            'residence_country' => 'Ghana',
        ]));

        $response->assertStatus(201);
        $response->assertJsonPath('lane', EligibilityLane::ManualReview->value);
        $response->assertJsonPath('next', 'callback');
        // Manual-review payload must NOT expose a fixed checkout hint.
        $response->assertJsonMissingPath('checkout_hint');

        $order = Order::query()->where('order_ref', $response->json('order_ref'))->firstOrFail();

        $this->assertSame(EligibilityLane::ManualReview, $order->eligibility);
        $this->assertSame(OrderStatus::Paid, $order->status); // entry stage; parked by the gate
        // No fixed charge on the manual-review (quote) path.
        $this->assertNull($order->tier);
        $this->assertNull($order->service_fee);
        $this->assertNull($order->govt_fee);
        $this->assertNull($order->total);
    }

    public function test_residency_visa_holder_intake_is_manual_review_even_when_uk(): void
    {
        Mail::fake();
        Queue::fake();

        $dest = $this->makeDestination();

        // UK + tourist but a visa-holder (not a citizen) must NOT qualify for standard.
        $response = $this->postJson('/apply', $this->standardIntake($dest, [
            'residency_status' => 'visa', // ApplyRequest normalises -> visa_holder
        ]));

        $response->assertStatus(201);
        $response->assertJsonPath('lane', EligibilityLane::ManualReview->value);
    }

    public function test_invalid_intake_is_rejected_with_422_and_no_order(): void
    {
        Mail::fake();
        Queue::fake();

        // Missing required fields (email, phone, destination, etc.).
        $response = $this->postJson('/apply', [
            'applicant_name' => 'Nobody',
        ]);

        $response->assertStatus(422);
        $this->assertSame(0, Order::query()->count());
    }
}
