<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Order;
use App\Services\FraudService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Advisory fraud/risk guard (#128).
 *
 * Verifies the guard FLAGS risky orders (for human review) while never blocking a legitimate
 * customer — every apply still returns the normal funnel response (201 / redirect).
 */
final class FraudCheckTest extends TestCase
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

    /** A clean, standard UK intake. */
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
            'begin_now' => true,
        ], $overrides);
    }

    public function test_clean_single_order_is_not_flagged(): void
    {
        Mail::fake();
        Queue::fake();

        $dest = $this->makeDestination();

        $response = $this->postJson('/apply', $this->standardIntake($dest));

        $response->assertStatus(201);

        $order = Order::query()->where('order_ref', $response->json('order_ref'))->firstOrFail();

        $this->assertFalse((bool) $order->risk_flag, 'A clean single order must not be flagged.');
        $this->assertSame(0, (int) $order->risk_score);
        $this->assertNull($order->risk_reason);

        // No fraud event recorded for a clean order.
        $this->assertSame(0, $order->events()->where('agent', 'fraud')->count());
    }

    public function test_two_orders_same_email_in_quick_succession_flags_the_second(): void
    {
        Mail::fake();
        Queue::fake();

        $dest = $this->makeDestination();
        $intake = $this->standardIntake($dest, ['email' => 'repeat@example.com']);

        // First order — clean (only one with this email so far).
        $first = $this->postJson('/apply', $intake);
        $first->assertStatus(201);
        $firstOrder = Order::query()->where('order_ref', $first->json('order_ref'))->firstOrFail();
        $this->assertFalse((bool) $firstOrder->risk_flag, 'First order should not be flagged.');

        // Second order, same email, moments later — velocity (+duplicate) trips the flag.
        $second = $this->postJson('/apply', $intake);

        // Customer is NOT blocked: the funnel still returns the normal 201 response.
        $second->assertStatus(201);

        $secondOrder = Order::query()->where('order_ref', $second->json('order_ref'))->firstOrFail();

        $this->assertTrue((bool) $secondOrder->risk_flag, 'Second same-email order should be flagged.');
        $this->assertGreaterThanOrEqual(FraudService::THRESHOLD, (int) $secondOrder->risk_score);
        $this->assertIsArray($secondOrder->risk_reason);
        $this->assertContains('velocity', $secondOrder->risk_reason);

        // An auditable fraud event is recorded on the flagged order.
        $this->assertSame(1, $secondOrder->events()->where('agent', 'fraud')->count());
    }

    public function test_assess_is_pure_and_writes_nothing(): void
    {
        Mail::fake();
        Queue::fake();

        $dest = $this->makeDestination();

        // Build an order directly (no fraud check yet).
        $order = new Order;
        $order->email = 'noah@mailinator.com'; // disposable domain
        $order->phone = '+44 7700 900111';
        $order->destination_name = $dest->name;
        $order->prior_refusal = true; // declared refusal
        $order->status = 'paid';
        $order->save();

        $result = app(FraudService::class)->assess($order, '203.0.113.10');

        // disposable_email (30) + prior_refusal (20) = 50 -> at threshold.
        $this->assertSame(50, $result['score']);
        $this->assertContains('disposable_email', $result['flags']);
        $this->assertContains('prior_refusal', $result['flags']);

        // assess() must not mutate/persist anything.
        $order->refresh();
        $this->assertFalse((bool) $order->risk_flag);
        $this->assertSame(0, (int) $order->risk_score);
        $this->assertSame(0, $order->events()->where('agent', 'fraud')->count());
    }
}
