<?php

namespace App\Http\Controllers;

use App\Services\CentreFinderService;
use App\Services\PostcodeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Public "find your nearest centre" finder (Wave 1 / A3).
 *
 * Surfaces the nearest in-person supply nodes (visa application centres, PayPoint IDP
 * issuers, embassies, couriers) to a visitor's location, and highlights nodes where
 * UKVisaCo books appointments (`we_book_here`).
 *
 * Privacy / honesty stance:
 *  - Location is a UK postcode (resolved server-side via PostcodeService -> postcodes.io)
 *    OR raw lat/lng supplied by the optional browser-geolocation enhancement. Nothing is
 *    stored — both endpoints are stateless GETs that render results inline.
 *  - Works with NO JavaScript: the postcode form is a plain GET to /find-a-centre/search.
 *    The "use my location" button is progressive enhancement only.
 *  - UKVisaCo is an independent service, not a government site — the view carries that
 *    strip, and PayPoint-type results link the official PayPoint locator (we don't
 *    replicate their database).
 *
 * Wiring (NOT done here — see reply to caller):
 *   GET /find-a-centre         -> page()
 *   GET /find-a-centre/search  -> search()
 *
 * Code-to-contract (built by parallel agents A1/A2):
 *   App\Services\PostcodeService::lookup(string $postcode): ?array{lat:float,lng:float}
 *   App\Services\CentreFinderService::nearest(float $lat, float $lng, ?string $type = null, int $limit = 5): \Illuminate\Support\Collection
 *       -> each item ['node' => SupplyNode, 'distance_km' => float], sorted ascending.
 */
class CentreController extends Controller
{
    /** Filterable supply-node types offered in the UI (value => visitor-facing label). */
    public const TYPE_OPTIONS = [
        'centre'   => 'Visa application centre',
        'paypoint' => 'PayPoint (IDP)',
        'embassy'  => 'Embassy / consulate',
        'courier'  => 'Courier drop-off',
    ];

    /** How many results to show. */
    private const LIMIT = 5;

    /**
     * Render the finder: postcode box + type filter + optional "use my location" button.
     * Pre-fills from the query string so the form is sticky after a no-JS search.
     */
    public function page(Request $request): View
    {
        return view('public.find-a-centre', [
            'typeOptions'    => self::TYPE_OPTIONS,
            'postcode'       => (string) $request->query('postcode', ''),
            'selectedType'   => $this->normaliseType($request->query('type')),
            'results'        => null,   // no search yet -> partial shows the gentle prompt
            'searchedLabel'  => null,
            'gentleMessage'  => null,
        ]);
    }

    /**
     * Resolve a location (postcode OR lat/lng) and render the nearest centres.
     *
     * Accepts ?postcode=  OR  ?lat=&lng= (plus an optional ?type=). A postcode that
     * postcodes.io can't resolve is a gentle no-op: we re-render the finder with a soft
     * message rather than an error page. Always renders the same page (no-JS friendly).
     */
    public function search(Request $request, PostcodeService $postcodes, CentreFinderService $finder): View
    {
        $validated = $request->validate([
            'postcode' => ['nullable', 'string', 'max:12'],
            'lat'      => ['nullable', 'numeric', 'between:-90,90'],
            'lng'      => ['nullable', 'numeric', 'between:-180,180'],
            'type'     => ['nullable', 'string', 'in:centre,paypoint,embassy,courier'],
        ]);

        $type          = $this->normaliseType($validated['type'] ?? null);
        $postcode      = trim((string) ($validated['postcode'] ?? ''));
        $hasCoords     = isset($validated['lat'], $validated['lng']);

        $lat   = null;
        $lng   = null;
        $label = null;
        $gentle = null;

        if ($hasCoords) {
            // Browser-geolocation path (progressive enhancement): trust the supplied coords.
            $lat   = (float) $validated['lat'];
            $lng   = (float) $validated['lng'];
            $label = 'your current location';
        } elseif ($postcode !== '') {
            // No-JS path: resolve the postcode server-side. Guarded (never throws); a miss
            // (invalid / not found / postcodes.io unreachable) -> null -> gentle message.
            $resolved = $postcodes->lookup($postcode);

            if ($resolved === null) {
                $gentle = "We couldn't find that postcode. Please check it and try again — a UK postcode like SW1A 1AA works best.";
            } else {
                $lat   = (float) $resolved['lat'];
                $lng   = (float) $resolved['lng'];
                $label = strtoupper($postcode);
            }
        } else {
            // Empty submit: fall back to the gentle "enter a postcode" prompt (no message,
            // the partial's empty state covers it).
            $gentle = null;
        }

        // Only run the finder once we have a location; otherwise pass null so the partial
        // shows its empty/prompt state.
        $results = ($lat !== null && $lng !== null)
            ? $finder->nearest($lat, $lng, $type, self::LIMIT)
            : null;

        return view('public.find-a-centre', [
            'typeOptions'   => self::TYPE_OPTIONS,
            'postcode'      => $postcode,
            'selectedType'  => $type,
            'results'       => $results,
            'searchedLabel' => $label,
            'gentleMessage' => $gentle,
        ]);
    }

    /**
     * Coerce an arbitrary ?type= value to a known filter key, or null (no filter).
     */
    private function normaliseType(mixed $type): ?string
    {
        $type = is_string($type) ? strtolower(trim($type)) : null;

        return ($type !== null && array_key_exists($type, self::TYPE_OPTIONS)) ? $type : null;
    }
}
