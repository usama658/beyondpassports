<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CentreSlot;
use App\Models\Destination;
use App\Models\Order;
use App\Models\SupplyNode;
use App\Services\SlotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Hold-on-apply: SlotService::holdForOrder reserves the soonest slot at a "we book here" centre
 * ONLY for in-person/biometric destinations — online visas (eVisa/ETA/visa-free/visa-on-arrival)
 * need no UK appointment and must never consume slot inventory.
 */
final class HoldOnApplyTest extends TestCase
{
    use RefreshDatabase;

    private function destination(string $visaType): Destination
    {
        return Destination::create([
            'name' => 'Testland '.$visaType,
            'slug' => 'testland-'.strtolower(str_replace([' ', '-'], '', $visaType)),
            'visa_type' => $visaType,
            'govt_fee_gbp' => 0,
            'tier_standard_gbp' => 39,
            'tier_express_gbp' => 59,
            'tier_premium_gbp' => 89,
            'passport_validity_months' => 6,
        ]);
    }

    private function centreWithSlot(bool $weBook = true): CentreSlot
    {
        $node = SupplyNode::create([
            'node_key' => 'n-'.uniqid('', true),
            'type' => 'centre',
            'name' => 'Test VAC',
            'lat' => 51.5,
            'lng' => -0.12,
            'we_book_here' => $weBook,
        ]);

        return CentreSlot::create([
            'supply_node_id' => $node->getKey(),
            'slot_at' => Carbon::now()->addDays(5)->setTime(10, 0),
            'status' => 'available',
        ]);
    }

    private function orderFor(Destination $dest): Order
    {
        return Order::create([
            'name' => 'Test', 'email' => 'h@example.com',
            'destination_id' => $dest->getKey(), 'destination_name' => $dest->name,
            'status' => 'paid',
        ]);
    }

    public function test_holds_soonest_slot_for_in_person_destination(): void
    {
        $slot = $this->centreWithSlot();
        $order = $this->orderFor($this->destination('Biometric visa'));

        $held = app(SlotService::class)->holdForOrder($order, 60);

        $this->assertNotNull($held);
        $this->assertSame($slot->getKey(), $held->getKey());

        $slot->refresh();
        $this->assertSame('held', $slot->status);
        $this->assertSame($order->getKey(), $slot->order_id);
        $this->assertNotNull($slot->hold_expires_at);
    }

    public function test_no_hold_for_online_visa_destination(): void
    {
        $slot = $this->centreWithSlot();
        $order = $this->orderFor($this->destination('eVisa'));

        $this->assertNull(app(SlotService::class)->holdForOrder($order));

        $slot->refresh();
        $this->assertSame('available', $slot->status);
        $this->assertNull($slot->order_id);
    }

    public function test_no_hold_when_centre_is_not_one_we_book_at(): void
    {
        $slot = $this->centreWithSlot(weBook: false);
        $order = $this->orderFor($this->destination('Biometric visa'));

        $this->assertNull(app(SlotService::class)->holdForOrder($order));
        $this->assertSame('available', $slot->refresh()->status);
    }
}
