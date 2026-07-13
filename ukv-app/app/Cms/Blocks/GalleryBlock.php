<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use App\Models\Media;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

/**
 * Image gallery. A responsive grid of lazy-loaded images, each from the media library or a one-off
 * upload (same resolution rules as the Image block). Self-contained + scoped. Editable: optional
 * heading + image tiles (image, alt, optional caption).
 */
class GalleryBlock implements BlockType
{
    public static function key(): string
    {
        return 'gallery';
    }

    public static function label(): string
    {
        return 'Gallery (image grid)';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('heading')->maxLength(120),
            Repeater::make('items')
                ->label('Images')
                ->schema([
                    Select::make('media_id')
                        ->label('From media library')
                        ->options(fn () => Media::query()->orderByDesc('id')->get()
                            ->mapWithKeys(fn (Media $m) => [$m->id => $m->label()])->all())
                        ->searchable(),
                    FileUpload::make('src')->label('Or upload a one-off')->image()
                        ->disk('public')->directory('cms')->maxSize(3072)->imageEditor(),
                    TextInput::make('alt')->label('Alt text')->maxLength(160),
                    TextInput::make('caption')->maxLength(160),
                ])
                ->reorderable()->collapsible()
                ->itemLabel(fn (array $state) => $state['alt'] ?? $state['caption'] ?? null)
                ->minItems(1)->maxItems(12),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.gallery';
    }
}
