<?php

namespace App\Enums;

/**
 * Order-event channel.
 * from: ukv_journey[].channel.
 */
enum EventChannel: string
{
    case Call = 'call';
    case Whatsapp = 'whatsapp';
    case Email = 'email';
    case Internal = 'internal';
    case Upload = 'upload';
}
