<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\AppointmentEnquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class AppointmentEnquiryEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_only_lead_is_captured(): void
    {
        Mail::fake();
        config(['ukv.owner_email' => 'ops@example.com']);

        $this->postJson('/appointment-enquiry', ['email' => 'lead@example.com', 'source' => '/schengen-visa-agent'])
            ->assertOk()->assertJson(['ok' => true]);

        // Mailable is ShouldQueue, so the fake records it as queued.
        Mail::assertQueued(AppointmentEnquiry::class, fn ($m) => $m->leadEmail === 'lead@example.com');
    }

    public function test_all_blank_is_rejected(): void
    {
        Mail::fake();

        $this->postJson('/appointment-enquiry', ['source' => '/x'])->assertStatus(422);

        Mail::assertNothingOutgoing();
    }

    public function test_invalid_email_is_rejected(): void
    {
        $this->postJson('/appointment-enquiry', ['email' => 'not-an-email'])->assertStatus(422);
    }

    public function test_lp_form_partial_exposes_the_email_fallback_field(): void
    {
        $html = view('partials.lp-appt-form', ['bpcWa' => '447882747584'])->render();

        $this->assertStringContainsString('name="e"', $html);
        $this->assertStringContainsString('type="email"', $html);
    }
}
