<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Geospatial helpers for the nearest-centre finder (Wave 1, A1).
 *
 * Pure math — no DB, no I/O. CentreFinderService uses haversineKm() to rank
 * located supply nodes by great-circle distance from a user's lat/lng.
 */
final class GeoService
{
    /** Mean Earth radius in kilometres. */
    private const EARTH_RADIUS_KM = 6371.0;

    /**
     * Great-circle distance between two WGS84 points, in kilometres (Haversine).
     *
     * Returns 0.0 for identical points. Symmetric in its arguments.
     */
    public function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        $c = 2 * asin(min(1.0, sqrt($a)));

        return self::EARTH_RADIUS_KM * $c;
    }
}
