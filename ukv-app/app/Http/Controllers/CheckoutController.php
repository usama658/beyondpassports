<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;

/**
 * Starts the STANDARD self-serve checkout: builds a Stripe Checkout Session for the order's
 * chosen tier and redirects the customer to Stripe's hosted payment page.
 *
 * The order is marked `paid` only by the Stripe webhook (StripeWebhookController), never here.
 */
final class CheckoutController extends Controller
{
    public function __construct(
        private readonly StripeService $stripe,
    ) {}

    /**
     * Create a Checkout Session for the given order and redirect to Stripe.
     *
     * Wire as: Route::post('/checkout/{order}', [CheckoutController::class, 'create'])
     *          ->name('checkout.create');
     * (Implicit route-model binding resolves {order}; adjust the key to order_ref via a
     * route key if you bind on reference rather than id.)
     */
    public function create(Order $order): RedirectResponse
    {
        // Manual-review / unpriced orders never go through self-serve Stripe Checkout — they
        // get a bespoke quote from an agent. Guard so a stray /checkout/{ref} can't 500.
        if ($order->eligibility === \App\Enums\EligibilityLane::ManualReview || ! $order->service_fee) {
            return redirect()->route('apply')->with('status',
                'This application needs a personalised quote — our team will be in touch.');
        }

        $url = $this->stripe->createCheckoutSession($order);

        // away() — Stripe Checkout is an external host, not an app route.
        return redirect()->away($url);
    }
}
