<?php

namespace App\Enums;

/**
 * Feedback (review) source.
 * from: (new in port) — review_request email / manual / import.
 */
enum FeedbackSource: string
{
    case ReviewRequest = 'review_request';
    case Manual = 'manual';
    case Import = 'import';
}
