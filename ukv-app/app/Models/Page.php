<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * A CMS page: a slug + a JSON stack of blocks, rendered by PageRenderer when in cms mode and
 * published. In coded mode (default) the existing Blade route owns the slug — the CMS is inert.
 * Fully reversible: mode/status toggles and the UKV_CMS_ENABLED flag decide, per page and globally.
 */
class Page extends Model
{
    protected $fillable = [
        'slug', 'title', 'mode', 'status', 'blocks',
        'seo_title', 'seo_description', 'og_image', 'noindex', 'in_sitemap', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'noindex' => 'boolean',
            'in_sitemap' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        $bust = fn (Page $page) => Cache::forget('cms:page:'.$page->slug);
        static::saved($bust);
        static::deleted($bust);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->latest();
    }

    /** Single source of truth for "render CMS blocks instead of the coded Blade". */
    public function isPublishedCms(): bool
    {
        return $this->mode === 'cms'
            && $this->status === 'published'
            && ! empty($this->blocks);
    }

    /**
     * Clone this page into a saved DRAFT copy — the "start from a template" flow. The copy is always
     * cms-mode + draft with a unique slug, so it can never collide with the source or go live by
     * accident; the team previews it, edits it, then publishes when ready.
     */
    public function duplicateAsDraft(): self
    {
        $copy = $this->replicate(['published_at']);
        $copy->title = $this->title.' (copy)';
        $copy->slug = self::uniqueSlug($this->slug.'-copy');
        $copy->mode = 'cms';
        $copy->status = 'draft';
        $copy->in_sitemap = false;
        $copy->published_at = null;
        $copy->save();

        return $copy;
    }

    /** First free slug of the form base, base-2, base-3, … so duplicates never clash. */
    public static function uniqueSlug(string $base): string
    {
        $slug = $base;
        $n = 1;
        while (self::where('slug', $slug)->exists()) {
            $n++;
            $slug = $base.'-'.$n;
        }

        return $slug;
    }
}
