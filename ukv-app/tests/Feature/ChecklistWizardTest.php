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
        $res->assertDontSee('gate-tier');                    // old tier gate removed
    }

    public function test_submitting_the_wizard_redirects_to_whatsapp_thanks_without_email(): void
    {
        Mail::fake();
        Destination::factory()->create(['name' => 'Turkey']);

        $res = $this->post('/document-checklist', [
            'destination' => 'Turkey',
            'residency_status' => 'citizen',
            'prior_refusal' => 'no',
        ]);

        $request = ChecklistRequest::query()->latest('id')->first();
        $this->assertNotNull($request);
        $this->assertNull($request->paid_at);
        $this->assertNull($request->email);
        $res->assertRedirect("/document-checklist/sent/{$request->token}");

        // Team gets the ready checklist to paste into the WhatsApp reply (even with no visitor email).
        Mail::assertQueued(ChecklistDelivery::class, fn ($mail) => $mail->forTeam === true);
        Mail::assertQueued(ChecklistDelivery::class, 1);
    }

    public function test_optional_email_sends_visitor_copy_plus_team_copy(): void
    {
        Mail::fake();
        Destination::factory()->create(['name' => 'Turkey']);

        $res = $this->post('/document-checklist', [
            'destination' => 'Turkey',
            'email' => 'traveller@example.com',
        ]);

        $request = ChecklistRequest::query()->latest('id')->first();
        $this->assertSame('traveller@example.com', $request->email);
        $res->assertRedirect("/document-checklist/sent/{$request->token}");

        // Two ChecklistDelivery sends: team copy (forTeam) + visitor copy.
        Mail::assertQueued(ChecklistDelivery::class, 2);
        Mail::assertQueued(ChecklistDelivery::class, fn ($mail) => $mail->forTeam === true);
        Mail::assertQueued(ChecklistDelivery::class, fn ($mail) => $mail->forTeam === false);
    }
}
