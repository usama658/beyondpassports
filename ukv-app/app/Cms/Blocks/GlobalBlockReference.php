<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

use App\Models\GlobalBlock;
use Filament\Forms\Components\Select;

/**
 * Reference block: places a reusable GlobalBlock on a page by id. The page stores only the reference;
 * the actual content lives once in the global_blocks table, so editing the global block updates every
 * page that references it. Never selectable as a global type itself (no recursion).
 */
class GlobalBlockReference implements BlockType
{
    public static function key(): string
    {
        return 'global';
    }

    public static function label(): string
    {
        return 'Reusable block';
    }

    public static function schema(): array
    {
        return [
            Select::make('global_id')
                ->label('Reusable block')
                ->options(fn () => GlobalBlock::query()->orderBy('name')->pluck('name', 'id')->all())
                ->searchable()
                ->required(),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.global';
    }
}
