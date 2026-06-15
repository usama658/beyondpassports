<?php

namespace App\Enums;

/**
 * Order-event type — distinguishes a free note from email/gate/system events.
 * from: derived (ukv_journey + ukv_email_log/ukv_email_sent).
 */
enum EventType: string
{
    case Note = 'note';
    case Email = 'email';
    case StageChange = 'stage_change';
    case System = 'system';
}
