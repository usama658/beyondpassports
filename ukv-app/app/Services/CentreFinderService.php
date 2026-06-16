<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SupplyNode;
use Illuminate\Support\Collection;

/**
 * Nearest-centre finder (Wave 1, A2).
 *
 * Pure DB + compute — NO HTTP. Given a lat/lng (resolved upstream by PostcodeService or browser
 * geolocation) it ranks located supply nodes by Haversine distance and returns the closest few.
 *
 * Tie-break: at (effectively) equal distance, nodes where we hold/book appointments
 * (`we_book_here`) sort first, so the surface can highlight bookable centres without reordering
 * genuinely closer ones.
 */
final class CentreFinderService
{
    /**
     * Distance (km) within which two nodes are treated as "the same distance" for the
     * we_book_here tie-break. Small enough that it never reorders meaningfully-closer nodes.
     */
    private const TIE_EPSILON_KM = 0.05;

    public function __construct(private readonly GeoService $geo) {}

    /**
     * Closest located nodes to the given point.
     *
     * @param  string|null  $type   optional SupplyNode type filter (centre|paypoint|embassy|courier).
     * @param  int  $limit  max results.
     * @return Collection<int, array{node: SupplyNode, distance_km: float}>  sorted nearest-first.
     */
    public function nearest(float $lat, float $lng, ?string $type = null, int $limit = 5): Collection
    {
        $query = SupplyNode::located();

        if ($type !== null) {
            $query->where('type', $type);
        }

        return $query->get()
            ->map(fn (SupplyNode $node): array => [
                'node' => $node,
                'distance_km' => round(
                    $this->geo->haversineKm((float) $lat, (float) $lng, (float) $node->lat, (float) $node->lng),
                    1
                ),
            ])
            ->sort(function (array $a, array $b): int {
                // Primary: ascending distance. Within a tiny epsilon, we_book_here wins.
                if (abs($a['distance_km'] - $b['distance_km']) < self::TIE_EPSILON_KM) {
                    return ($b['node']->we_book_here ? 1 : 0) <=> ($a['node']->we_book_here ? 1 : 0);
                }

                return $a['distance_km'] <=> $b['distance_km'];
            })
            ->values()
            ->take($limit);
    }
}
