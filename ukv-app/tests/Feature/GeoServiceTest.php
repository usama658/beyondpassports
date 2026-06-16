<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\GeoService;
use Tests\TestCase;

/**
 * Covers the Haversine great-circle distance. Pure math — no DB — but kept as a
 * Feature test alongside the other geo/finder coverage for the nearest-centre work.
 */
final class GeoServiceTest extends TestCase
{
    private GeoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GeoService;
    }

    public function test_zero_distance_for_identical_points(): void
    {
        // London city centre against itself.
        $this->assertSame(0.0, $this->service->haversineKm(51.5074, -0.1278, 51.5074, -0.1278));
    }

    public function test_known_distance_london_to_manchester(): void
    {
        // London (51.5074, -0.1278) -> Manchester (53.4808, -2.2426) ≈ 262 km.
        $km = $this->service->haversineKm(51.5074, -0.1278, 53.4808, -2.2426);

        $this->assertEqualsWithDelta(262.0, $km, 5.0);
    }

    public function test_distance_is_symmetric(): void
    {
        $forward = $this->service->haversineKm(51.5074, -0.1278, 53.4808, -2.2426);
        $reverse = $this->service->haversineKm(53.4808, -2.2426, 51.5074, -0.1278);

        $this->assertEqualsWithDelta($forward, $reverse, 0.0001);
    }
}
