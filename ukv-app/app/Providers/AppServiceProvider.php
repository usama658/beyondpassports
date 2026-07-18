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

        // Tour-packages body: inject its data here (not via an in-partial @php) so the variables
        // exist in every render path — coded route, @include, and the CMS locked-include. The
        // partial's leading @php was not surviving compilation in the locked-include path, leaving
        // $sla/$waCheck/etc undefined and forcing the CMS to serve a stale cached page.
        View::composer('partials.tours-body', function ($view) {
            $stats = \App\Support\SiteStats::class;
            $view->with([
                'tours'     => config('ukv.tours.packages', []),
                'sla'       => $stats::responseSla(),
                'apps'      => $stats::applications(),
                'revs'      => $stats::reversals(),
                'ins'       => $stats::insuranceMin(),
                'waCheck'   => $stats::chatUrl('Hi Beyond Passports, I would like to check my eligibility before booking a trip.'),
                'waConsult' => $stats::chatUrl('Hi Beyond Passports, I would like to book my free consultation about a tour.'),
                'bookMsg'   => fn ($p) => $stats::chatUrl('Hi Beyond Passports, I am interested in the '.$p['name'].' ('.$p['where'].', '.$p['days'].') trip with the visa included. Please tell me more.'),
                'waIcon'    => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width:17px;height:17px;fill:#fff;vertical-align:-3px"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.978-1.607zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>',
            ]);
        });

        // schengen-visa-help (lp-bold) appointment board: SAME data source as /schengen-visa —
        // AvailabilityService::byDestination (published CentreAvailability snapshots). Feature the
        // same countries (status ok/lim, "ask" omitted), same "next available" date, same soonest-
        // first order, so the two pages never disagree. The slot-count line is the real CentreSlot
        // total for that country (next 30 days) — the same inventory the pick-a-slot modal opens.
        View::composer('public.lp-bold', function ($view) {
            $availability = app(\App\Services\AvailabilityService::class)->byDestination('Schengen');
            $windowEnd = now()->addDays(30);
            $cards = \App\Models\Destination::query()
                ->where('visa_type', 'Schengen')
                ->with(['supplyNodes' => fn ($q) => $q->where('we_book_here', true)])
                ->get()
                ->map(function ($d) use ($availability, $windowEnd) {
                    $a = $availability[$d->id] ?? ['status' => 'ask', 'next_available_on' => null];
                    $nodeIds = $d->supplyNodes->pluck('id')->all();
                    $slots = empty($nodeIds) ? 0 : \App\Models\CentreSlot::query()
                        ->available()
                        ->whereIn('supply_node_id', $nodeIds)
                        ->where('slot_at', '<=', $windowEnd)
                        ->count();
                    return [
                        'name'   => $d->name,
                        'status' => $a['status'],
                        'date'   => $a['next_available_on'],
                        'slots'  => $slots,
                    ];
                })
                ->filter(fn ($c) => $c['status'] !== 'ask' && $c['date'] !== null)
                ->sortBy(fn ($c) => $c['date']->timestamp)
                ->map(fn ($c) => [
                    'name'  => $c['name'],
                    'cls'   => $c['status'] === 'ok' ? 'open' : 'tight',
                    'label' => $c['status'] === 'ok' ? 'Available' : 'Limited',
                    'date'  => $c['date']->format('j M Y'),
                    'slots' => $c['slots'],
                ])
                ->values();
            $view->with('apptCards', $cards);
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
