<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\EventChannel;
use App\Enums\EventType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Daily Stripe <-> orders reconciliation safety net (port of mu-plugins/ukv-reconcile.php).
 *
 * WHAT IT DOES
 * Pulls recent successful Stripe payments (Checkout Sessions in mode=payment, plus the
 * underlying PaymentIntents) from the last ~48h — or since the last successful run if a
 * watermark is stored — and cross-checks each against our Order records. It flags four
 * classes of discrepancy for HUMAN REVIEW and never silently mutates money state:
 *
 *   1. paid_no_order      — Stripe shows a successful payment with no matching order
 *                           (a missed / dropped webhook = revenue at risk; this is the
 *                           single thing the WP version flagged).
 *   2. paid_order_unpaid  — Stripe paid, but the matched order is NOT marked `paid`
 *                           (webhook fired but the transition was lost / reverted).
 *   3. order_paid_no_stripe — order is `paid` in our DB but we found NO matching successful
 *                           Stripe payment in the window (manual flip / test data / refund drift).
 *   4. amount_mismatch    — order matched, but the Stripe amount differs from order->total
 *                           by more than £0.01 (partial capture, wrong price, currency mix).
 *
 * Findings are recorded as OrderEvents (type=system) on the matched order where one exists,
 * and always written to the application log as a structured summary. Orphan Stripe payments
 * (paid_no_order) have no order to attach to, so they are reported via the log + console only.
 *
 * SAFETY: read-only against money state. It does NOT mark orders paid/unpaid, does not refund,
 * does not create orders. It only reports — mirroring the WP behaviour ("Read-only: it only
 * reports") — so a human resolves each flag.
 *
 * NO-OP: if config('services.stripe.secret') is empty, the command logs a notice and exits 0.
 */
class ReconcileStripe extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ukv:reconcile-stripe
        {--hours=48 : Look-back window in hours when no stored watermark exists}
        {--limit=100 : Max Stripe objects to page through per type (safety cap)}
        {--dry-run : Report findings without recording OrderEvents (log + console only)}';

    /**
     * @var string
     */
    protected $description = 'Reconcile recent Stripe payments against orders and flag mismatches for human review (read-only).';

    /**
     * Cache key for the "since last run" watermark (epoch seconds of the last window end).
     */
    private const WATERMARK_KEY = 'ukv:reconcile-stripe:last_run_at';

    /**
     * Money tolerance in GBP — matches the WP source (£0.01).
     */
    private const AMOUNT_TOLERANCE = 0.01;

    public function handle(): int
    {
        $secret = (string) config('services.stripe.secret');

        if ($secret === '') {
            $this->warn('Stripe secret is empty (services.stripe.secret) — skipping reconciliation (no-op).');
            Log::notice('ukv:reconcile-stripe skipped: Stripe secret not configured.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');

        // --- Determine the look-back window. Prefer the stored watermark, else --hours. ---
        $now = Carbon::now();
        $sinceEpoch = $this->resolveSince($now, (int) $this->option('hours'));
        $this->info(sprintf(
            'Reconciling Stripe payments created since %s UTC%s.',
            Carbon::createFromTimestamp($sinceEpoch)->toDateTimeString(),
            $dryRun ? ' [dry-run]' : '',
        ));

        // --- Pull successful payments from Stripe. ---
        try {
            $payments = $this->fetchSuccessfulPayments($secret, $sinceEpoch, (int) $this->option('limit'));
        } catch (\Throwable $e) {
            $this->error('Stripe API error: '.$e->getMessage());
            Log::error('ukv:reconcile-stripe Stripe API error.', ['error' => $e->getMessage()]);

            // Do NOT advance the watermark on failure, so the next run re-covers this window.
            return self::FAILURE;
        }

        $this->info(sprintf('Fetched %d successful Stripe payment(s) in window.', count($payments)));

        $findings = [];
        $matchedOrderIds = [];

        // --- Pass 1: every successful Stripe payment must map to a `paid` order at the right amount. ---
        foreach ($payments as $payment) {
            $order = $this->resolveOrder($payment);

            if ($order === null) {
                $findings[] = $this->flagOrphan($payment, $dryRun);

                continue;
            }

            $matchedOrderIds[$order->getKey()] = true;

            // Amount check (Stripe amount is in pence; order->total is GBP).
            $stripeGbp = $payment['amount'] / 100;
            $orderTotal = (float) $order->total;
            if (abs($stripeGbp - $orderTotal) > self::AMOUNT_TOLERANCE) {
                $findings[] = $this->flag(
                    $order,
                    'amount_mismatch',
                    sprintf(
                        'Stripe payment %s is £%s but order total is £%s (>£%s drift).',
                        $payment['id'],
                        number_format($stripeGbp, 2),
                        number_format($orderTotal, 2),
                        number_format(self::AMOUNT_TOLERANCE, 2),
                    ),
                    $payment,
                    $dryRun,
                );
            }

            // Paid-in-Stripe but order not marked paid.
            if ($order->status !== OrderStatus::Paid) {
                $findings[] = $this->flag(
                    $order,
                    'paid_order_unpaid',
                    sprintf(
                        'Stripe shows a successful payment (%s) but order status is "%s", not paid.',
                        $payment['id'],
                        $order->status instanceof OrderStatus ? $order->status->value : (string) $order->status,
                    ),
                    $payment,
                    $dryRun,
                );
            }
        }

        // --- Pass 2: orders marked paid in the window with NO matching successful Stripe payment. ---
        // Scoped to orders that entered the paid stage within the window to avoid re-scanning history.
        $paidOrders = Order::query()
            ->where('status', OrderStatus::Paid->value)
            ->where('updated_at', '>=', Carbon::createFromTimestamp($sinceEpoch))
            ->get();

        foreach ($paidOrders as $order) {
            if (isset($matchedOrderIds[$order->getKey()])) {
                continue; // already reconciled against a Stripe payment above
            }

            $findings[] = $this->flag(
                $order,
                'order_paid_no_stripe',
                sprintf(
                    'Order is marked paid (updated %s) but no matching successful Stripe payment was found in the window.',
                    optional($order->updated_at)->toDateTimeString() ?? 'unknown',
                ),
                null,
                $dryRun,
            );
        }

        // --- Summarise. ---
        $this->summarise($findings, count($payments), count($paidOrders));

        // Advance the watermark only on a clean (non-error) run so we don't re-scan resolved
        // windows. We still keep a 1h overlap to absorb Stripe event-availability lag.
        if (! $dryRun) {
            cache()->forever(self::WATERMARK_KEY, $now->getTimestamp());
        }

        // Non-zero exit if anything needs a human, so a scheduler/monitor can alert.
        return $findings === [] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Resolve the window start epoch: stored watermark (minus 1h overlap) if present, else now-hours.
     */
    private function resolveSince(Carbon $now, int $hours): int
    {
        $watermark = cache()->get(self::WATERMARK_KEY);

        if (is_int($watermark) || (is_string($watermark) && ctype_digit($watermark))) {
            // 1h overlap guards against Stripe events that become listable slightly late.
            return max(0, (int) $watermark - 3600);
        }

        $hours = $hours > 0 ? $hours : 48;

        return $now->copy()->subHours($hours)->getTimestamp();
    }

    /**
     * Pull successful payments from Stripe in the window.
     *
     * Strategy: list Checkout Sessions (the standard self-serve lane) with status=complete and
     * payment_status=paid, which carry our order metadata directly. Also list PaymentIntents with
     * status=succeeded to catch Payment Link / bespoke-lane charges whose metadata lives on the
     * intent. De-duplicate by payment_intent id so a session + its intent aren't double-counted.
     *
     * @return list<array{id:string,intent_id:?string,amount:int,currency:string,email:?string,metadata:array<string,string>,client_reference_id:?string,source:string}>
     */
    private function fetchSuccessfulPayments(string $secret, int $sinceEpoch, int $limit): array
    {
        $client = new \Stripe\StripeClient($secret);
        $limit = $limit > 0 ? min($limit, 100) : 100;

        $payments = [];
        $seenIntents = [];

        // 1) Checkout Sessions (standard lane) ------------------------------------------------
        $sessions = $client->checkout->sessions->all([
            'created' => ['gte' => $sinceEpoch],
            'limit' => $limit,
        ]);

        foreach ($sessions->autoPagingIterator() as $session) {
            if (($session->payment_status ?? null) !== 'paid') {
                continue;
            }

            $intentId = is_string($session->payment_intent ?? null) ? $session->payment_intent : null;
            if ($intentId !== null) {
                $seenIntents[$intentId] = true;
            }

            $payments[] = [
                'id' => (string) $session->id,
                'intent_id' => $intentId,
                'amount' => (int) ($session->amount_total ?? 0),
                'currency' => (string) ($session->currency ?? ''),
                'email' => $session->customer_email
                    ?? ($session->customer_details->email ?? null),
                'metadata' => $this->metadataToArray($session->metadata ?? null),
                'client_reference_id' => $session->client_reference_id ?? null,
                'source' => 'checkout_session',
            ];
        }

        // 2) PaymentIntents (bespoke / payment-link lane) -------------------------------------
        $intents = $client->paymentIntents->all([
            'created' => ['gte' => $sinceEpoch],
            'limit' => $limit,
        ]);

        foreach ($intents->autoPagingIterator() as $intent) {
            if (($intent->status ?? null) !== 'succeeded') {
                continue;
            }
            if (isset($seenIntents[$intent->id])) {
                continue; // already represented by its Checkout Session
            }

            $payments[] = [
                'id' => (string) $intent->id,
                'intent_id' => (string) $intent->id,
                'amount' => (int) ($intent->amount_received ?? $intent->amount ?? 0),
                'currency' => (string) ($intent->currency ?? ''),
                'email' => $intent->receipt_email
                    ?? ($intent->metadata->email ?? null),
                'metadata' => $this->metadataToArray($intent->metadata ?? null),
                'client_reference_id' => null,
                'source' => 'payment_intent',
            ];
        }

        return $payments;
    }

    /**
     * Resolve an Order from a Stripe payment record.
     *
     * Match order (mirrors StripeService::resolveOrderFromSession, then falls back to the WP
     * email+amount heuristic):
     *   1. metadata.order_id  (most reliable; set by createCheckoutSession)
     *   2. metadata.order_ref / client_reference_id
     *   3. email + amount within £0.01  (the WP fallback for legacy / link-less charges)
     *
     * @param array{id:string,amount:int,email:?string,metadata:array<string,string>,client_reference_id:?string} $payment
     */
    private function resolveOrder(array $payment): ?Order
    {
        $meta = $payment['metadata'];

        if (! empty($meta['order_id'])) {
            $order = Order::query()->find($meta['order_id']);
            if ($order !== null) {
                return $order;
            }
        }

        $ref = $meta['order_ref'] ?? $payment['client_reference_id'] ?? null;
        if ($ref !== null && $ref !== '') {
            $order = Order::query()->where('order_ref', $ref)->first();
            if ($order !== null) {
                return $order;
            }
        }

        // WP-style fallback: match by email + amount (within £0.01).
        $email = $payment['email'];
        if ($email !== null && $email !== '') {
            $gbp = $payment['amount'] / 100;

            return Order::query()
                ->where('email', $email)
                ->whereBetween('total', [$gbp - self::AMOUNT_TOLERANCE, $gbp + self::AMOUNT_TOLERANCE])
                ->first();
        }

        return null;
    }

    /**
     * Record a finding against a matched order (OrderEvent + log) and echo to console.
     *
     * @param array<string,mixed>|null $payment
     * @return array{kind:string,order_id:int|string|null,order_ref:?string,detail:string,stripe_id:?string}
     */
    private function flag(Order $order, string $kind, string $detail, ?array $payment, bool $dryRun): array
    {
        $finding = [
            'kind' => $kind,
            'order_id' => $order->getKey(),
            'order_ref' => $order->order_ref,
            'detail' => $detail,
            'stripe_id' => $payment['id'] ?? null,
        ];

        if (! $dryRun) {
            OrderEvent::create([
                'order_id' => $order->getKey(),
                'occurred_at' => Carbon::now(),
                'agent' => 'stripe-reconcile',
                'channel' => EventChannel::Internal,
                'type' => EventType::System,
                'text' => sprintf('[RECONCILE: %s] %s Needs human review — no money state was changed.', $kind, $detail),
                'meta' => [
                    'reconcile_kind' => $kind,
                    'stripe_id' => $payment['id'] ?? null,
                    'stripe_intent_id' => $payment['intent_id'] ?? null,
                    'stripe_amount' => $payment['amount'] ?? null,
                    'stripe_currency' => $payment['currency'] ?? null,
                    'order_total' => (string) $order->total,
                ],
            ]);
        }

        $this->line(sprintf('  [%s] order %s — %s', $kind, $order->order_ref, $detail));

        return $finding;
    }

    /**
     * Flag a successful Stripe payment that maps to NO order (missed webhook = revenue at risk).
     * There is no order to attach an OrderEvent to, so it is reported via log + console only.
     *
     * @param array<string,mixed> $payment
     * @return array{kind:string,order_id:null,order_ref:null,detail:string,stripe_id:string}
     */
    private function flagOrphan(array $payment, bool $dryRun): array
    {
        $detail = sprintf(
            'Successful Stripe payment %s (£%s, %s) has NO matching order — possible missed webhook.',
            $payment['id'],
            number_format($payment['amount'] / 100, 2),
            $payment['email'] ?? 'no email',
        );

        $this->line(sprintf('  [paid_no_order] %s', $detail));

        return [
            'kind' => 'paid_no_order',
            'order_id' => null,
            'order_ref' => null,
            'detail' => $detail,
            'stripe_id' => (string) $payment['id'],
        ];
    }

    /**
     * Log a structured summary of the run and print a console tally.
     *
     * @param list<array<string,mixed>> $findings
     */
    private function summarise(array $findings, int $paymentsSeen, int $paidOrdersSeen): void
    {
        $byKind = [];
        foreach ($findings as $f) {
            $byKind[$f['kind']] = ($byKind[$f['kind']] ?? 0) + 1;
        }

        if ($findings === []) {
            $this->info('All recent Stripe payments reconciled — no mismatches.');
            Log::info('ukv:reconcile-stripe clean run.', [
                'payments_seen' => $paymentsSeen,
                'paid_orders_seen' => $paidOrdersSeen,
            ]);

            return;
        }

        $this->warn(sprintf('%d reconciliation finding(s) need human review:', count($findings)));
        foreach ($byKind as $kind => $n) {
            $this->warn(sprintf('  - %s: %d', $kind, $n));
        }

        Log::warning('ukv:reconcile-stripe found mismatches needing review.', [
            'payments_seen' => $paymentsSeen,
            'paid_orders_seen' => $paidOrdersSeen,
            'counts' => $byKind,
            'findings' => $findings,
        ]);
    }

    /**
     * Normalise a Stripe metadata object (StripeObject|array|null) to a string-keyed array.
     *
     * @param mixed $metadata
     * @return array<string,string>
     */
    private function metadataToArray(mixed $metadata): array
    {
        if ($metadata === null) {
            return [];
        }
        if (is_object($metadata) && method_exists($metadata, 'toArray')) {
            $metadata = $metadata->toArray();
        }
        if (! is_array($metadata)) {
            return [];
        }

        $out = [];
        foreach ($metadata as $k => $v) {
            $out[(string) $k] = is_scalar($v) ? (string) $v : '';
        }

        return $out;
    }
}
