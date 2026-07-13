<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

/**
 * Call-to-action band. Renders the existing themed .cta-band section (petrol gradient panel) so it
 * matches the coded pages exactly; the team edits only the words + the primary button. The WhatsApp
 * consult CTA is included from code, unchanged.
 */
class CtaBandBlock implements BlockType
{
    public static function key(): string
    {
        return 'cta-band';
    }

    public static function label(): string
    {
        return 'Call-to-action band';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('heading')->required()->maxLength(120),
            Textarea::make('subtext')->rows(2)->maxLength(240),
            TextInput::make('button_label')->maxLength(60)
                ->helperText('Leave blank to hide the primary button (the WhatsApp CTA still shows).'),
            TextInput::make('button_url')->maxLength(300)
                ->helperText('A path like /apply or a full https:// URL.'),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.cta-band';
    }
}
