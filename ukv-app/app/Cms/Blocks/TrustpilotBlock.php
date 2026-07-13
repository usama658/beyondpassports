<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Select;

/**
 * Trustpilot widget. Renders the existing themed trustpilot-cta partial (rating chip from config,
 * consent-gated like everywhere else). The team can place it and pick theme + alignment; the rating
 * and behaviour stay in code — a "toggle it on here" block, not editable content.
 */
class TrustpilotBlock implements BlockType
{
    public static function key(): string
    {
        return 'trustpilot';
    }

    public static function label(): string
    {
        return 'Trustpilot rating';
    }

    public static function schema(): array
    {
        return [
            Select::make('theme')->options(['light' => 'Light', 'dark' => 'Dark'])->default('light'),
            Select::make('align')->options(['left' => 'Left', 'center' => 'Centre', 'right' => 'Right'])->default('center'),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.trustpilot';
    }
}
