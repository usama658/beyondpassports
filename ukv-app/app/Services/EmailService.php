<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EventChannel;
use App\Enums\EventType;
use App\Enums\OrderStatus;
use App\Mail\AppointmentBooked;
use App\Mail\CheckerAbandon;
use App\Mail\DecisionMade;
use App\Mail\Delivered;
use App\Mail\DocsNeeded;
use App\Mail\OrderMailable;
use App\Mail\OrderPaid;
use App\Mail\OrderSubmitted;
use App\Mail\Refunded;
use App\Mail\ReviewRequest;
use App\Models\Order;
use App\Models\OrderEvent;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Lifecycle email dispatcher.
 *
 * Ports the side effects of ukv_email_send() that must live OUTSIDE the Mailable
 * (so a queued Mailable can never double-fire and every attempt is recorded):
 *
 *   1. Idempotency guard — one send per (order, event). Enforced by the unique
 *      (order_id, email_event) index on order_events, plus a pre-check.
 *   2. Audit log + journey note — a single order_events row per event acts as
 *      BOTH the audit log (channel=email, type=email, email_event=<event>) and the
 *      timeline journey note (agent=system, text="Email sent: <event>"). It is
 *      written only after the Mailable is queued, mirroring the WP "record after
 *      attempt" behaviour.
 *   3. Empty-recipient guard — skip (return false) if the order has no email.
 *
 * Each public method = one lifecycle event from docs/superpowers/port/03-emails.md.
 * LIVE events have real triggers to wire into OrderService/status-change.
 * DORMANT events (orderPaid, docsNeeded, appointmentBooked, checkerAbandon) are
 * ported but their triggers are stubs in WP — wire on demand.
 */
final class EmailService
{
    // Canonical event keys (stored in order_events.email_event for idempotency).
    public const EVENT_ORDER_PAID = 'order_paid';
    public const EVENT_DOCS_NEEDED = 'docs_needed';
    public const EVENT_SUBMITTED = 'submitted';
    public const EVENT_DECISION = 'decision';
    public const EVENT_DELIVERED = 'delivered';
    public const EVENT_REVIEW_REQUEST = 'review_request';
    public const EVENT_REFUNDED = 'refunded';
    public const EVENT_APPOINTMENT_BOOKED = 'appointment_booked';
    public const EVENT_CHECKER_ABANDON = 'checker_abandon';

    // --- LIVE events -------------------------------------------------------

    /** submitted — status → submitted. */
    public function sendSubmitted(Order $order): bool
    {
        return $this->dispatch($order, self::EVENT_SUBMITTED, new OrderSubmitted($order));
    }

    /** decision — status → awaiting_decision. */
    public function sendDecision(Order $order): bool
    {
        return $this->dispatch($order, self::EVENT_DECISION, new DecisionMade($order));
    }

    /** delivered — status → delivered|won. */
    public function sendDelivered(Order $order): bool
    {
        return $this->dispatch($order, self::EVENT_DELIVERED, new Delivered($order));
    }

    /** review_request — fired together with delivered on the same transition. */
    public function sendReviewRequest(Order $order): bool
    {
        return $this->dispatch($order, self::EVENT_REVIEW_REQUEST, new ReviewRequest($order));
    }

    /** refunded — refund processed (status → refunded). */
    public function sendRefunded(Order $order): bool
    {
        return $this->dispatch($order, self::EVENT_REFUNDED, new Refunded($order));
    }

    // --- DORMANT events (templates ported; triggers to be wired) -----------

    /** order_paid — intended: order reaches the `paid` entry stage. */
    public function sendOrderPaid(Order $order): bool
    {
        return $this->dispatch($order, self::EVENT_ORDER_PAID, new OrderPaid($order));
    }

    /**
     * docs_needed — intended: order enters `awaiting_docs`.
     *
     * Embeds a personalised document checklist (RequirementService::for) so the
     * customer sees the exact documents to prepare for THEIR case. The items are
     * plain arrays, so they serialise cleanly through the queued Mailable. If the
     * engine yields nothing, the email still renders fine (checklist section omitted).
     */
    public function sendDocsNeeded(Order $order): bool
    {
        $items = app(RequirementService::class)->for($order);

        return $this->dispatch($order, self::EVENT_DOCS_NEEDED, new DocsNeeded($order, $items));
    }

