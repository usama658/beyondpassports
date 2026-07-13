<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use App\Models\Media;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

/**
 * Logo strip. A muted row of partner / press / "as seen on" logos. Self-contained + scoped; logos
 * render greyscale and lift on hover. Each logo may link out. Editable: optional heading + logos
 * (image, name/alt, optional link).
 */
class LogoStripBlock implements BlockType
{
    public static function key(): string
    {
        return 'logo-strip';
    }

    public static function label(): string
    {
        return 'Logo strip (partners / press)';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('heading')->maxLength(120)->helperText('Optional, e.g. "As featured in".'),
            Repeater::make('items')
                ->label('Logos')
                ->schema([
                    Select::make('media_id')
                        ->label('From media library')
                        ->options(fn () => Media::query()->orderByDesc('id')->get()
                            ->mapWithKeys(fn (Media $m) => [$m->id => $m->label()])->all())
                        ->searchable(),
                    FileUpload::make('src')->label('Or upload a one-off')->image()
                        ->disk('public')->directory('cms')->maxSize(1024),
                    TextInput::make('name')->label('Name / alt text')->required()->maxLength(80),
                    TextInput::make('url')->label('Link (optional)')->maxLength(200),
                ])
                ->reorderable()->collapsible()
                ->itemLabel(fn (array $state) => $state['name'] ?? null)
                ->minItems(1)->maxItems(12),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.logo-strip';
    }
}
