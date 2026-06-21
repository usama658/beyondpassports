<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\DeliverPaidChecklist;
use App\Models\ChecklistRequest;
use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class DeliverPaidChecklistTest extends TestCase
{
    use RefreshDatabase;

    private function paid(string $tier, ?string $email): ChecklistRequest
    {
        $d = Destination::factory()->create(['name' => 'Turkey']);

        $r = ChecklistRequest::create([
            'destination_id' => $d->id,
            'inputs' => [],
            'items' => [['document_key' => 'passport', 'label' => 'Valid passport', 'note' => null, 'category' => 'Identity', 'mandatory' => true]],
            'tier' => $tier,
            'email' => $email,
        ]);

        // paid_at is NOT in $fillable (payment-integrity); set via forceFill so isPaid() returns true.
        $r->forceFill(['paid_at' => now()])->save();

        return $r;
    }

    public function test_express_with_email_sends_mail(): void
    {
        Mail::fake();
        $r = $this->paid('express', 'buyer@example.com');

        (new DeliverPaidChecklist($r->id))->handle();

        Mail::assertSentCount(1);
    }

    public function test_standard_does_not_send_mail(): void
    {
        Mail::fake();
        $r = $this->paid('standard', 'buyer@example.com');

        (new DeliverPaidChecklist($r->id))->handle();

        Mail::assertNothingSent();
    }

    public function test_express_without_email_sends_nothing(): void
    {
        Mail::fake();
        $r = $this->paid('express', null);

        (new DeliverPaidChecklist($r->id))->handle();

        Mail::assertNothingSent();
    }
}
