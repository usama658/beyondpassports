<?php

namespace App\Enums;

/**
 * Rejection reason.
 * from: ukv_rejection_reason (UKV_REJECTION_REASONS).
 */
enum RejectionReason: string
{
    case DocQuality = 'doc_quality';
    case Eligibility = 'eligibility';
    case PassportValidity = 'passport_validity';
    case PortalError = 'portal_error';
    case CustomerWithdrew = 'customer_withdrew';
    case Other = 'other';
}
