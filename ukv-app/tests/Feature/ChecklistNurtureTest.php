<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\ChecklistNurture;
use App\Models\ChecklistRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class ChecklistNurtureTest extends TestCase
{
    use RefreshDatabase;

    /** @param array<string,mixed> $attrs */
    private function lead(array $attrs = []): ChecklistRequest
    {
        $lead = ChecklistRequest::create(array_merge([
            'email' => 'lead@example.com',
            'marketing_consent' => true,
            'inputs' => ['destination' => 'Germany'],
            'items' => [],
        ], $attrs));

        // created_at is set by timestamps; override when a test needs an age.
        if (isset($attrs['created_at'])) {
            $lead->forceFill(['created_at' => $attrs['created_at']])->save();
        }

        return $lead;
    }

    public function test_eligible_lead_is_emailed_once_and_stamped(): void
    {
        Mail::fake();
        $lead = $this->lead(['created_at' => now()->subDays(3)]);

        $this->artisan('ukv:nurture-checklists')->assertSuccessful();

        Mail::assertQueued(ChecklistNurture::class, 1);
        $this->assertNotNull($lead->fresh()->nurture_sent_at);
    }

    public function test_no_consent_is_skipped(): void
    {
        Mail::fake();
        $this->lead(['created_at' => now()->subDays(3), 'marketing_consent' => false]);

        $this->artisan('ukv:nurture-checklists')->assertSuccessful();

        Mail::assertNothingQueued();
    }

    public function test_missing_email_is_skipped(): void
    {
        Mail::fake();
        $this->lead(['created_at' => now()->subDays(3), 'email' => null]);

        $this->artisan('ukv:nurture-checklists')->assertSuccessful();

        Mail::assertNothingQueued();
    }

    public function test_too_recent_is_skipped(): void
    {
        Mail::fake();
        $this->lead(['created_at' => now()->subHours(6)]);

        $this->artisan('ukv:nurture-checklists')->assertSuccessful();

        Mail::assertNothingQueued();
    }

    public function test_older_than_thirty_days_is_skipped(): void
    {
        Mail::fake();
        $this->lead(['created_at' => now()->subDays(40)]);

        $this->artisan('ukv:nurture-checklists')->assertSuccessful();

        Mail::assertNothingQueued();
    }

    public function test_run_is_idempotent(): void
    {
        Mail::fake();
        $this->lead(['created_at' => now()->subDays(3)]);

        $this->artisan('ukv:nurture-checklists')->assertSuccessful();
        $this->artisan('ukv:nurture-checklists')->assertSuccessful();

        Mail::assertQueued(ChecklistNurture::class, 1); // second run sends nothing
    }
}
