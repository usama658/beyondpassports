<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SyncOrderToHubSpot;
use App\Mail\OrderPaid;
use App\Models\Destination;
use App\Models\Order;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * BLOCKER-3 / H-3 — the Stripe webhook's paid path now:
 *   - stamps `paid_at` (the distinct money-received signal),
 *   - queues the OrderPaid confirmation email exactly once,
 *   - and is idempotent on a retry (no duplicate mail, paid_at unchanged).
 *
 * The webhook entry point (handleWebhook) requires a valid Stripe signature we cannot forge in
 * a unit test, so we exercise the smallest seam directly: the private markOrderPaid(Order,
 * Session) via reflection, which is the sole writer of the paid transition.
 */
final class WebhookEmailTest extends TestCase
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

    private function paidEligibleOrder(): Order
    {
        $dest = $this->makeDestination();

        // Orders are created at the `paid` ENTRY stage with paid_at still null — exactly the
        // state a checkout.session.completed event arrives for.
        return Order::create([
            'name' => 'Jane Traveller',
            'email' => 'jane@example.com',
            'destination_id' => $dest->getKey(),
            'destination_name' => $dest->name,
            'tier' => 'standard',
            'service_fee' => 39,
            'govt_fee' => 20,
            'total' => 59,
            'status' => 'paid',
        ]);
    }

    /** Invoke the private markOrderPaid seam with a stub Stripe session. */
    private function markPaid(Order $order): void
    {
        $session = new \Stripe\Checkout\Session('cs_test_123');
        $session->payment_intent = 'pi_test_123';
        $session->amount_total = 5900;
        $session->currency = 'gbp';

        $stripe = app(StripeService::class);
        $method = new \ReflectionMethod($stripe, 'markOrderPaid');
        $method->setAccessible(true);
        $method->invoke($stripe, $order, $session);
    }

    public function test_markOrderPaid_sets_paid_at_and_queues_order_paid_email_once(): void
    {
        Mail::fake();
        Queue::fake();

        $order = $this->paidEligibleOrder();
        $this->assertNull($order->paid_at);

        $this->markPaid($order->fresh());

        $order->refresh();
        $this->assertNotNull($order->paid_at, 'paid_at should be stamped on first payment.');
        Mail::assertQueued(OrderPaid::class, 1);
        Queue::assertPushed(SyncOrderToHubSpot::class, 1);
    }

    public function test_markOrderPaid_is_idempotent_on_a_retry(): void
    {
        Mail::fake();
        Queue::fake();

        $order = $this->paidEligibleOrder();

        // First delivery.
        $this->markPaid($order->fresh());
        $paidAt = $order->fresh()->paid_at;
        $this->assertNotNull($paidAt);

        // Stripe retries the same event.
        $this->markPaid($order->fresh());

        $order->refresh();
        // paid_at unchanged (not re-stamped).
        $this->assertEquals($paidAt->toIso8601String(), $order->paid_at->toIso8601String());
        // No duplicate side effects.
        Mail::assertQueued(OrderPaid::class, 1);
        Queue::assertPushed(SyncOrderToHubSpot::class, 1);
    }
}
