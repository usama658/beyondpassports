<?php

namespace App\Enums;

/**
 * Trip purpose.
 * from: ukv_trip_purpose (UKV_TRIP_PURPOSE); default 'tourist'.
 */
enum TripPurpose: string
{
    case Tourist = 'tourist';
    case Business = 'business';
    case Transit = 'transit';
    case Study = 'study';
    case Other = 'other';
}
