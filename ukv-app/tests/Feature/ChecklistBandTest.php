<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ChecklistBandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Per-country /visa/{slug} pages are drafted behind a flag; enable so these tests render them.
        config(['ukv.destinations.country_pages_enabled' => true]);
    }

    public function test_generic_band_links_to_the_tool(): void
    {
        $html = view('partials.checklist-band')->render();

        $this->assertStringContainsString(route('checklist.tool'), $html);
        $this->assertStringContainsString('Build my checklist', $html);
        $this->assertStringNotContainsString('?destination=', $html);
    }

    public function test_destination_band_deep_links_with_country_preselected(): void
    {
        $html = view('partials.checklist-band', ['cbDestination' => 'Germany'])->render();

        $this->assertStringContainsString('?destination=Germany', $html);
        $this->assertStringContainsString('Germany needs', $html);
    }

    public function test_tool_preselects_destination_from_query(): void
    {
        Destination::factory()->create(['name' => 'Germany']);

        $this->get('/document-checklist?destination=Germany')
            ->assertOk()
            ->assertSee('<option value="Germany" selected>Germany</option>', false);
    }

    public function test_destination_page_shows_the_band_deep_linked_to_its_country(): void
    {
        // Money pages are Schengen-only since the pivot.
        Destination::create([
            'name' => 'Germany',
            'slug' => 'germany',
            'visa_type' => 'Schengen',
            'govt_fee_gbp' => 80.00,
            'tier_standard_gbp' => 39.00,
            'tier_express_gbp' => 59.00,
            'tier_premium_gbp' => 89.00,
            'passport_validity_months' => 6,
        ]);

        $this->get('/visa/germany')->assertOk()
            ->assertSee('cl-band', false)
            ->assertSee('?destination=Germany', false);
    }

    public function test_tools_hub_shows_the_generic_band(): void
    {
        $this->get('/tools')->assertOk()->assertSee('cl-band', false);
    }

    public function test_compact_variant_adds_the_modifier_class(): void
    {
        $full = view('partials.checklist-band')->render();
        $compact = view('partials.checklist-band', ['cbCompact' => true])->render();

        // The class name appears in the scoped <style> either way; assert on the wrapper div class.
        $this->assertStringContainsString('class="cl-band"', $full);
        $this->assertStringContainsString('class="cl-band cl-band--compact"', $compact);
    }

    public function test_destination_page_carries_a_compact_scroll_capture_strip(): void
    {
        Destination::create([
            'name' => 'Germany',
            'slug' => 'germany',
            'visa_type' => 'Schengen',
            'govt_fee_gbp' => 80.00,
            'tier_standard_gbp' => 39.00,
            'tier_express_gbp' => 59.00,
            'tier_premium_gbp' => 89.00,
            'passport_validity_months' => 6,
        ]);

        $this->get('/visa/germany')->assertOk()->assertSee('cl-band--compact', false);
    }
}
