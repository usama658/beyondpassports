<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Throttle the public status-tracker lookup (anti-enumeration).
        RateLimiter::for('tracker', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        // Throttle the contact/callback form (anti-spam).
        RateLimiter::for('contact', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        // Share the live destination list with public pages that show a picker/preview,
        // so every seeded location appears everywhere (not a hardcoded subset).
        View::composer(['public.home', 'public.tools', 'public.apply', 'public.about'], function ($view) {
            $view->with('navDestinations', \App\Models\Destination::orderBy('name')->get());
        });

        // Home appointments band: live slot summary (guarded — zeros => the band shows a plain
        // finder CTA instead of fake counts).
        View::composer('public.home', function ($view) {
            $view->with('slotSummary', app(\App\Services\SlotService::class)->summary());
        });

        // Header mega-menu (shared partial): grouped Destinations dropdown —
        //   • Popular: the curated money pages (non-ETIAS), photo cards.
        //   • Europe: the Schengen/ETIAS countries grouped by region, each linking to the
        //     filtered hub (/visa/schengen?region=…).
        // Bound to the header partial so it populates everywhere the header renders (shared
        // layout AND standalone pages that include partials.site-header directly).
        View::composer('partials.site-header', function ($view) {
            // Curated order for the "Popular" column — money pages lead, not alphabetical.
            $popularOrder = ['turkey', 'india', 'egypt', 'uae', 'thailand', 'usa-esta'];
            $popular = \App\Models\Destination::query()
                ->where('visa_type', '!=', 'Schengen')
                ->whereIn('slug', $popularOrder)
                ->get()
                ->sortBy(fn ($d) => array_search($d->slug, $popularOrder))
                ->values();

            // Europe regions, fixed display order, with live counts (only non-empty regions).
            $regionOrder = ['Western Europe', 'Southern Europe', 'Northern Europe', 'Central & Eastern Europe'];
            $counts = \App\Models\Destination::query()
                ->where('visa_type', 'Schengen')
                ->selectRaw('region, count(*) as c')
                ->groupBy('region')
                ->pluck('c', 'region');
            $regions = collect($regionOrder)
                ->filter(fn ($r) => ($counts[$r] ?? 0) > 0)
                ->map(fn ($r) => ['name' => $r, 'count' => (int) $counts[$r]])
                ->values();

            $view->with('navMenuPopular', $popular);
            $view->with('navMenuRegions', $regions);
        });
    }
}
