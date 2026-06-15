<?php

namespace App\Enums;

/**
 * Barrier nature.
 * from: barrier meta `nature` (UKV_BARRIER_NATURE).
 */
enum BarrierNature: string
{
    case Temporary = 'temporary';
    case Permanent = 'permanent';
}
