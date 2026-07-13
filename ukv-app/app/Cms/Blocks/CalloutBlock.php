<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

/**
 * Highlighted notice box. A single tone-coloured panel for a key point, tip, or caution. Self-contained
 * + scoped so it is safe anywhere. Editable: tone (info/success/warning), title, body, optional button.
 */
class CalloutBlock implements BlockType
{
    public static function key(): string
    {
        return 'callout';
    }

    public static function label(): string
    {
        return 'Callout (notice box)';
    }

    public static function schema(): array
    {
        return [
            Select::make('tone')
                ->options(['info' => 'Info (teal)', 'success' => 'Success (green)', 'warning' => 'Warning (amber)'])
                ->default('info')->required(),
            TextInput::make('title')->maxLength(120),
            Textarea::make('body')->rows(3)->maxLength(600)->required(),
            TextInput::make('button_label')->maxLength(40),
            TextInput::make('button_url')->maxLength(200)
                ->helperText('Optional. Leave both button fields empty for a plain notice.'),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.callout';
    }
}
