<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Select;

/**
 * Locked include block: renders an existing, whitelisted Blade partial verbatim (config-driven or
 * interactive sections that must stay in code). The editor can place/reorder it but cannot edit its
 * internals. The whitelist is the single source of truth for what a locked-include may render.
 */
class LockedIncludeBlock implements BlockType
{
    /** Whitelisted partial keys => Blade partial names. Add entries as sections are extracted. */
    public const PARTIALS = [
        'services-body' => 'partials.services-body',
        'about-body' => 'partials.about-body',
    ];

    public static function key(): string
    {
        return 'locked-include';
    }

    public static function label(): string
    {
        return 'Locked section (from code)';
    }

    public static function schema(): array
    {
        return [
            Select::make('partial')
                ->label('Section')
                ->options([
                    'services-body' => 'Services body (catalogue, how, why, FAQ, CTA)',
                    'about-body' => 'About body (who we are, values, team, reviews, CTA)',
                ])
                ->required(),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.locked-include';
    }
}
