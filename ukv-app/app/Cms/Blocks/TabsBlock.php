<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

/**
 * Tabbed panels. A set of labelled tabs revealing one panel at a time, with no JavaScript (pure-CSS
 * radio inputs). Self-contained + scoped. Editable: optional heading + tabs (label, body). The first
 * tab is selected by default.
 */
class TabsBlock implements BlockType
{
    public static function key(): string
    {
        return 'tabs';
    }

    public static function label(): string
    {
        return 'Tabs (no-JS panels)';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('heading')->maxLength(120),
            Repeater::make('items')
                ->label('Tabs')
                ->schema([
                    TextInput::make('label')->required()->maxLength(40),
                    Textarea::make('body')->rows(4)->required()->maxLength(1200),
                ])
                ->reorderable()->collapsible()
                ->itemLabel(fn (array $state) => $state['label'] ?? null)
                ->minItems(2)->maxItems(6),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.tabs';
    }
}
