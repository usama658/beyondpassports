<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Page;
use App\Models\PageRevision;

/**
 * Page content history: snapshot before each save (keep last 10), revert to any snapshot.
 * Team safety net so an edit can always be rolled back.
 */
class PagePublisher
{
    public function snapshot(Page $page, ?int $editorId): PageRevision
    {
        $rev = $page->revisions()->create([
            'title' => $page->title,
            'blocks' => $page->blocks,
            'seo_title' => $page->seo_title,
            'seo_description' => $page->seo_description,
            'og_image' => $page->og_image,
            'editor_id' => $editorId,
        ]);

        // Trim to the latest 10 revisions.
        $page->revisions()->latest()->skip(10)->take(PHP_INT_MAX)->get()->each->delete();

        return $rev;
    }

    public function revertTo(Page $page, PageRevision $rev): void
    {
        $page->update([
            'title' => $rev->title,
            'blocks' => $rev->blocks,
            'seo_title' => $rev->seo_title,
            'seo_description' => $rev->seo_description,
            'og_image' => $rev->og_image,
        ]);
    }
}
