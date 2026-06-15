<?php

namespace App\Enums;

/**
 * Barrier status.
 * from: barrier meta `status` — WP only ever set 'open'; 'resolved' added in the port.
 */
enum BarrierStatus: string
{
    case Open = 'open';
    case Resolved = 'resolved';
}
