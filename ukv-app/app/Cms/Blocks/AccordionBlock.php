<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

/**
 * Generic collapsible content. A stack of expandable rows for "more detail" sections that are not
 * strictly Q/A (use FAQ for questions, which also emits FAQ schema.org). Self-contained + scoped so
 * it is safe on any CMS page. Editable: optional heading + ordered rows (title + body).
 */
class AccordionBlock implements BlockType
{
    public static function key(): string
    {
        return 'accordion';
    }

    public static function label(): string
    {
        return 'Accordion (collapsible rows)';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('heading')->maxLength(120),
            Repeater::make('items')
                ->label('Rows')
                ->schema([
                    TextInput::make('title')->required()->maxLength(120),
                    Textarea::make('body')->rows(3)->maxLength(600),
                ])
                ->reorderable()->collapsible()
                ->itemLabel(fn (array $state) => $state['title'] ?? null)
                ->minItems(1)->maxItems(12),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.accordion';
    }
}
