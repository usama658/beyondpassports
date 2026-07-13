<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;

/**
 * Trust bar. Renders the themed .tbar-f strip (dark mesh band of trust items) used across the home,
 * services, about and destination pages. Each item shows a check icon + a bold lead word + rest of
 * the phrase; the team edits the items, the icon + styling stay in code.
 */
class TrustBarBlock implements BlockType
{
    public static function key(): string
    {
        return 'trust-bar';
    }

    public static function label(): string
    {
        return 'Trust bar';
    }

    public static function schema(): array
    {
        return [
            Repeater::make('items')
                ->label('Trust items')
                ->schema([
                    TextInput::make('bold')->label('Bold lead')->required()->maxLength(40)
                        ->helperText('The emphasised start, e.g. "No hidden".'),
                    TextInput::make('rest')->label('Rest')->maxLength(40)
                        ->helperText('The remainder, e.g. "fees".'),
                ])
                ->reorderable()->collapsible()
                ->itemLabel(fn (array $state) => trim(($state['bold'] ?? '').' '.($state['rest'] ?? '')) ?: null)
                ->minItems(1)->maxItems(6)
                ->grid(2),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.trust-bar';
    }
}
