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
        View::composer(['public.home', 'public.tools', 'public.apply'], function ($view) {
            $view->with('navDestinations', \App\Models\Destination::orderBy('name')->get());
        });
    }
}
