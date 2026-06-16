<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SupplyNode;
use App\Services\CentreFinderService;
use App\Services\PostcodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Wave 1 / A2 — geocoding + nearest-centre finder.
 *
 * Covers:
 *   - PostcodeService: guarded no-op (404 -> null), success -> coords, and one-call caching.
 *   - CentreFinderService::nearest(): distance ordering, type filter, limit, we_book_here tie-boost.
 *
 * Geo columns (lat/lng/we_book_here) come from the sibling migration 2026_06_18_000001 — assumed
 * present at test runtime; rows are created inline via SupplyNode::create().
 */
final class CentreFinderTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = 'https://api.postcodes.io/postcodes/*';

    private int $nodeSeq = 0;

    private function node(array $overrides = []): SupplyNode
    {
        $this->nodeSeq++;

        return SupplyNode::create(array_merge([
            'node_key' => 'node-'.$this->nodeSeq,
            'type' => 'centre',
            'name' => 'Centre '.$this->nodeSeq,
            'lat' => 51.5,
            'lng' => -0.12,
            'we_book_here' => false,
        ], $overrides));
    }

    // -------------------------------------------------------------------------------------
    // PostcodeService
    // -------------------------------------------------------------------------------------

    /** Unknown postcode (404) is a guarded no-op: null, no throw. */
    public function test_postcode_lookup_returns_null_on_404(): void
    {
        Http::fake([self::ENDPOINT => Http::response(['status' => 404, 'error' => 'Postcode not found'], 404)]);

        $result = app(PostcodeService::class)->lookup('ZZ99 9ZZ');

        $this->assertNull($result);
    }

    /** A successful lookup returns float lat/lng from result.result.latitude/longitude. */
    public function test_postcode_lookup_returns_coordinates_on_success(): void
    {
        Http::fake([self::ENDPOINT => Http::response([
            'status' => 200,
            'result' => ['latitude' => 51.501009, 'longitude' => -0.141588],
        ], 200)]);

        $result = app(PostcodeService::class)->lookup('SW1A 1AA');

        $this->assertSame(['lat' => 51.501009, 'lng' => -0.141588], $result);
        $this->assertIsFloat($result['lat']);
        $this->assertIsFloat($result['lng']);
    }

    /** Repeat lookups of the same (normalised) postcode hit the network only once. */
    public function test_postcode_lookup_is_cached_one_http_call(): void
    {
        Cache::flush();
        Http::fake([self::ENDPOINT => Http::response([
            'status' => 200,
            'result' => ['latitude' => 51.5, 'longitude' => -0.12],
        ], 200)]);

        $svc = app(PostcodeService::class);

        $first = $svc->lookup('sw1a1aa');
        // Different casing/spacing -> same normalised key -> served from cache.
        $second = $svc->lookup('SW1A 1AA');

        $this->assertSame($first, $second);
        Http::assertSentCount(1);
    }

    /** Missing coordinate fields in an otherwise-200 body -> null (guarded). */
    public function test_postcode_lookup_returns_null_when_fields_missing(): void
    {
        Http::fake([self::ENDPOINT => Http::response(['status' => 200, 'result' => null], 200)]);

        $this->assertNull(app(PostcodeService::class)->lookup('SW1A 1AA'));
    }

    // -------------------------------------------------------------------------------------
    // CentreFinderService::nearest()
    // -------------------------------------------------------------------------------------

    /** Results are ordered nearest-first by distance, each carrying its rounded distance_km. */
    public function test_nearest_orders_by_distance(): void
    {
        // Origin = central London.
        $origin = ['lat' => 51.5074, 'lng' => -0.1278];

        $far = $this->node(['name' => 'Manchester', 'lat' => 53.4808, 'lng' => -2.2426]);
        $mid = $this->node(['name' => 'Reading', 'lat' => 51.4543, 'lng' => -0.9781]);
        $near = $this->node(['name' => 'Westminster', 'lat' => 51.4995, 'lng' => -0.1248]);

        $results = app(CentreFinderService::class)->nearest($origin['lat'], $origin['lng']);

        $this->assertSame(
            [$near->id, $mid->id, $far->id],
            $results->pluck('node.id')->all()
        );
        $this->assertArrayHasKey('distance_km', $results->first());
        $this->assertIsFloat($results->first()['distance_km']);
        // Sanity: ascending distances.
        $distances = $results->pluck('distance_km')->all();
        $sorted = $distances;
        sort($sorted);
        $this->assertSame($sorted, $distances);
    }

    /** The optional type filter restricts results to that node type. */
    public function test_nearest_filters_by_type(): void
    {
        $centre = $this->node(['type' => 'centre', 'lat' => 51.5, 'lng' => -0.12]);
        $paypoint = $this->node(['type' => 'paypoint', 'lat' => 51.5, 'lng' => -0.12]);

        $results = app(CentreFinderService::class)->nearest(51.5, -0.12, 'paypoint');

        $this->assertSame([$paypoint->id], $results->pluck('node.id')->all());
    }

    /** The limit caps the number of returned results. */
    public function test_nearest_respects_limit(): void
    {
        for ($i = 0; $i < 7; $i++) {
            $this->node(['lat' => 51.5 + ($i * 0.1), 'lng' => -0.12]);
        }

        $results = app(CentreFinderService::class)->nearest(51.5, -0.12, null, 3);

        $this->assertCount(3, $results);
    }

    /** At equal distance, a we_book_here node sorts ahead of one that isn't. */
    public function test_nearest_boosts_we_book_here_on_ties(): void
    {
        // Both at the exact same coordinates -> identical distance -> tie.
        $plain = $this->node(['name' => 'Plain', 'lat' => 51.5, 'lng' => -0.12, 'we_book_here' => false]);
        $booked = $this->node(['name' => 'Booked', 'lat' => 51.5, 'lng' => -0.12, 'we_book_here' => true]);

        $results = app(CentreFinderService::class)->nearest(51.5, -0.12);

        $this->assertSame(
            [$booked->id, $plain->id],
            $results->pluck('node.id')->all(),
            'we_book_here node should rank first at equal distance.'
        );
    }
}
