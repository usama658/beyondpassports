<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\Textarea;

/**
 * Fine print. A small, muted block of disclaimer / small-print text (e.g. "We are not a government
 * body; the embassy decides."). Self-contained + scoped. Editable: the text only. Useful on a
 * compliance-sensitive site where a plain honest caveat belongs at the foot of a section.
 */
class FinePrintBlock implements BlockType
{
    public static function key(): string
    {
        return 'fine-print';
    }

    public static function label(): string
    {
        return 'Fine print (small disclaimer)';
    }

    public static function schema(): array
    {
        return [
            Textarea::make('text')->rows(3)->required()->maxLength(600)
                ->helperText('Keep it honest and plain. Shown small and muted.'),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.fine-print';
    }
}
