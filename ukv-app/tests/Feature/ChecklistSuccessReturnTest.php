<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChecklistRequest;
use App\Models\Destination;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

final class ChecklistSuccessReturnTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // The on-page result is drafted behind a flag; enable it so these tests exercise the page.
        config(['ukv.checklist.result_enabled' => true]);
    }

    public function test_valid_session_id_reveals_before_webhook_without_writing_paid_at(): void
    {
        $d = Destination::factory()->create(['name' => 'Turkey']);
        $r = ChecklistRequest::create([
            'destination_id' => $d->id,
            'inputs' => [],
            'items' => [['document_key' => 'bank', 'label' => 'Bank statements', 'note' => null, 'category' => 'Finance', 'mandatory' => true]],
        ]);

        $mock = Mockery::mock(StripeService::class);
        $mock->shouldReceive('isChecklistSessionPaid')
            ->with($r->token, 'cs_live_1')->once()->andReturn(true);
        $this->app->instance(StripeService::class, $mock);

        $res = $this->get("/checklist/{$r->token}?session_id=cs_live_1");
        $res->assertOk();
        $res->assertSee('Bank statements'); // revealed via read-only verify

        $r->refresh();
        $this->assertNull($r->paid_at); // success return must NOT write paid_at (webhook does)
    }

    public function test_invalid_session_id_stays_redacted(): void
    {
        $d = Destination::factory()->create(['name' => 'Turkey']);
        $r = ChecklistRequest::create([
            'destination_id' => $d->id,
            'inputs' => [],
            'items' => [['document_key' => 'bank', 'label' => 'Bank statements', 'note' => null, 'category' => 'Finance', 'mandatory' => true]],
        ]);

        $mock = Mockery::mock(StripeService::class);
        $mock->shouldReceive('isChecklistSessionPaid')->andReturn(false);
        $this->app->instance(StripeService::class, $mock);

        $this->get("/checklist/{$r->token}?session_id=bad")->assertDontSee('Bank statements');
    }
}
