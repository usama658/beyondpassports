<?php

namespace App\Enums;

/**
 * Discount issuance context.
 * from: discount record `context` (UKV_LOYALTY/REVIEW).
 */
enum DiscountContext: string
{
    case Loyal = 'loyal';
    case Review = 'review';
    case Code = 'code';
}
