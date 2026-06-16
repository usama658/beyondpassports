<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * UK postcode -> coordinates geocoder for the nearest-centre finder.
 *
 * Backed by postcodes.io (free, no API key): GET https://api.postcodes.io/postcodes/{pc}.
 * Reads result.result.latitude / .longitude.
 *
 * SAFETY (mirrors HubSpotService's guarded-Http posture):
 *   - Every failure path — non-2xx (incl. 404 for an unknown postcode), transport/timeout
 *     error, or a body missing lat/lng — returns null and logs at debug. It NEVER throws, so a
 *     postcodes.io outage degrades the finder to "no result" rather than erroring the request.
 *   - Results are cached for a day, keyed by the normalised postcode, so repeat lookups (and the
 *     test suite) hit the network at most once per postcode.
 */
final class PostcodeService
{
    private const BASE_URL = 'https://api.postcodes.io';

    private const TIMEOUT = 8;

    /**
     * Resolve a UK postcode to coordinates.
     *
     * @return array{lat: float, lng: float}|null  null on any failure / unknown postcode.
     */
    public function lookup(string $postcode): ?array
    {
        $normalised = $this->normalise($postcode);
        if ($normalised === '') {
            return null;
        }

        return Cache::remember(
            'postcode:'.$normalised,
            now()->addDay(),
            fn (): ?array => $this->fetch($postcode)
        );
    }

    /**
     * Hit postcodes.io and pull lat/lng. Guarded: any non-2xx / transport error / missing
     * fields -> null (debug log, never throws).
     *
     * @return array{lat: float, lng: float}|null
     */
    private function fetch(string $postcode): ?array
    {
        try {
            $res = Http::baseUrl(self::BASE_URL)
                ->acceptJson()
                ->timeout(self::TIMEOUT)
                ->get('/postcodes/'.rawurlencode(trim($postcode)));
        } catch (Throwable $e) {
            Log::debug('PostcodeService lookup transport error.', [
                'postcode' => $postcode,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $res->successful()) {
            Log::debug('PostcodeService lookup non-2xx.', [
                'postcode' => $postcode,
                'status' => $res->status(),
            ]);

            return null;
        }

        $lat = $res->json('result.latitude');
        $lng = $res->json('result.longitude');

        if (! is_numeric($lat) || ! is_numeric($lng)) {
            Log::debug('PostcodeService lookup missing coordinates.', [
                'postcode' => $postcode,
            ]);

            return null;
        }

        return ['lat' => (float) $lat, 'lng' => (float) $lng];
    }

    /** Uppercase, trim, strip all spaces — yields a stable cache key (e.g. "SW1A 1AA" -> "SW1A1AA"). */
    private function normalise(string $postcode): string
    {
        return str_replace(' ', '', strtoupper(trim($postcode)));
    }
}
