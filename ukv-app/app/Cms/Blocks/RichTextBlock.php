<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\RichEditor;

class RichTextBlock implements BlockType
{
    public static function key(): string
    {
        return 'rich-text';
    }

    public static function label(): string
    {
        return 'Rich text';
    }

    public static function schema(): array
    {
        return [
            RichEditor::make('body')
                ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList', 'h2', 'h3'])
                ->required(),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.rich-text';
    }
}
