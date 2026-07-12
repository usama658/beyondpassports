<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * Resolves a slug to a published CMS page, or 404s so the existing coded route can own it.
 * Global kill switch: when UKV_CMS_ENABLED is off, the CMS never owns any slug.
 */
class CmsController extends Controller
{
    public function show(string $slug): Response
    {
        abort_unless((bool) config('ukv.cms.enabled'), 404);

        $page = Page::where('slug', $slug)->first();
        abort_unless($page && $page->isPublishedCms(), 404);

        return $this->renderCms($page);
    }

    /**
     * Serve a CMS page for an EXISTING named route (e.g. /services) when the flag is on and the page
     * is a published cms page; otherwise render the coded Blade view. This is the per-page toggle +
     * coded fallback for pages that already own a route.
     */
    public function pageOrCoded(string $slug, string $codedView): Response
    {
        if ((bool) config('ukv.cms.enabled')) {
            $page = Page::where('slug', $slug)->first();
            if ($page && $page->isPublishedCms()) {
                return $this->renderCms($page);
            }
        }

        return response()->view($codedView);
    }

    /**
     * Preview a page's blocks regardless of published status or the global flag, for Admin/Editor
     * only. Always fresh (no cache), so the team can see a draft before publishing. This is the
     * "preview" leg of draft -> preview -> publish.
     */
    public function preview(Page $page): Response|RedirectResponse
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('filament.admin.auth.login');
        }
        abort_unless(in_array($user->role, [UserRole::Admin, UserRole::Editor], true), 403);

        return response(view('cms.page', ['page' => $page])->render());
    }

    /**
     * Render the full CMS page (blocks inside the site layout) and cache the HTML. Rendering the
     * blocks within the layout in one pass keeps @once/@push behaviour identical to the coded page.
     */
    private function renderCms(Page $page): Response
    {
        $html = Cache::remember(
            'cms:page:'.$page->slug,
            now()->addHours(6),
            fn () => view('cms.page', ['page' => $page])->render(),
        );

        return response($html);
    }
}
