<?php

namespace App\Enums;

/**
 * Client-update send channel.
 * from: ukv-client-updates.php send path (wp_mail today).
 */
enum ClientUpdateChannel: string
{
    case Email = 'email';
    case Whatsapp = 'whatsapp';
    case Call = 'call';
}
