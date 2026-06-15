<?php

namespace App\Enums;

/**
 * Order blocker.
 * from: ukv_blocker (UKV_BLOCKERS).
 */
enum OrderBlocker: string
{
    case None = 'none';
    case DocsMissing = 'docs_missing';
    case PaymentPending = 'payment_pending';
    case Eligibility = 'eligibility';
    case CustomerDeciding = 'customer_deciding';
}
