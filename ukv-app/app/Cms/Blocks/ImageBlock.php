<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\FileUpload;
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
            FileUpload::make('src')
                ->label('Image')
                ->image()
                ->disk('public')
                ->directory('cms')
                ->maxSize(3072)
                ->imageEditor()
                ->required(),
            TextInput::make('alt')->label('Alt text (describe the image)')->required()->maxLength(160),
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
