<?php

namespace App\Enums;

/**
 * How a barrier was detected.
 * from: barrier meta `detected_by`.
 */
enum BarrierDetectedBy: string
{
    case Agent = 'agent';
    case Auto = 'auto';
}
