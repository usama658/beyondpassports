<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EligibilityLane;
use App\Http\Requests\ApplyRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Public apply-intake endpoint. Builds the order via OrderService and returns the minimal
 * payload the front-end needs to continue down the correct lane:
 *
 *   standard       -> { lane, order_ref, next: 'checkout', checkout_hint }
 *   manual_review  -> { lane, order_ref, next: 'callback' }
 *
 * The actual Stripe redirect for the standard lane is built by a separate CheckoutController;
 * this endpoint only hands back the order_ref + a hint so the client can route there.
 */
class ApplyController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function store(ApplyRequest $request): JsonResponse|RedirectResponse
    {
        $order = $this->orders->createFromIntake($request->validated());

        $lane = $order->eligibility instanceof EligibilityLane
            ? $order->eligibility
            : EligibilityLane::tryFrom((string) $order->eligibility);

        $wantsJson = $request->expectsJson();

        if ($lane === EligibilityLane::Standard) {
            // No-JS / CSP-blocked fallback: hand straight to checkout (a GET) so the funnel
            // works without the front-end fetch reading the JSON body.
            if (! $wantsJson) {
                return redirect()->route('checkout.create', ['order' => $order->order_ref]);
            }

            return response()->json([
                'lane' => EligibilityLane::Standard->value,
                'order_ref' => $order->order_ref,
                'next' => 'checkout',
                'checkout_hint' => $this->checkoutHint($order),
            ], 201);
        }

        // manual_review (or any non-standard auto lane) -> human callback, no fixed charge.
        if (! $wantsJson) {
            return redirect()->route('apply')->with('status',
                "Thanks — your application (ref {$order->order_ref}) needs a quick human check. "
                ."We'll call you with a personalised quote.");
        }

        return response()->json([
            'lane' => EligibilityLane::ManualReview->value,
            'order_ref' => $order->order_ref,
            'next' => 'callback',
        ], 201);
    }

    /**
     * Lightweight pricing/summary hint for the standard lane so the checkout screen can show
     * the chosen tier + total without re-querying. The CheckoutController owns the real Stripe
     * session; this is purely presentational.
     *
     * @return array<string, mixed>
     */
    private function checkoutHint(Order $order): array
    {
        return [
            'destination' => $order->destination_name,
            'tier' => $order->tier?->value,
            'service_fee' => $order->service_fee,
            'govt_fee' => $order->govt_fee,
            'total' => $order->total,
            'currency' => 'GBP',
        ];
    }
}
