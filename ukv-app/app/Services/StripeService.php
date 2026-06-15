<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EventChannel;
use App\Enums\EventType;
use App\Enums\OrderStatus;
use App\Enums\OrderTier;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Stripe payment layer for the UK visa app.
 *
 * Two payment paths, matching the two pricing lanes (see docs/superpowers/port/02-domain-logic.md):
 *
 *   1. STANDARD self-serve lane  -> Stripe Checkout Session (mode=payment) for the chosen
 *      fixed tier's service fee. createCheckoutSession() builds it; the webhook marks the
 *      order `paid` on checkout.session.completed.
 *
 *   2. BESPOKE quote lane (manual_review / cleared) -> a per-order Stripe Payment Link for an
 *      agent-set amount. The link creation lives in PricingService::bespokeQuote() (currently a
 *      placeholder). createBespokeQuotePaymentLink() below is the placeholder seam where the
 *      live Payment Links API call will go. No live URL is invented here.
 *
 * Status flow is none -> sent -> paid. Nothing in the existing app sets 'paid'; the webhook in
 * this service is the sole writer of the `paid` order status (mirrors the WP source, where the
 * "paid" status had no setter).
 *
 * SDK: references stripe/stripe-php as `\Stripe\...`. The package is NOT installed yet; it must
 * be required before this code can run (see reply notes).
 */
final class StripeService
{
    public function __construct(
        private readonly PricingService $pricing,
        // private readonly OrderService $orders,   // wire when OrderService::transition() exists
        // private readonly EmailService $emails,   // wire when EmailService exists (order_paid)
    ) {}