    /** appointment_booked — intended: an appointment is booked for the order. */
    public function sendAppointmentBooked(Order $order): bool
    {
        return $this->dispatch($order, self::EVENT_APPOINTMENT_BOOKED, new AppointmentBooked($order));
    }

    /**
     * checker_abandon — lead-only (no Order). Captured visa-checker email with no
     * follow-up Apply/order. Idempotency here is per-lead, NOT per-order, so this
     * does NOT write an order_events row (there is no order). Dedupe must be owned
     * by the lead store / cron that calls this (mirrors the WP per-lead guard).
     */
    public function sendCheckerAbandon(string $leadEmail, ?string $name, ?string $dest): bool
    {
        $leadEmail = trim($leadEmail);
        if ($leadEmail === '') {
            return false; // empty-recipient guard
        }

        Mail::to($leadEmail)->queue(new CheckerAbandon($name, $dest));

        return true;
    }

    /**
     * Stage-change hook called by OrderService::transition() on every real, allowed move.
     * Maps the target status to its lifecycle email(s). Idempotency + empty-recipient
     * guards live in dispatch(), so this is safe to call on every transition.
     *
     * `paid` is intentionally silent here — it is the entry stage set at creation; the
     * order_paid confirmation is owned by the Stripe webhook (genuine payment), not the
     * pipeline move. doc_review and rejected have no customer email.
     */
    public function onStageChange(Order $order, OrderStatus $from, OrderStatus $to): void
    {
        switch ($to) {
            case OrderStatus::Submitted:
                $this->sendSubmitted($order);
                break;
            case OrderStatus::AwaitingDecision:
                $this->sendDecision($order);
                break;
            case OrderStatus::AwaitingDocs:
                $this->sendDocsNeeded($order);
                break;
            case OrderStatus::Delivered:
            case OrderStatus::Won:
                $this->sendDelivered($order);
                $this->sendReviewRequest($order);
                break;
            case OrderStatus::Refunded:
                $this->sendRefunded($order);
                break;
            default:
                break;
        }
    }

    // --- internals ---------------------------------------------------------

    /**
     * Queue an order Mailable to the order's customer email, once per (order, event),
     * then record the audit/journey order_event.
     *
     * @return bool true if queued; false if skipped (no email / already sent).
     */
    private function dispatch(Order $order, string $event, OrderMailable $mailable): bool
    {
        $to = trim((string) ($order->email ?? ''));
        if ($to === '') {
            return false; // empty-recipient guard (ukv_email_fire returns false)
        }

        // Idempotency pre-check: never resend an event for an order.
        if ($this->alreadySent($order, $event)) {
            return false;
        }

        Mail::to($to)->queue($mailable);

        $this->record($order, $event, $to, $mailable->envelope()->subject ?? $event);

        return true;
    }

    /**
     * Has this event already been sent for this order?
     * (Ports the ukv_email_sent[] membership check.)
     */
    private function alreadySent(Order $order, string $event): bool
    {
        return $order->events()
            ->where('email_event', $event)
            ->exists();
    }

    /**
     * Write the single order_events row that serves as both audit log and journey
     * note. Wrapped to swallow the unique-constraint race (two concurrent workers):
     * the DB unique (order_id, email_event) index is the real once-only guard.
     */
    private function record(Order $order, string $event, string $to, string $subject): void
    {
        try {
            OrderEvent::create([
                'order_id' => $order->getKey(),
                'occurred_at' => Carbon::now(),
                'agent' => 'system',
                'channel' => EventChannel::Email,
                'type' => EventType::Email,
                'text' => "Email sent: {$event}",
                'email_event' => $event,
                'meta' => [
                    'event' => $event,
                    'to' => $to,
                    'subject' => $subject,
                ],
            ]);
        } catch (QueryException $e) {
            // Duplicate (order_id, email_event) — already recorded by another worker.
            // The mail is queued idempotently elsewhere via alreadySent(); log and move on.
            Log::warning('Duplicate lifecycle email event suppressed', [
                'order_id' => $order->getKey(),
                'event' => $event,
            ]);
        }
    }
}
