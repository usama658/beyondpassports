<?php

namespace App\Enums;

/**
 * Barrier scope.
 * from: barrier meta `scope` (UKV_BARRIER_SCOPE).
 */
enum BarrierScope: string
{
    case Case_ = 'case';
    case Destination = 'destination';
    case All = 'all';
}
