<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EventChannel;
use App\Enums\EventType;
use App\Enums\OrderStatus;
use App\Enums\OrderTier;
use App\Models\ChecklistRequest;
use App\Models\Order;
use App\Services\ChecklistPricing;
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
 *      agent-set amount, created by createBespokeQuotePaymentLink() via the live Payment Links
 *      API (guarded to a placeholder when no Stripe secret is configured).
 *
 * Payment is signalled by the order's `paid_at` timestamp (set by the webhook), a distinct
 * "money received" marker rather than the `paid` pipeline ENTRY stage (orders are created at
 * `paid`). The webhook in this service is the sole writer of `paid_at`.
 */
final class StripeService
{
    public function __construct(
        private readonly PricingService $pricing,
        private readonly EmailService $emails,
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
     *   paid. Idempotent: if the order already has `paid_at` set, it is left untouched.
     * - Records an order_event, sends the order_paid email, and dispatches the CRM sync.
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

        // Checklist purchases carry metadata.type='checklist' and are resolved by token.
        if (($session->metadata->type ?? null) === 'checklist') {
            $token = (string) ($session->metadata->token ?? $session->client_reference_id ?? '');
            if ($token !== '') {
                $this->markChecklistPaidByToken($token, $session->id ?? null, $session->customer_email ?? null);
            }

            return;
        }

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
     * Build a Stripe Checkout Session for an instant CHECKLIST purchase. Charges the chosen
     * tier's per-destination service fee. metadata.type='checklist' routes the webhook to the
     * checklist path. Throws if the request has no tier / destination / resolvable price.
     */
    public function createChecklistSession(ChecklistRequest $request): string
    {
        $destination = $request->destination;
        if ($destination === null) {
            throw new \InvalidArgumentException("Checklist {$request->token} has no destination.");
        }

        $tier = (string) $request->tier;
        if ($tier === '') {
            throw new \InvalidArgumentException("Checklist {$request->token} has no tier.");
        }

        $amount = app(ChecklistPricing::class)->priceFor($destination, $tier);
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Tier '{$tier}' is not priced for this destination.");
        }

        $session = $this->client()->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'gbp',
                    'unit_amount' => (int) round($amount * 100),
                    'product_data' => [
                        'name' => sprintf(
                            '%s document checklist — %s',
                            $destination->name ?? 'Trip',
                            ucfirst($tier),
                        ),
                    ],
                ],
            ]],
            'metadata' => [
                'type' => 'checklist',
                'checklist_id' => (string) $request->getKey(),
                'token' => (string) $request->token,
                'tier' => $tier,
            ],
            'client_reference_id' => (string) $request->token,
            'success_url' => url('/checklist/'.$request->token).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => url('/checklist/'.$request->token),
        ]);

        return (string) $session->url;
    }

    /**
     * Read-only check used on the Stripe success return so the buyer sees the list instantly,
     * even before the webhook lands. NO database write. True iff the session is paid and its
     * metadata.token matches.
     */
    public function isChecklistSessionPaid(string $token, string $sessionId): bool
    {
        if ($sessionId === '' || (string) config('services.stripe.secret') === '') {
            return false;
        }

        try {
            $session = $this->client()->checkout->sessions->retrieve($sessionId);
        } catch (\Throwable $e) {
            Log::warning('Checklist session retrieve failed.', ['error' => $e->getMessage()]);

            return false;
        }

        $metaToken = $session->metadata->token ?? null;

        return ($session->payment_status ?? null) === 'paid' && $metaToken === $token;
    }

    /**
     * Idempotently mark a checklist paid (sole writer of paid_at on the checklist path).
     * DB-only — safe to call from tests without the Stripe SDK. Dispatches post-pay delivery.
     */
    public function markChecklistPaidByToken(string $token, ?string $sessionId, ?string $email): void
    {
        $request = ChecklistRequest::query()->where('token', $token)->first();
        if ($request === null) {
            Log::warning('Stripe webhook: checklist not found for token.', ['token' => $token]);

            return;
        }

        if ($request->paid_at !== null) {
            return; // idempotent
        }

        $request->paid_at = \Illuminate\Support\Carbon::now();
        $request->stripe_session_id = $sessionId;
        if ($email !== null && $email !== '' && $request->email === null) {
            $request->email = $email;
        }
        $request->save();

        if (class_exists(\App\Jobs\DeliverPaidChecklist::class)) {
            \App\Jobs\DeliverPaidChecklist::dispatch($request->id);
        }
    }

    /**
     * Idempotently mark an order paid and record the event.
     */
    private function markOrderPaid(Order $order, \Stripe\Checkout\Session $session): void
    {
        // --- Idempotency: keyed on `paid_at`, NOT on the `status === Paid` check. ---
        // Orders are CREATED at `paid` (the pipeline entry stage), so the old status check
        // returned early for every brand-new order and the payment audit/email never ran (H-3).
        // `paid_at` is the distinct "money received" signal: null = not yet paid, set = done.
        // A Stripe retry of the same event therefore short-circuits here without duplicating
        // the audit event, the confirmation email, or the CRM sync.
        if ($order->paid_at !== null) {
            return;
        }

        // Stamp the money-received signal and ensure the order rests at the `paid` entry stage.
        $order->paid_at = Carbon::now();
        if (! ($order->status instanceof OrderStatus && $order->status === OrderStatus::Paid)) {
            $order->status_last = $order->status instanceof OrderStatus
                ? $order->status->value
                : (string) $order->status;
            $order->status = OrderStatus::Paid;
        }
        $order->save();

        // --- Audit trail ---
        $order->events()->create([
            'occurred_at' => Carbon::now(),
            'agent' => 'stripe-webhook',
            'channel' => EventChannel::Internal,
            'type' => EventType::System,
            'text' => sprintf(
                'Payment received via Stripe Checkout (%s). Order marked paid.',
                $session->id ?? 'unknown session',
            ),
            'meta' => [
                'stripe_session_id' => $session->id ?? null,
                'stripe_payment_intent' => $session->payment_intent ?? null,
                'amount_total' => $session->amount_total ?? null,
                'currency' => $session->currency ?? null,
            ],
        ]);

        // --- Side effects (BLOCKER-3): payment-confirmation email + CRM sync. ---
        // The webhook is the sole writer of a genuine payment, so the order_paid email is
        // owned here (EmailService::onStageChange is deliberately silent on `paid`).
        $this->emails->sendOrderPaid($order);
        \App\Jobs\SyncOrderToHubSpot::dispatch($order, 'Payment received via Stripe.');
    }

    /**
     * Bespoke-quote Payment Link path (manual_review / cleared lane). (H-4)
     *
     * The standard lane uses Checkout Sessions (above). The bespoke lane needs a per-order
     * Stripe Payment Link for an agent-set amount. This creates an inline GBP Price and a
     * Payment Link via the live Stripe API, persists the hosted URL on the order's latest
     * quote (if any), and returns it.
     *
     * Guard: when config('services.stripe.secret') is empty (no keys configured — pre-launch),
     * this logs and returns PricingService::QUOTE_PLACEHOLDER_LINK without calling Stripe, so
     * the method is a safe no-op in environments with no Stripe credentials.
     *
     * @return string the hosted Payment Link URL, or the placeholder when Stripe is unconfigured.
     */
    public function createBespokeQuotePaymentLink(Order $order, float $amount): string
    {
        if ((string) config('services.stripe.secret') === '') {
            Log::info('Stripe not configured; bespoke Payment Link not created (returning placeholder).', [
                'order_ref' => $order->order_ref,
                'amount' => $amount,
            ]);

            return PricingService::QUOTE_PLACEHOLDER_LINK;
        }

        // The static \Stripe\* resources read the global API key.
        \Stripe\Stripe::setApiKey((string) config('services.stripe.secret'));

        $price = \Stripe\Price::create([
            'currency' => 'gbp',
            // Smallest currency unit (pence); round to avoid float drift.
            'unit_amount' => (int) round($amount * 100),
            'product_data' => ['name' => "Bespoke UK visa service — {$order->order_ref}"],
        ]);

        $link = \Stripe\PaymentLink::create([
            'line_items' => [['price' => $price->id, 'quantity' => 1]],
            'metadata' => [
                'order_id' => (string) $order->getKey(),
                'order_ref' => (string) $order->order_ref,
            ],
        ]);

        $url = (string) $link->url;

        // Persist the live URL on the order's latest quote, if one exists.
        $quote = $order->quotes()->latest('id')->first();
        if ($quote !== null) {
            $quote->payment_link = $url;
            $quote->save();
        }

        return $url;
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
