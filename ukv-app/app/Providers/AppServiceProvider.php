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

        // Header mega-menu (shared layout): a small set of destinations with a photo + "from" fee
        // for the Destinations dropdown panel. On every public page, so kept cheap (≤6 rows).
        View::composer('layouts.public', function ($view) {
            $view->with('navMenuDestinations', \App\Models\Destination::query()
                ->orderByRaw('image_path IS NULL') // photographed first
                ->orderBy('name')
                ->take(6)
                ->get());
        });
    }
}
