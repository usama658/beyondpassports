<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Select;

/**
 * Divider. Vertical breathing space between sections, optionally with a hairline rule or a small
 * centred dot motif. Self-contained + scoped, no editable text. Editable: size (space) + style.
 */
class DividerBlock implements BlockType
{
    public static function key(): string
    {
        return 'divider';
    }

    public static function label(): string
    {
        return 'Divider / spacer';
    }

    public static function schema(): array
    {
        return [
            Select::make('size')->options(['s' => 'Small', 'm' => 'Medium', 'l' => 'Large'])
                ->default('m')->required(),
            Select::make('style')->options(['space' => 'Blank space', 'line' => 'Hairline rule', 'dots' => 'Centred dots'])
                ->default('space')->required(),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.divider';
    }
}
