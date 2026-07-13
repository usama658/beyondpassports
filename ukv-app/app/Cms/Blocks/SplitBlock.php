<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use App\Models\Media;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;

/**
 * Two-column split: an image on one side, heading + body + optional button on the other. Self-contained
 * + scoped (brand tokens). The image comes from the media library; a flip toggle swaps the sides.
 */
class SplitBlock implements BlockType
{
    public static function key(): string
    {
        return 'split';
    }

    public static function label(): string
    {
        return 'Split (image + text)';
    }

    public static function schema(): array
    {
        return [
            Select::make('media_id')->label('Image (from media library)')
                ->options(fn () => Media::query()->orderByDesc('id')->get()
                    ->mapWithKeys(fn (Media $m) => [$m->id => $m->label()])->all())
                ->searchable(),
            Toggle::make('flip')->label('Image on the right'),
            TextInput::make('eyebrow')->maxLength(40),
            TextInput::make('heading')->required()->maxLength(120),
            Textarea::make('body')->rows(3)->maxLength(600),
            TextInput::make('button_label')->maxLength(60),
            TextInput::make('button_url')->maxLength(300),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.split';
    }
}
