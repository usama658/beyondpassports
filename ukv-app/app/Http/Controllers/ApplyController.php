<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EligibilityLane;
use App\Http\Requests\ApplyRequest;
use App\Mail\NewApplication;
use App\Models\Order;
use App\Services\FraudService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
    public function __construct(
        private readonly OrderService $orders,
        private readonly FraudService $fraud,
    ) {}

    public function store(ApplyRequest $request): JsonResponse|RedirectResponse
    {
        $intake = $request->validated();
        $order = $this->orders->createFromIntake($intake);

        // Advisory fraud/risk guard (#128): score the freshly-created order and, if it crosses
        // the threshold, flag it + log a fraud event for human review. This NEVER blocks the
        // customer — the funnel below proceeds exactly the same whether flagged or not. It
        // complements Stripe Radar (card-level, dashboard config); this is application-level.
        $this->fraud->flagIfRisky($order, $request->ip());

        // Notify the owner of every new application so leads never sit silently (esp. the
        // manual_review lane, which needs a hand-checked quote + callback). Inline send +
        // try/catch, mirroring ContactController — a mail hiccup logs but never breaks the funnel.
        $recipient = config('ukv.owner_email') ?: config('mail.from.address');
        if (! empty($recipient)) {
            try {
                Mail::to($recipient)->send(new NewApplication($order, $intake));
                Log::info('New application emailed', ['to' => $recipient, 'order' => $order->order_ref]);
            } catch (\Throwable $e) {
                Log::error('New application email failed', ['order' => $order->order_ref, 'error' => $e->getMessage()]);
            }
        }

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
        // Send the traveller to a thank-you page that confirms the callback + opens WhatsApp,
        // mirroring the contact flow. Server-driven so it works with or without JS.
        $first = trim(explode(' ', trim((string) $order->applicant_name))[0]);
        $waText = "Hi Beyond Passports, I just submitted an application (ref {$order->order_ref}).\n"
            ."Name: {$order->applicant_name}\n"
            ."Destination: {$order->destination_name}"
            .($order->phone ? "\nPhone: {$order->phone}" : '')
            ."\nPlease confirm what I need and my personalised quote.";
        $waUrl = 'https://wa.me/'.(config('ukv.whatsapp') ?: '447882747584').'?text='.rawurlencode($waText);

        $request->session()->flash('apply_thanks', [
            'name' => $first,
            'ref' => $order->order_ref,
            'destination' => $order->destination_name,
            'wa_url' => $waUrl,
        ]);

        if (! $wantsJson) {
            return redirect()->route('apply.thanks');
        }

        return response()->json([
            'lane' => EligibilityLane::ManualReview->value,
            'order_ref' => $order->order_ref,
            'next' => 'callback',
            'redirect' => route('apply.thanks'),
        ], 201);
    }

    /**
     * Thank-you page for the manual-review lane. Confirms the callback + quote, and auto-opens
     * the prefilled WhatsApp chat. Reads the one-shot flash from store(); a direct visit with no
     * flash bounces back to /apply.
     */
    public function thanks(): RedirectResponse|\Illuminate\Contracts\View\View
    {
        $ctx = session('apply_thanks');
        if (empty($ctx)) {
            return redirect()->route('apply');
        }

        return view('public.apply-thanks', [
            'leadName' => $ctx['name'] ?? '',
            'orderRef' => $ctx['ref'] ?? '',
            'destination' => $ctx['destination'] ?? '',
            'waUrl' => $ctx['wa_url'] ?? null,
        ]);
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
