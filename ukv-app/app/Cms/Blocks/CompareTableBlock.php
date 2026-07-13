<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

/**
 * Comparison table. A two-column "us vs them" feature matrix — each row is a feature with a yes/no
 * tick for each column. Self-contained + scoped. Editable: optional heading, the two column labels,
 * and feature rows (label, has_a, has_b). Keep claims honest and substantiable.
 */
class CompareTableBlock implements BlockType
{
    public static function key(): string
    {
        return 'compare-table';
    }

    public static function label(): string
    {
        return 'Comparison table (us vs them)';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('heading')->maxLength(120),
            TextInput::make('col_a')->label('Column A label (yours)')->default('With us')->required()->maxLength(40),
            TextInput::make('col_b')->label('Column B label (the others)')->default('Elsewhere')->required()->maxLength(40),
            Repeater::make('items')
                ->label('Feature rows')
                ->schema([
                    TextInput::make('label')->label('Feature')->required()->maxLength(120),
                    Toggle::make('has_a')->label('Column A has it')->default(true),
                    Toggle::make('has_b')->label('Column B has it')->default(false),
                ])
                ->reorderable()->collapsible()
                ->itemLabel(fn (array $state) => $state['label'] ?? null)
                ->minItems(1)->maxItems(15),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.compare-table';
    }
}
