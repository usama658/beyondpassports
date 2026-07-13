<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

/**
 * Vertical timeline. A labelled sequence of milestones down a connecting rail (Steps is a card grid;
 * this reads as a journey with a marker per row). Self-contained + scoped so it is safe anywhere.
 * Editable: optional heading + ordered rows (label, title, text).
 */
class TimelineBlock implements BlockType
{
    public static function key(): string
    {
        return 'timeline';
    }

    public static function label(): string
    {
        return 'Timeline (milestones)';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('heading')->maxLength(120),
            Repeater::make('items')
                ->label('Milestones')
                ->schema([
                    TextInput::make('label')->maxLength(40)->helperText('Short marker, e.g. "Day 1" or "Step 2".'),
                    TextInput::make('title')->required()->maxLength(120),
                    Textarea::make('text')->rows(2)->maxLength(300),
                ])
                ->reorderable()->collapsible()
                ->itemLabel(fn (array $state) => $state['title'] ?? null)
                ->minItems(1)->maxItems(12),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.timeline';
    }
}
