<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\PageRenderer;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * Resolves a slug to a published CMS page, or 404s so the existing coded route can own it.
 * Global kill switch: when UKV_CMS_ENABLED is off, the CMS never owns any slug.
 */
class CmsController extends Controller
{
    public function show(string $slug, PageRenderer $renderer): Response
    {
        abort_unless((bool) config('ukv.cms.enabled'), 404);

        $page = Page::where('slug', $slug)->first();
        abort_unless($page && $page->isPublishedCms(), 404);

        $html = Cache::remember('cms:page:'.$page->slug, now()->addHours(6), fn () => $renderer->render($page));

        return response()->view('cms.page', ['page' => $page, 'rendered' => $html]);
    }
}
