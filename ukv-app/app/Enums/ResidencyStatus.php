<?php

namespace App\Enums;

/**
 * Applicant residency status.
 * from: ukv_residency_status (UKV_RESIDENCY_STATUS).
 */
enum ResidencyStatus: string
{
    case Citizen = 'citizen';
    case Permanent = 'permanent';
    case VisaHolder = 'visa_holder';
    case Other = 'other';
}
