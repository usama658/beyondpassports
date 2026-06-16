<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CentreSlot;
use App\Models\Order;
use App\Models\SupplyNode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Held-slot inventory (Wave 2, B1).
 *
 * Sits on top of CentreFinderService: given a lat/lng it finds the nearest located centres
 * and attaches their live held-slot availability (next available slot + a count). Also owns
 * the hold lifecycle: a slot is temporarily reserved against an order (available -> held),
 * and lapsed holds are swept back into the pool (held -> available).
 *
 * Slot statuses: available | held | booked. `available` means status=available AND the slot
 * is still in the future (see CentreSlot::scopeAvailable). Holds carry an expiry; an expired
 * hold is reclaimed by releaseExpired() (typically the scheduled slots:release-expired command).
 */
final class SlotService
{
    public function __construct(
        private readonly CentreFinderService $finder,
    ) {}

    /**
     * Nearest located centres with their held-slot availability, sorted by distance.
     *
     * Wraps CentreFinderService::nearest() and, for each returned node, attaches the soonest
     * available slot and how many available slots that centre currently has. Centres with no
     * availability are still returned (next_slot null, available_count 0) so the surface can
     * show "no slots near you" rather than nothing.
     *
     * @return Collection<int, array{node: SupplyNode, distance_km: float, next_slot: ?CentreSlot, available_count: int}>
     */
    public function nextAvailableNear(float $lat, float $lng, ?string $type = null, int $limit = 5): Collection
    {
        return $this->finder->nearest($lat, $lng, $type, $limit)
            ->map(function (array $item): array {
                /** @var SupplyNode $node */
                $node = $item['node'];

                $available = CentreSlot::query()
                    ->where('supply_node_id', $node->getKey())
                    ->available()
                    ->orderBy('slot_at');

                return [
                    'node' => $node,
                    'distance_km' => $item['distance_km'],
                    'next_slot' => (clone $available)->first(),
                    'available_count' => $available->count(),
                ];
            });
    }

    /**
     * Upcoming available slots for a single centre, soonest first.
     *
     * @return Collection<int, CentreSlot>
     */
    public function availableFor(SupplyNode $node, int $limit = 3): Collection
    {
        return CentreSlot::query()
            ->where('supply_node_id', $node->getKey())
            ->available()
            ->orderBy('slot_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Place a temporary hold on a slot for an order.
     *
     * Only an currently-available slot can be held: this re-checks availability on the live
     * row to guard against a double-hold race. On success the slot flips to `held`, gains a
     * hold_expires_at = now()+$minutes, and is linked to the order (nullable). Returns whether
     * the hold was taken.
     */
    public function hold(CentreSlot $slot, ?Order $order, int $minutes = 30): bool
    {
        $isAvailable = CentreSlot::query()
            ->whereKey($slot->getKey())
            ->available()
            ->exists();

        if (! $isAvailable) {
            return false;
        }

        $slot->forceFill([
            'status' => 'held',
            'hold_expires_at' => Carbon::now()->addMinutes($minutes),
            'order_id' => $order?->getKey(),
        ])->save();

        return true;
    }

    /**
     * Sweep lapsed holds back into the available pool.
     *
     * Each held slot whose hold_expires_at has passed is reset to `available` with its hold
     * timestamp and order link cleared. Returns the number of slots released.
     */
    public function releaseExpired(): int
    {
        return CentreSlot::query()
            ->heldExpired()
            ->update([
                'status' => 'available',
                'hold_expires_at' => null,
                'order_id' => null,
            ]);
    }
}
