<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

/**
 * How-it-works steps. A numbered sequence of step cards. Self-contained + scoped (brand tokens) so it
 * drops onto any CMS page without depending on a specific page's CSS. The team edits an optional
 * heading + the ordered steps; numbering is automatic.
 */
class StepsBlock implements BlockType
{
    public static function key(): string
    {
        return 'steps';
    }

    public static function label(): string
    {
        return 'Steps (how it works)';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('eyebrow')->maxLength(40),
            TextInput::make('heading')->maxLength(120),
            Repeater::make('items')
                ->label('Steps')
                ->schema([
                    TextInput::make('title')->required()->maxLength(80),
                    Textarea::make('text')->rows(2)->maxLength(280),
                ])
                ->reorderable()->collapsible()
                ->itemLabel(fn (array $state) => $state['title'] ?? null)
                ->minItems(1)->maxItems(6),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.steps';
    }
}
