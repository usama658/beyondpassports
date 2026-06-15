<?php

namespace App\Enums;

/**
 * Order priority.
 * from: ukv_priority.
 */
enum OrderPriority: string
{
    case Normal = 'normal';
    case High = 'high';
    case Urgent = 'urgent';
}
