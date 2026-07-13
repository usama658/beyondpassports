<?php

declare(strict_types=1);

namespace App\Cms\Blocks;

/**
 * Pricing table. Renders the existing themed pricing partial, driven entirely by config('ukv.pricing')
 * — tiers, whether amounts show, currency, and the honest fee disclaimer all stay in code and remain
 * the single source of truth. A "toggle it on here" block: no editable pricing content, so the
 * business-controlled pricing policy can never be changed from the page editor.
 */
class PricingBlock implements BlockType
{
    public static function key(): string
    {
        return 'pricing';
    }

    public static function label(): string
    {
        return 'Pricing table';
    }

    public static function schema(): array
    {
        return [];
    }

    public static function view(): string
    {
        return 'cms.blocks.pricing';
    }
}
