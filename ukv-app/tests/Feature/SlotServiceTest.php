<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CentreSlot;
use App\Models\SupplyNode;
use App\Services\SlotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Held-slot inventory (Wave 2, B1).
 *
 * Covers the availability scope, the hold lifecycle (available -> held with expiry, guarded
 * against double-hold), the expired-hold sweep, and nextAvailableNear's distance ordering +
 * soonest-slot attachment. The latter runs against the real CentreFinderService (A2) with
 * nodes placed at distinct lat/lng so genuine Haversine distance drives the ordering.
 */
final class SlotServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): SlotService
    {
        return app(SlotService::class);
    }

    private function makeNode(array $overrides = []): SupplyNode
    {
        $node = new SupplyNode;
        $node->forceFill(array_merge([
            'node_key' => 'centre-'.uniqid(),
            'type' => 'centre',
            'name' => 'London Visa Centre',
            'lat' => 51.5074,
            'lng' => -0.1278,
            'we_book_here' => true,
        ], $overrides));
        $node->save();

        return $node->fresh();
    }

    private function makeSlot(SupplyNode $node, array $attrs = []): CentreSlot
    {
        return CentreSlot::create(array_merge([
            'supply_node_id' => $node->id,
            'slot_at' => Carbon::now()->addDays(3),
            'status' => 'available',
            'hold_expires_at' => null,
            'order_id' => null,
        ], $attrs));
    }

    public function test_available_scope_excludes_past_booked_and_held(): void
    {
        $node = $this->makeNode();

        $future = $this->makeSlot($node, ['slot_at' => Carbon::now()->addDay()]);
        $this->makeSlot($node, ['slot_at' => Carbon::now()->subDay()]);                 // past
        $this->makeSlot($node, ['status' => 'booked']);                                 // booked
        $this->makeSlot($node, ['status' => 'held', 'hold_expires_at' => Carbon::now()->addMinutes(10)]);

        $ids = CentreSlot::query()->available()->pluck('id');

        $this->assertEquals([$future->id], $ids->all());
    }

    public function test_available_for_returns_upcoming_available_soonest_first(): void
    {
        $node = $this->makeNode();

        $later = $this->makeSlot($node, ['slot_at' => Carbon::now()->addDays(5)]);
        $soonest = $this->makeSlot($node, ['slot_at' => Carbon::now()->addDay()]);
        $mid = $this->makeSlot($node, ['slot_at' => Carbon::now()->addDays(3)]);

        $ids = $this->service()->availableFor($node, 2)->pluck('id');

        $this->assertEquals([$soonest->id, $mid->id], $ids->all());
        $this->assertCount(2, $ids, 'availableFor must respect the limit.');
        $this->assertNotContains($later->id, $ids->all());
    }

    public function test_hold_flips_available_to_held_with_expiry(): void
    {
        $node = $this->makeNode();
        $slot = $this->makeSlot($node, ['slot_at' => Carbon::now()->addDay()]);

        $ok = $this->service()->hold($slot, null, 30);

        $this->assertTrue($ok);

        $slot->refresh();
        $this->assertSame('held', $slot->status);
        $this->assertNotNull($slot->hold_expires_at);
        $this->assertTrue($slot->hold_expires_at->greaterThan(Carbon::now()));
    }

    public function test_hold_rejects_double_hold(): void
    {
        $node = $this->makeNode();
        $slot = $this->makeSlot($node, ['slot_at' => Carbon::now()->addDay()]);

        $this->assertTrue($this->service()->hold($slot, null, 30));

        // Same slot is no longer available -> second hold must fail and leave it held.
        $this->assertFalse($this->service()->hold($slot->fresh(), null, 30));

        $this->assertSame('held', $slot->fresh()->status);
    }

    public function test_release_expired_restores_expired_holds_only(): void
    {
        $node = $this->makeNode();

        $expired = $this->makeSlot($node, [
            'status' => 'held',
            'hold_expires_at' => Carbon::now()->subMinute(),
            'order_id' => null,
        ]);
        $live = $this->makeSlot($node, [
            'status' => 'held',
            'hold_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $released = $this->service()->releaseExpired();

        $this->assertSame(1, $released);

        $expired->refresh();
        $this->assertSame('available', $expired->status);
        $this->assertNull($expired->hold_expires_at);
        $this->assertNull($expired->order_id);

        $this->assertSame('held', $live->fresh()->status, 'A live hold must be left untouched.');
    }

    public function test_next_available_near_orders_by_distance_and_attaches_soonest_slot(): void
    {
        // Origin is central London; near centre is metres away, far centre ~5km north.
        $near = $this->makeNode(['name' => 'Near Centre', 'lat' => 51.5074, 'lng' => -0.1278]);
        $far = $this->makeNode(['name' => 'Far Centre', 'lat' => 51.5530, 'lng' => -0.1278]);

        // Near centre: soonest available slot + a later one + a past one (excluded).
        $soonest = $this->makeSlot($near, ['slot_at' => Carbon::now()->addDay()]);
        $this->makeSlot($near, ['slot_at' => Carbon::now()->addDays(4)]);
        $this->makeSlot($near, ['slot_at' => Carbon::now()->subDay()]);
        // Far centre: one available slot.
        $this->makeSlot($far, ['slot_at' => Carbon::now()->addDays(2)]);

        // Real A2 finder: distinct lat/lng so genuine Haversine distance orders near < far.
        $results = $this->service()->nextAvailableNear(51.5074, -0.1278, null, 5);

        $this->assertSame(['Near Centre', 'Far Centre'], $results->pluck('node.name')->all());

        $nearItem = $results->first();
        $this->assertLessThan($results->last()['distance_km'], $nearItem['distance_km']);
        $this->assertSame($soonest->id, $nearItem['next_slot']->id);
        $this->assertSame(2, $nearItem['available_count'], 'Excludes the past slot.');

        $farItem = $results->last();
        $this->assertSame(1, $farItem['available_count']);
        $this->assertNotNull($farItem['next_slot']);
    }
}
