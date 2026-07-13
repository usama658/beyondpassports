<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;

/**
 * Checklist. A ticked list of points, e.g. "what's included" or "what you'll need". Reads as a list,
 * not steps (Steps) or Q/A (FAQ). Self-contained + scoped. Editable: optional heading + items (text).
 */
class ChecklistBlock implements BlockType
{
    public static function key(): string
    {
        return 'checklist';
    }

    public static function label(): string
    {
        return 'Checklist (ticked list)';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('heading')->maxLength(120),
            Repeater::make('items')
                ->label('Points')
                ->schema([
                    TextInput::make('text')->required()->maxLength(160),
                ])
                ->reorderable()->collapsible()
                ->itemLabel(fn (array $state) => $state['text'] ?? null)
                ->minItems(1)->maxItems(15),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.checklist';
    }
}
