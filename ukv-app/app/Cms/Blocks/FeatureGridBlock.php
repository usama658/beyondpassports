<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

/**
 * Feature grid. A responsive grid of title + text cards (each with a check tick). Self-contained +
 * scoped (brand tokens). The team edits an optional heading + the feature cards.
 */
class FeatureGridBlock implements BlockType
{
    public static function key(): string
    {
        return 'feature-grid';
    }

    public static function label(): string
    {
        return 'Feature grid';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('eyebrow')->maxLength(40),
            TextInput::make('heading')->maxLength(120),
            Repeater::make('items')
                ->label('Features')
                ->schema([
                    TextInput::make('title')->required()->maxLength(80),
                    Textarea::make('text')->rows(2)->maxLength(280),
                ])
                ->reorderable()->collapsible()
                ->itemLabel(fn (array $state) => $state['title'] ?? null)
                ->minItems(1)->maxItems(9)
                ->grid(2),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.feature-grid';
    }
}
