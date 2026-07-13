<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

/**
 * Button group. A centred row of call-to-action buttons (a hub-page pattern where one CTA is not
 * enough). Self-contained + scoped. Each button is primary (filled) or secondary (outline). Editable:
 * optional heading + buttons (label, url, style).
 */
class ButtonsBlock implements BlockType
{
    public static function key(): string
    {
        return 'buttons';
    }

    public static function label(): string
    {
        return 'Button group';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('heading')->maxLength(120),
            Repeater::make('items')
                ->label('Buttons')
                ->schema([
                    TextInput::make('label')->required()->maxLength(40),
                    TextInput::make('url')->required()->maxLength(200),
                    Select::make('style')->options(['primary' => 'Primary (filled)', 'secondary' => 'Secondary (outline)'])
                        ->default('primary')->required(),
                ])
                ->reorderable()->collapsible()
                ->itemLabel(fn (array $state) => $state['label'] ?? null)
                ->minItems(1)->maxItems(5),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.buttons';
    }
}
