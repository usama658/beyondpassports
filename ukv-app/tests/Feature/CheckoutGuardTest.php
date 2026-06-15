<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EligibilityLane;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Audit L-3 regression: CheckoutController::create guards manual-review / unpriced orders.
 *
 * A MANUAL_REVIEW (or unpriced) order must NOT reach Stripe — the guard redirects to /apply with
 * a status message instead of calling StripeService (which would 500 here without Stripe keys).
 * We only prove the guard path; the standard-lane real checkout needs Stripe keys, so it is not
 * exercised here.
 */
final class CheckoutGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_review_order_checkout_redirects_to_apply_without_calling_stripe(): void
    {
        // Manual-review, unpriced order — created directly so no Stripe key is ever needed.
        $order = Order::create([
            'name' => 'Kofi Manual',
            'applicant_name' => 'Kofi Manual',
            'email' => 'kofi@example.com',
            'destination_name' => 'Testlandia',
            'status' => OrderStatus::Paid->value,
            'eligibility' => EligibilityLane::ManualReview->value,
            // No service_fee / tier / total — quote path.
        ]);

        $response = $this->get('/checkout/'.$order->order_ref);

        // The guard fires: 302 to the named apply route, no Stripe call, no 500.
        $response->assertStatus(302);
        $response->assertRedirect(route('apply'));
    }

    public function test_unpriced_standard_order_also_hits_the_guard(): void
    {
        // Standard lane but no service_fee set (unpriced) — the `! $order->service_fee` branch.
        $order = Order::create([
            'name' => 'Jane Unpriced',
            'applicant_name' => 'Jane Unpriced',
            'email' => 'jane@example.com',
            'destination_name' => 'Testlandia',
            'status' => OrderStatus::Paid->value,
            'eligibility' => EligibilityLane::Standard->value,
            'service_fee' => null,
        ]);

        $response = $this->get('/checkout/'.$order->order_ref);

        $response->assertStatus(302);
        $response->assertRedirect(route('apply'));
    }
}
