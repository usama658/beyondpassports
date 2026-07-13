<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

/**
 * FAQ accordion. Renders the existing themed .faq-e section (native <details> accordion, styled by
 * ukv.css) so it matches the coded pages. The team edits the heading + a list of question/answer
 * pairs.
 */
class FaqBlock implements BlockType
{
    public static function key(): string
    {
        return 'faq';
    }

    public static function label(): string
    {
        return 'FAQ accordion';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('eyebrow')->default('Questions')->maxLength(40),
            TextInput::make('heading')->required()->maxLength(120),
            Repeater::make('items')
                ->label('Questions')
                ->schema([
                    TextInput::make('q')->label('Question')->required()->maxLength(200),
                    Textarea::make('a')->label('Answer')->required()->rows(2)->maxLength(600),
                ])
                ->reorderable()->collapsible()
                ->itemLabel(fn (array $state) => $state['q'] ?? null)
                ->minItems(1),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.faq';
    }
}
