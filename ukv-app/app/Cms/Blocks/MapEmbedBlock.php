<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\TextInput;

/**
 * Map embed. A responsive Google Maps iframe from a place query or a pasted Google Maps embed URL.
 * Only google.com/maps sources resolve (matching the public frame-src CSP); anything else renders
 * nothing, so an editor can never inject an arbitrary iframe. Editable: optional heading, query/URL.
 */
class MapEmbedBlock implements BlockType
{
    public static function key(): string
    {
        return 'map-embed';
    }

    public static function label(): string
    {
        return 'Map (Google Maps)';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('heading')->maxLength(120),
            TextInput::make('query')->label('Place or address')->required()->maxLength(200)
                ->helperText('A place/address (e.g. "VFS Global London") or a Google Maps embed URL. Only Google Maps is honoured.'),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.map-embed';
    }
}
