<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use App\Models\Media;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

/**
 * Image block: an Editor-uploaded image, rendered lazy-loaded. Width/height are optional but
 * recommended (they reserve space and prevent layout shift / CLS). Stored on the public disk;
 * requires `php artisan storage:link`.
 */
class ImageBlock implements BlockType
{
    public static function key(): string
    {
        return 'image';
    }

    public static function label(): string
    {
        return 'Image';
    }

    public static function schema(): array
    {
        return [
            Select::make('media_id')
                ->label('From media library')
                ->options(fn () => Media::query()->orderByDesc('id')->get()
                    ->mapWithKeys(fn (Media $m) => [$m->id => $m->label()])->all())
                ->searchable()
                ->helperText('Reuse an image from the library, or upload a one-off below.'),
            FileUpload::make('src')
                ->label('Or upload a one-off image')
                ->image()
                ->disk('public')
                ->directory('cms')
                ->maxSize(3072)
                ->imageEditor(),
            TextInput::make('alt')->label('Alt text (describe the image)')->maxLength(160)
                ->helperText('Leave blank to use the library image\'s default alt text.'),
            TextInput::make('width')->numeric()->label('Width (px)')
                ->helperText('Optional. Set width and height to reserve space and avoid layout shift.'),
            TextInput::make('height')->numeric()->label('Height (px)'),
            TextInput::make('caption')->maxLength(200),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.image';
    }
}
