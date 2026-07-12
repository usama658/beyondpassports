<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Enums\UserRole;

/**
 * Hides a Filament resource from the CMS Editor role. The Editor is scoped to the Content group
 * (Pages, Articles/Guides, later Media/Settings/Nav) and must never reach orders, customers,
 * payments, or operational resources. Applied to every non-content resource.
 */
trait HiddenFromEditor
{
    public static function canAccess(): bool
    {
        return auth()->user()?->role !== UserRole::Editor;
    }
}
