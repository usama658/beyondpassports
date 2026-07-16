<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Services\AvailabilityService;
use App\Services\GuideService;
use App\Services\RequirementService;
use Illuminate\Contracts\View\View;

/**
 * Public, database-driven destination "money pages".
 *
 * These are the SEO/marketing pages a traveller lands on before applying. Everything
 * rendered here comes from the `destinations` table (see the create_destinations_table
 * migration) — no per-destination hard-coding. The single page mirrors the coded
 * reference at frontend/destination.html (the Turkey eVisa money page).
 *
 * Routes to register (NOT wired here — routes/web.php is owned elsewhere):
 *   GET /schengen-visa            -> index()
 *   GET /visa/{destination:slug} -> show()    (route-model binding key: slug)
 *
 * Compliance copy (independent service / govt fee separate / express = our handling /
 * no approval guarantee) lives in the Blade views and is required on every page.
 */
class DestinationController extends Controller
{
    /**
     * Destinations hub — boarding-pass cards for every published destination.
     */
    public function index(AvailabilityService $availability): View
    {
        // Schengen-only: the /schengen-visa hub lists the Schengen countries (searchable grid).
        $destinations = Destination::query()
            ->where('visa_type', 'Schengen')
            ->orderBy('name')
            ->get();

        // Honest, per-destination appointment availability (published snapshots only; "ask" when none).
        $availability = $availability->byDestination('Schengen');

        // Region-grouped, soonest-available-first within each region, for the appointment board.
        $regionOrder = ['Western Europe', 'Southern Europe', 'Northern Europe', 'Central & Eastern Europe'];
        $byRegion = $destinations
            ->sortBy(fn ($d) => optional($availability[$d->id]['next_available_on'] ?? null)?->timestamp ?? PHP_INT_MAX)
            ->groupBy('region')
            ->sortBy(fn ($group, $region) => array_search($region, $regionOrder, true) === false
                ? PHP_INT_MAX
                : array_search($region, $regionOrder, true));

        return view('destinations.index', [
            'destinations' => $destinations,
            'availability' => $availability,
            'byRegion' => $byRegion,
        ]);
    }

    /**
     * Conversion landing page for the "Schengen visa consultancy" keyword (separate from the
     * /schengen-visa browse hub). Reuses the honest availability board + reviews; built to convert
     * paid/organic traffic searching for Schengen visa help.
     */
    public function schengenLanding(AvailabilityService $availability): View
    {
        $destinations = Destination::query()
            ->where('visa_type', 'Schengen')
            ->orderBy('name')
            ->get();

        $avail = $availability->byDestination('Schengen');

        $regionOrder = ['Western Europe', 'Southern Europe', 'Northern Europe', 'Central & Eastern Europe'];
        $byRegion = $destinations
            ->sortBy(fn ($d) => optional($avail[$d->id]['next_available_on'] ?? null)?->timestamp ?? PHP_INT_MAX)
            ->groupBy('region')
            ->sortBy(fn ($group, $region) => array_search($region, $regionOrder, true) === false
                ? PHP_INT_MAX
                : array_search($region, $regionOrder, true));

        return view('public.schengen-visa', [
            'destinations' => $destinations,
            'availability' => $avail,
            'byRegion' => $byRegion,
            'reviews' => array_slice(\App\Http\Controllers\ReviewController::all(), 0, 3),
        ]);
    }

    /**
     * Schengen / ETIAS hub — reuses the destination boarding-pass card layout for every
     * ETIAS destination. (Regional grouping of the cards is a follow-up.)
     */
    public function schengen(\Illuminate\Http\Request $request): View
    {
        // Optional region filter from the nav (?region=Western Europe). Validated against
        // the regions that actually exist so a junk value just falls back to "all".
        $regions = Destination::query()->where('visa_type', 'Schengen')
            ->whereNotNull('region')->distinct()->pluck('region');
        $activeRegion = $request->query('region');
        $activeRegion = $regions->contains($activeRegion) ? $activeRegion : null;

        $destinations = Destination::query()
            ->where('visa_type', 'Schengen')
            ->when($activeRegion, fn ($q) => $q->where('region', $activeRegion))
            ->orderBy('name')
            ->get();

        return view('destinations.schengen', [
            'destinations' => $destinations,
            'activeRegion' => $activeRegion,
        ]);
    }

    /**
     * Single destination money page, bound by slug.
     *
     * Bind in the route as {destination:slug} so the URL is /visa/turkey, not /visa/7.
     */
    public function show(Destination $destination, RequirementService $requirements)
    {
        // Per-country money pages DRAFTED (config ukv.destinations.country_pages_enabled). While off,
        // every /visa/{slug} 302-redirects to the single /schengen-visa hub. Reversible.
        if (! config('ukv.destinations.country_pages_enabled')) {
            return redirect('/schengen-visa');
        }

        // Schengen-only pivot (2026-06-24): non-Schengen money pages 301 -> /schengen-visa. Reversible.
        if ($destination->visa_type !== 'Schengen') {
            return redirect('/schengen-visa', 301);
        }

        // Document Requirements Engine: a generic "documents you'll likely need" preview for
        // this destination. No order yet — preview() evaluates rules scoped to the destination
        // with no traveller-specific context (tourist-adult baseline).
        $docItems = $requirements->preview($destination);

        // Guide engine: the published guide cluster for this destination (hub-and-spoke).
        // Cards link DOWN to /visa/{slug}/{topic}. Empty when nothing is published yet.
        $guideCluster = app(GuideService::class)->clusterFor($destination);

        return view('destinations.show', [
            'destination'  => $destination,
            'docItems'     => $docItems,
            'guideCluster' => $guideCluster,
        ]);
    }
}
