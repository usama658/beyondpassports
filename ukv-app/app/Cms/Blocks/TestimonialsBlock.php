<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

/**
 * Testimonial grid. Several short quotes side by side (the single Quote block is for one hero quote).
 * Self-contained + scoped so it is safe anywhere. Only place genuine, consented testimonials.
 * Editable: optional heading + quote cards (quote, name, optional detail).
 */
class TestimonialsBlock implements BlockType
{
    public static function key(): string
    {
        return 'testimonials';
    }

    public static function label(): string
    {
        return 'Testimonials (quote grid)';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('heading')->maxLength(120),
            Repeater::make('items')
                ->label('Quotes')
                ->schema([
                    Textarea::make('quote')->rows(3)->required()->maxLength(400),
                    TextInput::make('name')->required()->maxLength(80),
                    TextInput::make('detail')->maxLength(80)->helperText('Optional, e.g. "Schengen visa, France".'),
                ])
                ->reorderable()->collapsible()
                ->itemLabel(fn (array $state) => $state['name'] ?? null)
                ->minItems(1)->maxItems(9),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.testimonials';
    }
}
