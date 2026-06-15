<?php

namespace App\Enums;

/**
 * Canonical order pipeline status.
 * from: ukv_status (UKV_ORDER_STATUSES).
 * Order: paid -> awaiting_docs -> doc_review -> submitted -> awaiting_decision -> delivered -> won
 *                                                                                       \-> rejected
 *                                                                                       \-> refunded
 */
enum OrderStatus: string
{
    case Paid = 'paid';
    case AwaitingDocs = 'awaiting_docs';
    case DocReview = 'doc_review';
    case Submitted = 'submitted';
    case AwaitingDecision = 'awaiting_decision';
    case Delivered = 'delivered';
    case Won = 'won';
    case Rejected = 'rejected';
    case Refunded = 'refunded';

    /** Closed set (UKV_ORDER_CLOSED) — drives barriers/retention/SLA. App constant, not a DB enum subset. */
    public const CLOSED = [self::Delivered, self::Won, self::Rejected, self::Refunded];

    public function isClosed(): bool
    {
        return in_array($this, self::CLOSED, true);
    }
}
