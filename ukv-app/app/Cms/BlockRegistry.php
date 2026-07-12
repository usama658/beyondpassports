<?php

declare(strict_types=1);

namespace App\Cms;

use App\Cms\Blocks\BlockType;
use App\Cms\Blocks\HeroBlock;
use App\Cms\Blocks\ImageBlock;
use App\Cms\Blocks\LockedIncludeBlock;
use App\Cms\Blocks\RichTextBlock;
use Filament\Forms\Components\Builder\Block;

/**
 * The CMS extension point. Add a new section by appending its class here (plus a BlockType class
 * and a Blade partial). Never touches existing blocks — each addition is isolated.
 */
class BlockRegistry
{
    /** @var array<int, class-string<BlockType>> */
    private array $types = [
        HeroBlock::class,
        RichTextBlock::class,
        ImageBlock::class,
        LockedIncludeBlock::class,
    ];

    /** @return array<string, class-string<BlockType>> keyed by block key */
    public function all(): array
    {
        $out = [];
        foreach ($this->types as $class) {
            $out[$class::key()] = $class;
        }

        return $out;
    }

    public function view(string $type): ?string
    {
        $class = $this->all()[$type] ?? null;

        return $class ? $class::view() : null;
    }

    /** @return array<int, Block> Filament Builder blocks for the admin form. */
    public function builderBlocks(): array
    {
        return array_map(
            fn (string $class) => Block::make($class::key())->label($class::label())->schema($class::schema()),
            array_values($this->all()),
        );
    }
}
