<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EligibilityLane;
use App\Enums\EventChannel;
use App\Enums\EventType;
use App\Enums\OrderStatus;
use App\Enums\OrderTier;
use App\Jobs\SendWhatsAppUpdate;
use App\Jobs\SyncOrderToHubSpot;
use App\Models\Destination;
use App\Models\Order;
use App\Models\OrderEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Apply-intake + order-creation + stage-gate transitions.
 *
 * Composes the already-written domain services:
 *   - EligibilityService::apply()  -> stores intake axes + sets the lane (never overwrites
 *                                     an agent decision).
 *   - PricingService::tiers()      -> resolves the chosen tier's service/govt/total for the
 *                                     standard (fixed-fee) lane.
 *
 * Stage-gate rules are ported from docs/superpowers/port/02-domain-logic.md:
 *   - section 1.4  eligibility gate (a non-cleared manual_review/referred order may rest at
 *                  `paid` but cannot advance past it).
 *   - section 3    stage gates (entry requirements per target stage).
 *   - section 4    QA gate (sole authority on the `-> submitted` transition).
 *
 * EmailService is intentionally NOT hard-wired. transition() exposes an optional injected
 * dependency + a hook comment so the email side-effect can be added without coupling this
 * service to it (mirrors WP's "email hook fires at priority 12, after the gates").
 */
final class OrderService
{
    public function __construct(
        private readonly EligibilityService $eligibility,
        private readonly PricingService $pricing,
        // EmailService is autowired by the container (required so the hook always fires;
        // a nullable default would resolve to null and silently skip emails). transition()
        // invokes it via the onStageChange() hook below.
        private readonly EmailService $emailer,
        // Loyalty (L2.7 / #177): returning-customer discount on the standard lane. Autowired.
        private readonly LoyaltyService $loyalty,
    ) {}

    // -----------------------------------------------------------------------------------
    // Intake -> Order
    // -----------------------------------------------------------------------------------

    /**
     * Validate (already done by ApplyRequest) + persist a new order from apply-form intake.
     *
     * Flow:
     *   1. resolve destination (id or slug or display name) -> destination_id + name snapshot.
     *   2. seed customer + intake fields.
     *   3. EligibilityService::apply() stores the axes and computes the lane.
     *   4. STANDARD lane -> price the chosen tier (service/govt/total), status `paid`
     *                       (entry stage; ready for checkout).
     *      MANUAL_REVIEW   -> NO fixed charge (quote path); status `paid` entry stage too
     *                       (the eligibility gate keeps it parked there until an agent clears
     *                       it — doc section 1.4), tier/fees left null.
     *   5. append the opening journey event.
     *
     * @param  array<string, mixed>  $data  intake payload (keys mirror ApplyRequest output)
     */
    public function createFromIntake(array $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $destination = $this->resolveDestination($data['destination'] ?? null);

            $order = new Order;

            // --- Customer / contact ---
            $order->name = $this->clean($data['applicant_name'] ?? null);
            $order->applicant_name = $this->clean($data['applicant_name'] ?? null);
            $order->guardian_name = $this->clean($data['guardian_name'] ?? null);
            $order->email = $this->clean($data['email'] ?? null);
            // Phone is persisted to its own column (M-4) AND retained in the opening event
            // meta below, so agents can find a callback number without digging the event log.
            $order->phone = $this->clean($data['phone'] ?? null);

            // --- Destination snapshot (id + display-name snapshot, per MEMORY convention) ---
            $order->destination_id = $destination?->getKey();
            $order->destination_name = $destination?->name ?? $this->clean($data['destination'] ?? null);

            // --- Trip ---
            $order->travel_date = $this->date($data['travel_date'] ?? null);
            $order->passport_expiry = $this->date($data['passport_expiry'] ?? null);

            // CCRs 2013 (reg 36): durable, timestamped record of the customer's express request
            // to begin the service within the 14-day cancellation window. Without it we cannot
            // lawfully start work before the 14 days elapse; with it, the right to cancel is lost
            // once the service is fully performed.
            if (! empty($data['begin_now'])) {
                $order->immediate_performance_consent_at = Carbon::now();
            }

            // Persist so the order has an id + order_ref (booted::creating) before we attach
            // events and let EligibilityService read the related destination.
            $order->status = OrderStatus::Paid->value; // entry stage for both lanes
            $order->save();

            // --- Eligibility axes + lane (delegated; never overwrites an agent decision) ---
            $this->eligibility->apply($order, [
                'nationality' => $data['nationality'] ?? null,
                'residence_country' => $data['residence_country'] ?? null,
                'residency_status' => $data['residency_status'] ?? null,
                'trip_purpose' => $data['trip_purpose'] ?? null,
                'prior_refusal' => $data['prior_refusal'] ?? false,
                'is_minor' => $data['is_minor'] ?? false,
                // captured-only axes (stored, not routed on)
                'visa_entries' => $data['visa_entries'] ?? null,
                'dual_nationality' => $data['dual_nationality'] ?? null,
            ]);

            // --- Lane-specific pricing ---
            $loyaltyDiscount = 0.0;
            if ($order->eligibility === EligibilityLane::Standard) {
                $this->applyTierPricing($order, $destination, $data['tier'] ?? null);

                // Returning-customer loyalty discount (#83): reduce service_fee + total by a
                // fixed/percent reward, mint an auditable `loyal` Discount row. Guarded — a
                // first-time customer (no prior order with this email) gets nothing. Only the
                // fixed-fee standard lane is discounted (manual_review has no fixed charge).
                $loyaltyDiscount = $this->loyalty->applyReturningCustomerDiscount($order);
            }
            // manual_review: NO fixed charge. Fees stay null; an agent issues a bespoke quote
            // via PricingService::bespokeQuote() later.

            $order->save();

            // --- Opening journey event ---
            $lane = $order->eligibility instanceof EligibilityLane
                ? $order->eligibility->value
                : (string) $order->eligibility;

            $this->recordEvent(
                $order,
                EventType::System,
                "Order created from apply intake (lane: {$lane}).",
                channel: EventChannel::Internal,
                meta: [
                    'lane' => $lane,
                    'destination' => $order->destination_name,
                    'tier' => $order->tier instanceof OrderTier ? $order->tier->value : $order->tier,
                    'phone' => $this->clean($data['phone'] ?? null),
                    'consent' => (bool) ($data['consent'] ?? false),
                    'source' => 'apply_form',
                ],
            );

            // --- Loyalty discount audit note (only when one was actually applied) ---
            if ($loyaltyDiscount > 0.0) {
                $this->recordEvent(
                    $order,
                    EventType::System,
                    'Returning-customer loyalty discount applied: -£'
                        .number_format($loyaltyDiscount, 2)
                        ." (service fee now £".number_format((float) $order->service_fee, 2).').',
                    channel: EventChannel::Internal,
                    meta: [
                        'loyalty_discount' => $loyaltyDiscount,
                        'service_fee' => (float) $order->service_fee,
                        'total' => (float) $order->total,
                    ],
                );
            }

            // CRM sync (after the transaction commits so HubSpot never sees a rolled-back order).
            SyncOrderToHubSpot::dispatch($order)->afterCommit();

            return $order;
        });
    }

    // -----------------------------------------------------------------------------------
    // Stage-gate transition
    // -----------------------------------------------------------------------------------

    /**
     * Allowed pipeline order. A move is legal only to the immediate next stage, to one of the
     * terminal branches reachable from the current stage, or staying put (no-op rejected).
     * from: UKV_ORDER_STATUSES + the terminal branches in doc section 3.1.
     *
     * @var array<string, list<OrderStatus>>
     */
    private const ALLOWED = [
        'paid' => [OrderStatus::AwaitingDocs, OrderStatus::Refunded],
        'awaiting_docs' => [OrderStatus::DocReview, OrderStatus::Refunded],
        'doc_review' => [OrderStatus::Submitted, OrderStatus::Refunded],
        'submitted' => [OrderStatus::AwaitingDecision, OrderStatus::Refunded],
        'awaiting_decision' => [OrderStatus::Delivered, OrderStatus::Rejected, OrderStatus::Refunded],
        'delivered' => [OrderStatus::Won, OrderStatus::Rejected, OrderStatus::Refunded],
        // terminal
        'won' => [],
        'rejected' => [],
        'refunded' => [],
    ];

    /**
     * Move an order to a new status, enforcing every stage gate from the doc.
     *
     * Enforcement order mirrors the WP hook priorities (doc section 3.3):
     *   1. legal transition?            (pipeline adjacency)
     *   2. eligibility gate             (section 1.4 — non-cleared can't pass `paid`)
     *   3. QA gate                      (section 4 — sole authority on `-> submitted`)
     *   4. stage entry requirements     (section 3.2)
     * On success: write status_last, set status, record a stage_change event, then fire the
     * email hook (priority 12 — only ever on a real, allowed transition).
     *
     * @throws \DomainException when a gate blocks the move (caller decides how to surface it).
     */
    public function transition(Order $order, OrderStatus $to): void
    {
        $from = $order->status instanceof OrderStatus
            ? $order->status
            : OrderStatus::from((string) $order->status);

        if ($from === $to) {
            throw new \DomainException("Order {$order->order_ref} is already '{$to->value}'.");
        }

        // 1. Pipeline adjacency.
        $allowed = self::ALLOWED[$from->value] ?? [];
        if (! in_array($to, $allowed, true)) {
            throw new \DomainException(
                "Illegal transition {$from->value} -> {$to->value} for order {$order->order_ref}."
            );
        }

        // 2. Eligibility gate: a non-cleared (manual_review/referred) order may rest at `paid`
        //    but cannot advance past it. (standard/cleared are never blocked.)
        if (! $this->eligibility->canAdvancePastPaid($order, $to)) {
            throw new \DomainException(
                "Order {$order->order_ref} blocked: eligibility not cleared (lane must be standard or cleared to pass 'paid')."
            );
        }

        // 3. QA gate owns the `-> submitted` transition.
        if ($to === OrderStatus::Submitted) {
            $qa = $this->qaCanSubmit($order);
            if (! $qa['ok']) {
                throw new \DomainException(
                    "Submission blocked by QA gate: ".implode(' ', $qa['reasons'])
                );
            }
        } else {
            // 4. Stage entry requirements for every other target.
            $check = $this->stageEntryOk($order, $to);
            if (! $check['ok']) {
                throw new \DomainException(
                    "Stage move to {$to->value} blocked: ".implode(' ', $check['reasons'])
                );
            }
        }

        // --- Commit the move ---
        $order->status_last = $from->value;
        $order->status = $to->value;
        if ($to->isClosed() && $order->closed_at === null) {
            $order->closed_at = Carbon::now(); // starts the retention clock (doc section: closed set)
        }
        $order->save();

        $this->recordEvent(
            $order,
            EventType::StageChange,
            "Status changed: {$from->value} -> {$to->value}.",
            channel: EventChannel::Internal,
            meta: ['from' => $from->value, 'to' => $to->value],
        );

        // --- Email hook (WP priority 12: only on a real, allowed transition) ---
        // EmailService should fire here — e.g. a stage-change customer email keyed by $to.
        // It is invoked through the optional injected dependency rather than a hard `new`/
        // facade call, so this service stays decoupled and unit-testable. If no emailer is
        // bound, the transition silently completes (matches "no email on a reverted status").
        $this->emailer->onStageChange($order, $from, $to);

        // CRM sync on every status change.
        SyncOrderToHubSpot::dispatch($order, "Status: {$from->value} -> {$to->value}")->afterCommit();

        // WhatsApp customer notification (queued; guarded no-op without creds/phone, idempotent).
        SendWhatsAppUpdate::dispatch($order, $from->value, $to->value)->afterCommit();
    }

    /**
     * Process a refund: record the refund fields, then transition to `refunded`
     * (which records the journey event and fires the refund email via onStageChange).
     * #178 — refund / cancellation flow.
     */
    public function refund(Order $order, float $amount, ?string $reason = null): void
    {
        // One transaction: if transition() throws (e.g. refunding a terminal order), the
        // refund-field writes roll back too — no orphaned refund_amount on a non-refunded order.
        DB::transaction(function () use ($order, $amount, $reason): void {
            $order->refund_amount = $amount;
            $order->refund_reason = $this->clean($reason);
            $order->refunded_at = Carbon::now();
            $order->save();

            $this->transition($order, OrderStatus::Refunded);
        });
    }

    // -----------------------------------------------------------------------------------
    // Gates (ported, read-only helpers)
    // -----------------------------------------------------------------------------------

    /**
     * QA gate (doc section 4). Order may be submitted iff documents are complete AND the
     * human QA sign-off is recorded.
     *
     * Document completeness uses the synced per-destination required count
     * (`required_docs_count`); when >0 we need >= that many uploaded docs, else the floor is
     * at least one uploaded document.
     *
     * @return array{ok: bool, reasons: list<string>}
     */
    public function qaCanSubmit(Order $order): array
    {
        $reasons = [];

        $have = $this->uploadedDocCount($order);
        $need = (int) ($order->required_docs_count ?? 0);
        $floor = $need > 0 ? $need : 1;

        if ($have < $floor) {
            $reasons[] = $have === 0
                ? 'No documents attached.'
                : "Only {$have} of {$floor} required document(s) attached.";
        }

        if (! (bool) $order->qa_signed_off) {
            $reasons[] = 'QA sign-off not recorded.';
        }

        return ['ok' => $reasons === [], 'reasons' => $reasons];
    }

    /**
     * Stage entry requirements (doc section 3.2). A target absent from the map is always
     * enterable. `submitted` is delegated to the QA gate and is never evaluated here.
     *
     * @return array{ok: bool, reasons: list<string>}
     */
    private function stageEntryOk(Order $order, OrderStatus $to): array
    {
        $reasons = [];

        switch ($to) {
            case OrderStatus::DocReview:
                if ($this->uploadedDocCount($order) < 1) {
                    $reasons[] = 'At least one document must be uploaded before doc review.';
                }
                break;

            case OrderStatus::AwaitingDecision:
            case OrderStatus::Delivered:
                if ($this->clean($order->govt_ref) === null) {
                    $reasons[] = 'A government reference is required (order must have been submitted to the government).';
                }
                break;

            default:
                // awaiting_docs, won, rejected, refunded, paid -> no entry criteria.
                break;
        }

        return ['ok' => $reasons === [], 'reasons' => $reasons];
    }

    // -----------------------------------------------------------------------------------
    // Event log
    // -----------------------------------------------------------------------------------

    /**
     * Append a journey/audit event consistently. Non-email events leave `email_event` null
     * (the unique (order_id, email_event) guard only applies to once-only email sends, and
     * MySQL permits many NULLs in a unique index).
     *
     * @param  array<string, mixed>|null  $meta
     */
    public function recordEvent(
        Order $order,
        EventType $type,
        string $text,
        EventChannel $channel = EventChannel::Internal,
        string $agent = 'system',
        ?array $meta = null,
        ?string $emailEvent = null,
    ): OrderEvent {
        return $order->events()->create([
            'occurred_at' => Carbon::now(),
            'agent' => $agent,
            'channel' => $channel->value,
            'type' => $type->value,
            'text' => $text,
            'meta' => $meta,
            'email_event' => $emailEvent,
        ]);
    }

    // -----------------------------------------------------------------------------------
    // Internal helpers
    // -----------------------------------------------------------------------------------

    /**
     * Resolve a destination reference to a model.
     * Accepts: numeric id, slug, or display name (the apply form posts the display name).
     */
    private function resolveDestination(mixed $ref): ?Destination
    {
        if ($ref === null || $ref === '') {
            return null;
        }

        if (is_numeric($ref)) {
            return Destination::query()->find((int) $ref);
        }

        $value = trim((string) $ref);

        // Try slug first (exact), then a slugified match, then exact display name.
        return Destination::query()->where('slug', $value)->first()
            ?? Destination::query()->where('slug', Str::slug($value))->first()
            ?? Destination::query()->where('name', $value)->first();
    }

    /**
     * Price the chosen tier from the destination's own tier fields (PricingService::tiers()).
     * Falls back to Standard when the requested tier is missing/unknown.
     */
    private function applyTierPricing(Order $order, ?Destination $destination, mixed $tier): void
    {
        if ($destination === null) {
            // No destination resolved -> cannot price; leave fees null, default the tier label.
            $order->tier = $this->tierEnum($tier)?->value;

            return;
        }

        $tiers = $this->pricing->tiers($destination);
        $key = $this->tierEnum($tier)?->value ?? OrderTier::Standard->value;
        $chosen = $tiers[$key] ?? $tiers[OrderTier::Standard->value];

        $order->tier = $chosen['tier']->value;
        $order->service_fee = $chosen['service_fee'];
        $order->govt_fee = $chosen['govt_fee'];
        $order->total = $chosen['total'];
    }

    private function tierEnum(mixed $tier): ?OrderTier
    {
        if ($tier instanceof OrderTier) {
            return $tier;
        }

        if ($tier === null || $tier === '') {
            return null;
        }

        return OrderTier::tryFrom(strtolower(trim((string) $tier)));
    }

    /** Count actually-uploaded documents on the order's documents relation. */
    private function uploadedDocCount(Order $order): int
    {
        return $order->documents()->count();
    }

    private function clean(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $clean = trim((string) $value);

        return $clean === '' ? null : $clean;
    }

    private function date(mixed $value): ?Carbon
    {
        $clean = $this->clean($value);
        if ($clean === null) {
            return null;
        }

        try {
            return Carbon::parse($clean);
        } catch (\Throwable) {
            return null;
        }
    }
}
