<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChecklistRequest;
use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ChecklistWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_wizard_shows_both_steps_and_no_tier_gate(): void
    {
        Destination::factory()->create(['name' => 'Turkey']);

        $res = $this->get('/document-checklist');
        $res->assertOk();
        $res->assertSee('name="residency_status"', false);   // step 2 reinstated
        $res->assertSee('name="prior_refusal"', false);      // step 2 reinstated
        $res->assertDontSee('gate-tier');                    // old tier gate removed
    }

    public function test_submitting_the_wizard_creates_an_unpaid_request_and_redirects(): void
    {
        $d = Destination::factory()->create(['name' => 'Turkey']);

        $res = $this->post('/document-checklist', [
            'destination' => 'Turkey',
            'residency_status' => 'citizen',
            'prior_refusal' => 'no',
        ]);

        $request = ChecklistRequest::query()->latest('id')->first();
        $this->assertNotNull($request);
        $this->assertNull($request->paid_at);
        $res->assertRedirect("/checklist/{$request->token}");
    }
}
