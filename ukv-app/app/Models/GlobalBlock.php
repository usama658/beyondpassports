<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * A reusable block: one named (type + data) record referenced from many pages via a `global` block.
 * Editing it must invalidate the cached HTML of every published CMS page, so a change shows up
 * everywhere. Page count is tiny, so busting all page caches on save is cheap and always correct.
 */
class GlobalBlock extends Model
{
    protected $fillable = ['name', 'type', 'data'];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    protected static function booted(): void
    {
        $bustAllPages = function (): void {
            Page::query()->pluck('slug')->each(fn (string $slug) => Cache::forget('cms:page:'.$slug));
        };
        static::saved($bustAllPages);
        static::deleted($bustAllPages);
    }
}
