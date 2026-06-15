<?php

namespace App\Enums;

/**
 * Bespoke quote status.
 * from: ukv_quote_status (UKV_QUOTE_STATUSES); default 'none'.
 */
enum QuoteStatus: string
{
    case None = 'none';
    case Sent = 'sent';
    case Paid = 'paid';
}
