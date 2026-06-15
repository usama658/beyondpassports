<?php

namespace App\Enums;

/**
 * Operator role.
 * from: WP capability — "eligible owner" = can edit orders.
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Agent = 'agent';
    case Viewer = 'viewer';
}
