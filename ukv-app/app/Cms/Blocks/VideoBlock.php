<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\TextInput;

/**
 * Responsive video embed. Accepts a YouTube or Vimeo URL and renders a privacy-friendly, responsive
 * iframe (youtube-nocookie). Only those two hosts are honoured; anything else renders nothing, so the
 * block can never inject an arbitrary iframe. Editable: optional heading, video URL, optional caption.
 */
class VideoBlock implements BlockType
{
    public static function key(): string
    {
        return 'video';
    }

    public static function label(): string
    {
        return 'Video (YouTube / Vimeo)';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('heading')->maxLength(120),
            TextInput::make('url')->required()->maxLength(300)
                ->helperText('Paste a YouTube or Vimeo link. Other hosts are ignored.'),
            TextInput::make('caption')->maxLength(160),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.video';
    }
}
