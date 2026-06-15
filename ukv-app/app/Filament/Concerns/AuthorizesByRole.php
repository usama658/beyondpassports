<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;

/**
 * Role-based write gating for Filament resources (audit H-1).
 *
 * Viewers are read-only everywhere; Admin and Agent keep full write access.
 * `use` this trait in every resource so a Viewer cannot reach create/edit/delete
 * on any record. Read paths (viewAny/view) are intentionally left to the panel's
 * coarse access gate (User::canAccessPanel).
 */
trait AuthorizesByRole
{
    public static function canCreate(): bool
    {
        return ! self::isViewer();
    }

    public static function canEdit(Model $record): bool
    {
        return ! self::isViewer();
    }

    public static function canDelete(Model $record): bool
    {
        return ! self::isViewer();
    }

    public static function canDeleteAny(): bool
    {
        return ! self::isViewer();
    }

    /** True when the authenticated user is a read-only Viewer. */
    protected static function isViewer(): bool
    {
        return auth()->user()?->role === UserRole::Viewer;
    }
}
