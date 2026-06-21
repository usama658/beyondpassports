<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChecklistRequest;
use App\Models\Destination;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ChecklistPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function unpaid(array $attrs = []): ChecklistRequest
    {
        $d = Destination::factory()->create(['tier_standard_gbp' => 35]);

        return ChecklistRequest::create(array_merge([
            'destination_id' => $d->id,
            'inputs' => [],
            'items' => [['document_key' => 'passport', 'label' => 'Valid passport', 'note' => null, 'category' => 'Identity', 'mandatory' => true]],
            'tier' => 'standard',
            'amount_gbp' => 35,
        ], $attrs));
    }

    public function test_mark_checklist_paid_sets_paid_at_and_is_idempotent(): void
    {
        $r = $this->unpaid();
        $svc = app(StripeService::class);

        $svc->markChecklistPaidByToken($r->token, 'cs_test_1', 'buyer@example.com');
        $r->refresh();
        $firstPaidAt = $r->paid_at;

        $this->assertNotNull($firstPaidAt);
        $this->assertSame('cs_test_1', $r->stripe_session_id);
        $this->assertSame('buyer@example.com', $r->email);

        // Replay: must not move paid_at or change anything.
        $svc->markChecklistPaidByToken($r->token, 'cs_test_2', 'other@example.com');
        $r->refresh();
        $this->assertEquals($firstPaidAt, $r->paid_at);
        $this->assertSame('cs_test_1', $r->stripe_session_id);
    }

    public function test_mark_checklist_paid_unknown_token_is_noop(): void
    {
        app(StripeService::class)->markChecklistPaidByToken('no-such-token', 'cs_x', null);
        $this->assertTrue(true); // no exception
    }
}
