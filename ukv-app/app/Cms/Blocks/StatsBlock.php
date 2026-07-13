<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;

/**
 * Stats band. A row of big-number metrics (number + label). Self-contained + scoped (brand tokens).
 * The team edits the stats; numbers are shown as typed (no formatting assumptions).
 */
class StatsBlock implements BlockType
{
    public static function key(): string
    {
        return 'stats';
    }

    public static function label(): string
    {
        return 'Stats band';
    }

    public static function schema(): array
    {
        return [
            Repeater::make('items')
                ->label('Stats')
                ->schema([
                    TextInput::make('number')->required()->maxLength(20)
                        ->helperText('Shown as typed, e.g. "98%", "5 days", "2,400+".'),
                    TextInput::make('label')->required()->maxLength(60),
                ])
                ->reorderable()
                ->itemLabel(fn (array $state) => trim(($state['number'] ?? '').' '.($state['label'] ?? '')) ?: null)
                ->minItems(1)->maxItems(5)
                ->grid(2),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.stats';
    }
}
