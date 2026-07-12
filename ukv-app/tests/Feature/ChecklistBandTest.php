<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ChecklistBandTest extends TestCase
{
    use RefreshDatabase;

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
}
