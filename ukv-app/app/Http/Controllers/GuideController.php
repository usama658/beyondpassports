<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\GuideType;
use App\Models\Destination;
use App\Models\Guide;
use App\Services\GuideService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Public GUIDES silo — now DB-driven (Guide engine, approach B).
 *
 * Three public surfaces:
 *   GET /guides                  -> index()       evergreen guide cards + links to country hubs
 *   GET /guides/{slug}           -> show()        one EVERGREEN guide (destination_id null)
 *   GET /visa/{destination}/{topic} -> showCountry()  one COUNTRY guide, resolved by GuideService
 *
 * The legacy `const GUIDES` registry is retired — the 6 entries were migrated into `guides`
 * rows (see 2026_06_17_000002_migrate_legacy_guides_into_rows). Routing/redirects are wired
 * elsewhere (routes/web.php is owned outside this build).
 *
 * Content is GENERAL guidance only. Not a government website; requirements depend on
 * nationality/residence; an ETA is an authorisation not a document; no service can guarantee
 * a government decision.
 */
class GuideController extends Controller
{
    public function __construct(private readonly GuideService $guides)
    {
    }

    /**
     * Evergreen slugs of every PUBLISHED evergreen guide, in display order.
     *
     * Retained for SitemapController, which reads this list so its URL set can never drift
     * from the guides that actually exist (audit M-7). Country guides are nested under their
     * destination and are reported via the destination, not here.
     *
     * @return list<string>
     */
    public static function slugs(): array
    {
        return Guide::published()
            ->whereNull('destination_id')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->pluck('slug')
            ->all();
    }

    /**
     * Guides hub — evergreen card grid + links across to the country hubs that carry a cluster.
     */
    public function index(): View
    {
        $evergreen = $this->guides->evergreen();

        // Country hubs that have at least one published guide — link ACROSS to /visa/{slug}.
        $countryHubs = Destination::query()
            ->whereHas('guides', fn ($q) => $q->published())
            ->withCount(['guides' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->get();

        return view('public.guides.index', [
            'evergreen'   => $evergreen,
            'countryHubs' => $countryHubs,
        ]);
    }

    /**
     * Single EVERGREEN guide article, looked up by slug. 404 on unknown/unpublished/non-evergreen.
     */
    public function show(string $slug): View
    {
        $guide = Guide::published()
            ->whereNull('destination_id')
            ->where('slug', $slug)
            ->first();

        if ($guide === null) {
            abort(404);
        }

        return view('public.guides.show', [
            'guide' => $guide,
            'checklistCountry' => null, // generic guide → generic checklist band
        ]);
    }

    /**
     * Single COUNTRY guide, resolved by destination slug + topic slug. 404 when no published
     * guide matches. {topic} is route-constrained to the 15 known topic slugs in web.php.
     */
    public function showCountry(Destination $destination, string $topic): View|RedirectResponse
    {
        // Country guide pages DRAFTED alongside the money pages (config
        // ukv.destinations.country_pages_enabled). While off, 302-redirect to the /schengen-visa hub.
        if (! config('ukv.destinations.country_pages_enabled')) {
            return redirect('/schengen-visa');
        }

        $guide = $this->guides->resolveCountryGuide($destination->slug, $topic);

        if ($guide === null) {
            abort(404);
        }

        return view('public.guides.show', [
            'guide' => $guide,
            'checklistCountry' => $destination->name, // country guide → deep-linked checklist band
        ]);
    }
}
