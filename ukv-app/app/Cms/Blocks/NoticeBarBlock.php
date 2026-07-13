<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

/**
 * Notice bar. A thin, full-width announcement strip (distinct from the boxed Callout) for a single
 * line, e.g. a seasonal note or a deadline. Self-contained + scoped. Editable: tone, text, optional
 * inline link.
 */
class NoticeBarBlock implements BlockType
{
    public static function key(): string
    {
        return 'notice-bar';
    }

    public static function label(): string
    {
        return 'Notice bar (announcement strip)';
    }

    public static function schema(): array
    {
        return [
            Select::make('tone')
                ->options(['brand' => 'Brand (teal)', 'dark' => 'Dark (ink)', 'warning' => 'Warning (amber)'])
                ->default('brand')->required(),
            TextInput::make('text')->required()->maxLength(160),
            TextInput::make('link_label')->maxLength(40),
            TextInput::make('link_url')->maxLength(200)
                ->helperText('Optional. Both link fields must be set for the link to show.'),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.notice-bar';
    }
}
