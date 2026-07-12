<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

/**
 * Editable hero block: eyebrow, title, lede. Renders the shared services-hero partial so the
 * output matches the coded page exactly. Structural parts (the "Start here" card) stay in the partial.
 */
class HeroBlock implements BlockType
{
    public static function key(): string
    {
        return 'hero';
    }

    public static function label(): string
    {
        return 'Hero';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('eyebrow'),
            TextInput::make('title')->required(),
            Textarea::make('lede')->rows(2),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.hero';
    }
}
