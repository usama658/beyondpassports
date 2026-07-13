<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

/**
 * Testimonial quote. A single customer quote with attribution and optional star rating. Self-contained
 * + scoped (brand tokens). Compliance note: only place quotes that are genuine + consented.
 */
class QuoteBlock implements BlockType
{
    public static function key(): string
    {
        return 'quote';
    }

    public static function label(): string
    {
        return 'Testimonial quote';
    }

    public static function schema(): array
    {
        return [
            Textarea::make('quote')->required()->rows(3)->maxLength(400),
            TextInput::make('name')->label('Attribution name')->maxLength(80),
            TextInput::make('detail')->label('Attribution detail')->maxLength(80)
                ->helperText('e.g. "Schengen visa, Germany" or a city.'),
            Select::make('stars')->options([5 => '5 stars', 4 => '4 stars', 3 => '3 stars', 0 => 'No stars'])
                ->default(5),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.quote';
    }
}
