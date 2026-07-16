<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\ChecklistDelivery;
use App\Models\ChecklistRequest;
use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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
        $res->assertSee('name="email"', false);              // delivery step (email required)
        $res->assertDontSee('gate-tier');                    // old tier gate removed
    }

    public function test_submitting_the_wizard_captures_email_and_redirects_to_thanks(): void
    {
        Mail::fake();
        Destination::factory()->create(['name' => 'Turkey']);

        $res = $this->post('/document-checklist', [
            'destination' => 'Turkey',
            'email' => 'traveller@example.com',
            'residency_status' => 'citizen',
            'prior_refusal' => 'no',
        ]);

        $request = ChecklistRequest::query()->latest('id')->first();
        $this->assertNotNull($request);
        $this->assertNull($request->paid_at);
        $this->assertSame('traveller@example.com', $request->email);
        $res->assertRedirect("/document-checklist/sent/{$request->token}");

        Mail::assertQueued(ChecklistDelivery::class);
    }

    public function test_wizard_requires_an_email(): void
    {
        Destination::factory()->create(['name' => 'Turkey']);

        $res = $this->from('/document-checklist')->post('/document-checklist', [
            'destination' => 'Turkey',
        ]);

        $res->assertSessionHasErrors('email');
        $this->assertSame(0, ChecklistRequest::query()->count());
    }
}
