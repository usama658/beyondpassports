<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\CentreAvailability;
use App\Models\Destination;
use App\Models\Order;
use App\Models\SupplyNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Scheduled janitors for the marketing availability board.
 *
 *   availability:sweep   — nulls long-expired snapshots (board already reports "ask").
 *   availability:derive  — publishes derived snapshots from upcoming booked appointments,
 *                          respecting MANUAL-WINS inside AvailabilityService::setSnapshot().
 *
 * Both must exit SUCCESS (scheduled heartbeats). Rows are created inline; no factories exist for
 * these models.
 */
final class AvailabilityCommandsTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    private function destination(array $overrides = []): Destination
    {
        $this->seq++;

        return Destination::create(array_merge([
            'name' => 'Dest '.$this->seq,
            'slug' => 'dest-'.$this->seq,
            'visa_type' => 'Schengen',
            'govt_fee_gbp' => 0,
            'tier_standard_gbp' => 39,
            'tier_express_gbp' => 59,
            'tier_premium_gbp' => 89,
            'passport_validity_months' => 6,
        ], $overrides));
    }

    private function node(array $overrides = []): SupplyNode
    {
        $this->seq++;

        return SupplyNode::create(array_merge([
            'node_key' => 'node-'.$this->seq,
            'type' => 'centre',
            'name' => 'Centre '.$this->seq,
            'lat' => 51.5,
            'lng' => -0.12,
            'we_book_here' => true,
            'is_global' => false,
        ], $overrides));
    }

    private function bookableDestinationWithCentre(): array
    {
        $dest = $this->destination();
        $node = $this->node();
        $dest->supplyNodes()->attach($node->getKey());

        return [$dest, $node];
    }

    private function order(Destination $dest): Order
    {
        return Order::create([
            'name' => 'Test', 'email' => 'a@example.com',
            'destination_id' => $dest->getKey(), 'destination_name' => $dest->name,
            'status' => 'paid',
        ]);
    }

    // -----------------------------------------------------------------------------------------
    // availability:sweep
    // -----------------------------------------------------------------------------------------

    public function test_sweep_nulls_long_expired_rows_only_and_is_idempotent(): void
    {
        $old = $this->node();
        $recent = $this->node();

        // Expired 40 days ago -> swept.
        $oldRow = CentreAvailability::create([
            'supply_node_id' => $old->getKey(),
            'next_available_on' => Carbon::now()->addDays(5)->toDateString(),
            'band' => 'good',
            'source' => 'manual',
            'confirmed_at' => Carbon::now()->subDays(47),
            'expires_at' => Carbon::now()->subDays(40),
        ]);

        // Expired only 2 days ago -> inside grace, untouched.
        $recentRow = CentreAvailability::create([
            'supply_node_id' => $recent->getKey(),
            'next_available_on' => Carbon::now()->addDays(5)->toDateString(),
            'band' => 'limited',
            'source' => 'manual',
            'confirmed_at' => Carbon::now()->subDays(9),
            'expires_at' => Carbon::now()->subDays(2),
        ]);

        $this->artisan('availability:sweep')->assertSuccessful();

        $oldRow->refresh();
        $this->assertNull($oldRow->next_available_on, 'Long-expired date should be nulled.');
        $this->assertNull($oldRow->band);

        $recentRow->refresh();
        $this->assertNotNull($recentRow->next_available_on, 'Recently expired row stays untouched.');
        $this->assertSame('limited', $recentRow->band);

        // Idempotent: a second sweep changes nothing further and still succeeds.
        $this->artisan('availability:sweep')->assertSuccessful();
        $this->assertNull($oldRow->fresh()->next_available_on);
    }

    public function test_sweep_runs_clean_with_no_rows(): void
    {
        $this->artisan('availability:sweep')->assertSuccessful();
    }

    // -----------------------------------------------------------------------------------------
    // availability:derive
    // -----------------------------------------------------------------------------------------

    public function test_derive_writes_snapshot_from_future_booked_appointment(): void
    {
        [$dest, $node] = $this->bookableDestinationWithCentre();
        $order = $this->order($dest);

        Appointment::create([
            'order_id' => $order->getKey(),
            'centre' => 'Test VAC',
            'scheduled_at' => Carbon::now()->addDays(30)->toDateString(), // >21d -> good
            'status' => 'booked',
        ]);

        $this->artisan('availability:derive')->assertSuccessful();

        $snap = CentreAvailability::where('supply_node_id', $node->getKey())->first();
        $this->assertNotNull($snap);
        $this->assertSame('derived', $snap->source);
        $this->assertSame('good', $snap->band);
        $this->assertSame(
            Carbon::now()->addDays(30)->toDateString(),
            $snap->next_available_on->toDateString()
        );
    }

    public function test_derive_bands_near_appointment_as_limited(): void
    {
        [$dest, $node] = $this->bookableDestinationWithCentre();
        $order = $this->order($dest);

        Appointment::create([
            'order_id' => $order->getKey(),
            'scheduled_at' => Carbon::now()->addDays(10)->toDateString(), // <=21d -> limited
            'status' => 'booked',
        ]);

        $this->artisan('availability:derive')->assertSuccessful();

        $snap = CentreAvailability::where('supply_node_id', $node->getKey())->first();
        $this->assertSame('limited', $snap->band);
    }

    public function test_derive_respects_manual_wins(): void
    {
        [$dest, $node] = $this->bookableDestinationWithCentre();
        $order = $this->order($dest);

        // Fresh manual snapshot already in place.
        CentreAvailability::create([
            'supply_node_id' => $node->getKey(),
            'next_available_on' => Carbon::now()->addDays(50)->toDateString(),
            'band' => 'good',
            'source' => 'manual',
            'confirmed_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        Appointment::create([
            'order_id' => $order->getKey(),
            'scheduled_at' => Carbon::now()->addDays(5)->toDateString(),
            'status' => 'booked',
        ]);

        $this->artisan('availability:derive')->assertSuccessful();

        $snap = CentreAvailability::where('supply_node_id', $node->getKey())->first();
        $this->assertSame('manual', $snap->source, 'Manual snapshot must survive a derive run.');
        $this->assertSame(
            Carbon::now()->addDays(50)->toDateString(),
            $snap->next_available_on->toDateString()
        );
    }

    public function test_derive_ignores_past_and_unbooked_and_non_schengen(): void
    {
        // Past booked appointment -> ignored.
        [$destPast, $nodePast] = $this->bookableDestinationWithCentre();
        Appointment::create([
            'order_id' => $this->order($destPast)->getKey(),
            'scheduled_at' => Carbon::now()->subDays(2)->toDateString(),
            'status' => 'booked',
        ]);

        // Future but only to_book (not confirmed) -> ignored.
        [$destToBook, $nodeToBook] = $this->bookableDestinationWithCentre();
        Appointment::create([
            'order_id' => $this->order($destToBook)->getKey(),
            'scheduled_at' => Carbon::now()->addDays(15)->toDateString(),
            'status' => 'to_book',
        ]);

        // Non-Schengen destination -> ignored.
        $nonSchengen = $this->destination(['visa_type' => 'eVisa']);
        $nodeNon = $this->node();
        $nonSchengen->supplyNodes()->attach($nodeNon->getKey());
        Appointment::create([
            'order_id' => $this->order($nonSchengen)->getKey(),
            'scheduled_at' => Carbon::now()->addDays(15)->toDateString(),
            'status' => 'booked',
        ]);

        $this->artisan('availability:derive')->assertSuccessful();

        $this->assertSame(0, CentreAvailability::count(), 'No snapshots should be derived.');
    }

    public function test_derive_succeeds_with_no_appointments(): void
    {
        $this->artisan('availability:derive')->assertSuccessful();
        $this->assertSame(0, CentreAvailability::count());
    }
}
