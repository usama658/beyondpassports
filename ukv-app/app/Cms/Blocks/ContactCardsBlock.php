<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

/**
 * Contact cards. A grid of channel tiles (WhatsApp / email / phone / booking), each with a title, a
 * line of text and an action link. Self-contained + scoped. Editable: optional heading + cards
 * (title, text, button label, button url).
 */
class ContactCardsBlock implements BlockType
{
    public static function key(): string
    {
        return 'contact-cards';
    }

    public static function label(): string
    {
        return 'Contact cards (channel tiles)';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('heading')->maxLength(120),
            Repeater::make('items')
                ->label('Cards')
                ->schema([
                    TextInput::make('title')->required()->maxLength(60),
                    Textarea::make('text')->rows(2)->maxLength(200),
                    TextInput::make('button_label')->maxLength(40),
                    TextInput::make('button_url')->maxLength(200)
                        ->helperText('e.g. a WhatsApp link, mailto:, tel: or /contact.'),
                ])
                ->reorderable()->collapsible()
                ->itemLabel(fn (array $state) => $state['title'] ?? null)
                ->minItems(1)->maxItems(6),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.contact-cards';
    }
}