    /**
     * Build a Stripe Checkout Session for a STANDARD order and return its hosted URL.
     *
     * - mode = payment (one-off charge, not a subscription)
     * - currency = GBP
     * - single line item = the chosen tier's SERVICE FEE (from PricingService::tiers()), in pence
     * - metadata carries order_id + order_ref so the webhook can resolve the order
     * - success_url -> confirmation page (keyed by order ref), cancel_url -> apply page
     *
     * NOTE: this charges the service fee only, mirroring the WP standard lane where the
     * Forminator Stripe field collected `radio-1` (the tier service fee). The government fee is
     * tracked separately on the order (govt_fee / govt_fee_paid).
     *
     * @throws \InvalidArgumentException if the order has no destination or no resolvable tier.
     */
    public function createCheckoutSession(Order $order): string
    {
        $destination = $order->destination;
        if ($destination === null) {
            throw new \InvalidArgumentException(
                "Order {$order->order_ref} has no destination; cannot price a Checkout Session."
            );
        }

        // Resolve the chosen tier (enum or string) to its per-destination pricing row.
        $tierKey = $order->tier instanceof OrderTier
            ? $order->tier->value
            : (string) $order->tier;

        if ($tierKey === '') {
            throw new \InvalidArgumentException(
                "Order {$order->order_ref} has no tier; cannot price a Checkout Session."
            );
        }

        $tiers = $this->pricing->tiers($destination);
        if (! isset($tiers[$tierKey])) {
            throw new \InvalidArgumentException(
                "Tier '{$tierKey}' is not priced for destination '{$destination->getKey()}'."
            );
        }

        $serviceFee = (float) $tiers[$tierKey]['service_fee'];
        if ($serviceFee <= 0) {
            throw new \InvalidArgumentException(
                "Tier '{$tierKey}' has a non-positive service fee; refusing to create a session."
            );
        }

        $client = $this->client();

        $session = $client->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'gbp',
                    // Stripe expects the smallest currency unit (pence). Round to avoid float drift.
                    'unit_amount' => (int) round($serviceFee * 100),
                    'product_data' => [
                        'name' => sprintf(
                            '%s visa service — %s tier',
                            $destination->name ?? $order->destination_name ?? 'UK visa',
                            ucfirst($tierKey),
                        ),
                    ],
                ],
            ]],
            // metadata is echoed back on the webhook event so we can resolve the order.
            'metadata' => [
                'order_id' => (string) $order->getKey(),
                'order_ref' => (string) $order->order_ref,
                'tier' => $tierKey,
            ],
            // client_reference_id is also surfaced on the session for cross-checks.
            'client_reference_id' => (string) $order->order_ref,
            'customer_email' => $order->email,
            'success_url' => $this->successUrl($order),
            'cancel_url' => $this->cancelUrl($order),
        ]);

        return (string) $session->url;
    }

    /**
     * Handle an inbound Stripe webhook.
     *
     * - Verifies the signature against config('services.stripe.webhook_secret'); a bad/empty
     *   signature throws \Stripe\Exception\SignatureVerificationException (the controller maps
     *   that to a 400).
     * - On `checkout.session.completed`, resolves the order from session metadata and marks it
     *   PAID. Idempotent: if the order is already `paid`, it is left untouched.
     * - Records an order_event and leaves a hook for EmailService order_paid.
     *
     * @param  string  $payload  raw request body (must be the unparsed body for signature checks)
     * @param  string  $sig      value of the Stripe-Signature header
     *
     * @throws \Stripe\Exception\SignatureVerificationException on signature mismatch
     */
    public function handleWebhook(string $payload, string $sig): void
    {
        $secret = (string) config('services.stripe.webhook_secret');

        // Throws SignatureVerificationException on mismatch -> controller returns 400.
        $event = \Stripe\Webhook::constructEvent($payload, $sig, $secret);

        if ($event->type !== 'checkout.session.completed') {
            // Not a type we act on. Acknowledge with 200 (handled by controller).
            return;
        }

        /** @var \Stripe\Checkout\Session $session */
        $session = $event->data->object;

        $order = $this->resolveOrderFromSession($session);
        if ($order === null) {
            Log::warning('Stripe webhook: could not resolve order from session.', [
                'session_id' => $session->id ?? null,
            ]);

            return; // nothing to do; acknowledged so Stripe stops retrying
        }

        $this->markOrderPaid($order, $session);
    }

    /**
     * Idempotently mark an order paid and record the event.
     */
    private function markOrderPaid(Order $order, \Stripe\Checkout\Session $session): void
    {
        // --- Idempotency: ignore if already paid (or already past paid in the pipeline). ---
        if ($order->status === OrderStatus::Paid) {
            return;
        }

        // Preserve prior status for the gate's revert-to behaviour, then transition to PAID.
        //
        // PREFERRED: route through OrderService::transition() once it exists, so stage/QA/
        // eligibility gates + audit run centrally:
        //   $this->orders->transition($order, OrderStatus::Paid, agent: 'stripe-webhook');
        //
        // Until then, set the status directly. `paid` is the pipeline ENTRY stage, so no gate
        // blocks entry to it (see EligibilityService::canAdvancePastPaid + stage gates: 'paid'
        // is always enterable).
        $order->status_last = $order->status instanceof OrderStatus
            ? $order->status->value
            : (string) $order->status;
        $order->status = OrderStatus::Paid;
        $order->save();

        // --- Audit trail ---
        $order->events()->create([
            'occurred_at' => Carbon::now(),
            'agent' => 'stripe-webhook',
            'channel' => EventChannel::Internal,
            'type' => EventType::System,
            'text' => sprintf(
                'Payment confirmed via Stripe Checkout (%s). Order marked paid.',
                $session->id ?? 'unknown session',
            ),
            'meta' => [
                'stripe_session_id' => $session->id ?? null,
                'stripe_payment_intent' => $session->payment_intent ?? null,
                'amount_total' => $session->amount_total ?? null,
                'currency' => $session->currency ?? null,
            ],
        ]);

        // --- Email hook ---
        // TODO(email): send the order_paid confirmation once EmailService is available, e.g.
        //   $this->emails->send($order, 'order_paid');
        // Kept as a hook so the webhook stays the single source of the paid transition.
    }

    /**
     * Bespoke-quote Payment Link path (manual_review / cleared lane) — PLACEHOLDER.
     *
     * The standard lane uses Checkout Sessions (above). The bespoke lane instead needs a
     * per-order Stripe Payment Link for an agent-set amount. Today PricingService::bespokeQuote()
     * writes PricingService::QUOTE_PLACEHOLDER_LINK; this method is the seam where the live
     * Payment Links API call will go, returning the hosted URL to store on the Quote.
     *
     * Intentionally NOT implemented against the live API yet (no live URL invented). When wired:
     *
     *   $client = $this->client();
     *   $price = $client->prices->create([
     *       'currency' => 'gbp',
     *       'unit_amount' => (int) round($amount * 100),
     *       'product_data' => ['name' => "Bespoke UK visa service — {$order->order_ref}"],
     *   ]);
     *   $link = $client->paymentLinks->create([
     *       'line_items' => [['price' => $price->id, 'quantity' => 1]],
     *       'metadata' => ['order_id' => (string) $order->getKey(), 'order_ref' => $order->order_ref],
     *   ]);
     *   return (string) $link->url;
     *
     * @return string the placeholder link until the live API is wired
     */
    public function createBespokeQuotePaymentLink(Order $order, float $amount): string
    {
        // TODO(stripe): replace with the live Payment Links API call shown in the docblock.
        return PricingService::QUOTE_PLACEHOLDER_LINK;
    }

    /**
     * Resolve an Order from a completed Checkout Session.
     *
     * Prefers metadata.order_id, then metadata.order_ref / client_reference_id.
     */
    private function resolveOrderFromSession(\Stripe\Checkout\Session $session): ?Order
    {
        $metadata = $session->metadata ?? null;

        $orderId = $metadata->order_id ?? null;
        if ($orderId !== null && $orderId !== '') {
            $order = Order::query()->find($orderId);
            if ($order !== null) {
                return $order;
            }
        }

        $ref = ($metadata->order_ref ?? null) ?: ($session->client_reference_id ?? null);
        if ($ref !== null && $ref !== '') {
            return Order::query()->where('order_ref', $ref)->first();
        }

        return null;
    }

    /**
     * Lazily build the Stripe client from the configured secret key.
     */
    private function client(): \Stripe\StripeClient
    {
        return new \Stripe\StripeClient((string) config('services.stripe.secret'));
    }

    /**
     * Confirmation page URL (success). Keyed by order ref so the page can look the order up.
     * Route name `confirmation` must be registered (see reply notes).
     */
    private function successUrl(Order $order): string
    {
        return route('confirmation', ['order' => $order->order_ref]);
    }

    /**
     * Apply page URL (cancel) — user abandoned checkout.
     * Route name `apply` must be registered (see reply notes).
     */
    private function cancelUrl(Order $order): string
    {
        return route('apply');
    }
}
