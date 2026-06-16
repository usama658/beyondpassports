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
 * Home appointments band data: SlotService::summary aggregates only upcoming available slots at
 * "we book here" centres, and returns zeros/null when there's nothing (so the home band falls back
 * to a plain finder CTA — no fake counts).
 */
final class SlotSummaryTest extends TestCase
{
    use RefreshDatabase;

    private function node(bool $weBook = true): SupplyNode
    {
        return SupplyNode::create([
            'node_key' => 'n-'.uniqid('', true),
            'type' => 'centre',
            'name' => 'C',
            'lat' => 51.5,
            'lng' => -0.1,
            'we_book_here' => $weBook,
        ]);
    }

    public function test_summary_is_zero_when_no_slots(): void
    {
        $s = app(SlotService::class)->summary();

        $this->assertSame(0, $s['available_count']);
        $this->assertNull($s['next_slot_at']);
        $this->assertSame(0, $s['centre_count']);
    }

    public function test_summary_counts_available_slots_at_partner_centres(): void
    {
        $a = $this->node();
        $b = $this->node();
        $soon = Carbon::now()->addDays(2)->setTime(9, 0);

        CentreSlot::create(['supply_node_id' => $a->getKey(), 'slot_at' => $soon, 'status' => 'available']);
        CentreSlot::create(['supply_node_id' => $a->getKey(), 'slot_at' => Carbon::now()->addDays(6), 'status' => 'available']);
        CentreSlot::create(['supply_node_id' => $b->getKey(), 'slot_at' => Carbon::now()->addDays(4), 'status' => 'available']);

        // Excluded: a non-partner centre, a past slot, and a held slot.
        $np = $this->node(weBook: false);
        CentreSlot::create(['supply_node_id' => $np->getKey(), 'slot_at' => Carbon::now()->addDay(), 'status' => 'available']);
        CentreSlot::create(['supply_node_id' => $a->getKey(), 'slot_at' => Carbon::now()->subDay(), 'status' => 'available']);
        CentreSlot::create(['supply_node_id' => $a->getKey(), 'slot_at' => Carbon::now()->addDays(3), 'status' => 'held']);

        $s = app(SlotService::class)->summary();

        $this->assertSame(3, $s['available_count']);
        $this->assertSame(2, $s['centre_count']);
        $this->assertNotNull($s['next_slot_at']);
        $this->assertSame($soon->toDateString(), $s['next_slot_at']->toDateString());
    }
}
