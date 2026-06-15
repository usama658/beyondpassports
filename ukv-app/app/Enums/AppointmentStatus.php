<?php

namespace App\Enums;

/**
 * Appointment booking lifecycle.
 * from: ukv_appointment_status (UKV_APPOINTMENT_STATUSES).
 */
enum AppointmentStatus: string
{
    case NotRequired = 'not_required';
    case ToBook = 'to_book';
    case Booked = 'booked';
    case Attended = 'attended';
    case Completed = 'completed';
}
