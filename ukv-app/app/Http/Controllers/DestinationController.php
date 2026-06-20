<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Destination;
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
 *   GET /destinations            -> index()
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
    public function index(): View
    {
        $destinations = Destination::query()
            ->orderBy('name')
            ->get();

        return view('destinations.index', [
            'destinations' => $destinations,
        ]);
    }

    /**
     * Schengen / ETIAS hub — reuses the destination boarding-pass card layout for every
     * ETIAS destination. (Regional grouping of the cards is a follow-up.)
     */
    public function schengen(): View
    {
        $destinations = Destination::query()
            ->where('visa_type', 'ETIAS')
            ->orderBy('name')
            ->get();

        return view('destinations.schengen', [
            'destinations' => $destinations,
        ]);
    }

    /**
     * Single destination money page, bound by slug.
     *
     * Bind in the route as {destination:slug} so the URL is /visa/turkey, not /visa/7.
     */
    public function show(Destination $destination, RequirementService $requirements): View
    {
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
