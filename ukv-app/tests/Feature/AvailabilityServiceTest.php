<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CentreAvailability;
use App\Models\Destination;
use App\Models\SupplyNode;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Marketing-side availability aggregation (AvailabilityService).
 *
 * Covers byDestination() aggregation (soonest date + best band across a destination's own centres
 * plus global bookable centres; all-expired/none => ask), setSnapshot() MANUAL-WINS (a derived
 * write must not clobber a fresh manual snapshot), and parseBulk() row validation. Rows are created
 * inline as the sibling tests do — there are no factories for these models.
 */
final class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    private function service(): AvailabilityService
    {
        return app(AvailabilityService::class);
    }

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

    private function snapshot(SupplyNode $node, array $attrs = []): CentreAvailability
    {
        return CentreAvailability::create(array_merge([
            'supply_node_id' => $node->getKey(),
            'next_available_on' => Carbon::now()->addDays(10)->toDateString(),
            'band' => 'good',
            'source' => 'manual',
            'confirmed_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addDays(5),
        ], $attrs));
    }

    // -----------------------------------------------------------------------------------------
    // byDestination()
    // -----------------------------------------------------------------------------------------

    public function test_by_destination_picks_soonest_date_and_best_band(): void
    {
        $dest = $this->destination();

        $nodeA = $this->node();
        $nodeB = $this->node();
        $dest->supplyNodes()->attach([$nodeA->getKey(), $nodeB->getKey()]);

        // Soonest date but only 'limited'.
        $this->snapshot($nodeA, [
            'next_available_on' => Carbon::now()->addDays(3)->toDateString(),
            'band' => 'limited',
        ]);
        // Later date but 'good' — best band should win for status, soonest date for the date.
        $this->snapshot($nodeB, [
            'next_available_on' => Carbon::now()->addDays(9)->toDateString(),
            'band' => 'good',
        ]);

        $result = $this->service()->byDestination('Schengen')[$dest->getKey()];

        $this->assertSame('ok', $result['status'], 'Any good band => ok.');
        $this->assertSame(
            Carbon::now()->addDays(3)->toDateString(),
            $result['next_available_on']->toDateString(),
            'Soonest date across centres wins.'
        );
        $this->assertNotNull($result['confirmed_at']);
    }

    public function test_by_destination_global_bookable_centre_applies_to_all(): void
    {
        $dest = $this->destination(); // no own centres

        // Global bookable centre with a good snapshot — applies to every destination.
        $global = $this->node(['is_global' => true, 'we_book_here' => true]);
        $this->snapshot($global, [
            'next_available_on' => Carbon::now()->addDays(4)->toDateString(),
            'band' => 'good',
        ]);

        $result = $this->service()->byDestination('Schengen')[$dest->getKey()];

        $this->assertSame('ok', $result['status']);
        $this->assertSame(
            Carbon::now()->addDays(4)->toDateString(),
            $result['next_available_on']->toDateString()
        );
    }

    public function test_by_destination_is_ask_when_all_expired_or_none(): void
    {
        $destExpired = $this->destination();
        $node = $this->node();
        $destExpired->supplyNodes()->attach($node->getKey());
        // Expired snapshot => contributes nothing.
        $this->snapshot($node, [
            'next_available_on' => Carbon::now()->addDays(3)->toDateString(),
            'band' => 'good',
            'expires_at' => Carbon::now()->subDay(),
        ]);

        // A second destination with no centres at all.
        $destNone = $this->destination();

        $out = $this->service()->byDestination('Schengen');

        foreach ([$destExpired, $destNone] as $d) {
            $this->assertSame('ask', $out[$d->getKey()]['status']);
            $this->assertNull($out[$d->getKey()]['next_available_on']);
            $this->assertNull($out[$d->getKey()]['confirmed_at']);
        }
    }

    // -----------------------------------------------------------------------------------------
    // setSnapshot() — MANUAL-WINS
    // -----------------------------------------------------------------------------------------

    public function test_set_snapshot_derived_does_not_overwrite_fresh_manual(): void
    {
        $node = $this->node();
        $manual = $this->snapshot($node, [
            'next_available_on' => Carbon::now()->addDays(7)->toDateString(),
            'band' => 'good',
            'source' => 'manual',
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        $returned = $this->service()->setSnapshot(
            supplyNodeId: $node->getKey(),
            nextAvailableOn: Carbon::now()->addDays(2),
            band: 'limited',
            source: 'derived',
        );

        // The fresh manual row is returned untouched.
        $this->assertSame('manual', $returned->source);
        $this->assertSame($manual->getKey(), $returned->getKey());
        $this->assertSame('good', $returned->band);
        $this->assertSame(
            Carbon::now()->addDays(7)->toDateString(),
            $returned->next_available_on->toDateString()
        );
    }

    public function test_set_snapshot_derived_overwrites_a_stale_manual(): void
    {
        $node = $this->node();
        $this->snapshot($node, [
            'source' => 'manual',
            'expires_at' => Carbon::now()->subDay(), // stale -> no longer protected
        ]);

        $returned = $this->service()->setSnapshot(
            supplyNodeId: $node->getKey(),
            nextAvailableOn: Carbon::now()->addDays(2),
            band: 'limited',
            source: 'derived',
        );

        $this->assertSame('derived', $returned->source);
        $this->assertSame('limited', $returned->band);
    }

    public function test_set_snapshot_null_date_forces_null_band(): void
    {
        $node = $this->node();

        $returned = $this->service()->setSnapshot($node->getKey(), null, 'good');

        $this->assertNull($returned->next_available_on);
        $this->assertNull($returned->band);
    }

    // -----------------------------------------------------------------------------------------
    // parseBulk()
    // -----------------------------------------------------------------------------------------

    public function test_parse_bulk_handles_valid_reset_bad_date_bad_band_and_unknown_slug(): void
    {
        $dest = $this->destination(['slug' => 'francia']);
        $node = $this->node();
        $dest->supplyNodes()->attach($node->getKey());

        $input = implode("\n", [
            'francia: 2026-12-01 good',   // valid
            'francia: ask',               // reset
            'francia: 2026-13-99 good',   // bad date
            'francia: 2026-12-01 maybe',  // bad band
            'nowhere: 2026-12-01 good',   // unknown slug
        ]);

        $result = $this->service()->parseBulk($input);

        $this->assertSame(2, $result['ok'], 'valid + reset are ok rows.');
        $this->assertSame(3, $result['errors'], 'bad date + bad band + unknown slug are errors.');

        [$valid, $reset, $badDate, $badBand, $unknown] = $result['rows'];

        $this->assertSame('2026-12-01', $valid['next_available_on']);
        $this->assertSame('good', $valid['band']);
        $this->assertNull($valid['error']);
        $this->assertSame($node->getKey(), $valid['node_id']);

        $this->assertTrue($reset['reset']);
        $this->assertNull($reset['error']);

        $this->assertStringContainsString('date', strtolower($badDate['error']));
        $this->assertStringContainsString('band', strtolower($badBand['error']));
        $this->assertStringContainsString('slug', strtolower($unknown['error']));
    }
}
