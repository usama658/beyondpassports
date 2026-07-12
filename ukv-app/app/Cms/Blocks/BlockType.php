<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

/**
 * A CMS block type. Each block = a Filament schema (admin fields) + a Blade partial that renders
 * the EXISTING themed section on the public site. Adding a block never touches existing blocks.
 */
interface BlockType
{
    /** Stable machine key stored in pages.blocks[].type. Never rename once data exists. */
    public static function key(): string;

    /** Human label in the builder palette. */
    public static function label(): string;

    /** Filament form components for this block's fields. */
    public static function schema(): array;

    /** Blade partial name (dot notation) that renders this block on the public site. */
    public static function view(): string;
}
