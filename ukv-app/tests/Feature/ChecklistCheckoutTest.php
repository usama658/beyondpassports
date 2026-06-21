<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChecklistRequest;
use App\Models\Destination;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

final class ChecklistCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function request(): ChecklistRequest
    {
        $d = Destination::factory()->create(['tier_standard_gbp' => 35, 'tier_express_gbp' => 55]);

        return ChecklistRequest::create([
            'destination_id' => $d->id,
            'inputs' => [],
            'items' => [['document_key' => 'passport', 'label' => 'Valid passport', 'note' => null, 'category' => 'Identity', 'mandatory' => true]],
        ]);
    }

    public function test_checkout_requires_consent(): void
    {
        $r = $this->request();

        $this->post("/checklist/{$r->token}/checkout", ['tier' => 'standard'])
            ->assertSessionHasErrors('consent');

        $r->refresh();
        $this->assertNull($r->paid_at);
        $this->assertNull($r->tier);
    }

    public function test_checkout_with_consent_snapshots_and_redirects_to_stripe(): void
    {
        $r = $this->request();

        $mock = Mockery::mock(StripeService::class);
        $mock->shouldReceive('createChecklistSession')
            ->once()
            ->andReturn('https://checkout.stripe.test/session');
        $this->app->instance(StripeService::class, $mock);

        $this->post("/checklist/{$r->token}/checkout", [
            'tier' => 'express',
            'consent' => '1',
            'email' => 'buyer@example.com',
        ])->assertRedirect('https://checkout.stripe.test/session');

        $r->refresh();
        $this->assertSame('express', $r->tier);
        $this->assertSame('55.00', (string) $r->amount_gbp);
        $this->assertTrue($r->immediate_delivery_consent);
        $this->assertNotNull($r->consent_at);
        $this->assertSame('buyer@example.com', $r->email);
    }

    public function test_checkout_when_already_paid_redirects_to_result_without_new_session(): void
    {
        $r = $this->request();
        $r->update(['tier' => 'standard']);
        $r->forceFill(['paid_at' => now()])->save();

        $mock = Mockery::mock(StripeService::class);
        $mock->shouldNotReceive('createChecklistSession');
        $this->app->instance(StripeService::class, $mock);

        $this->post("/checklist/{$r->token}/checkout", ['tier' => 'standard', 'consent' => '1'])
            ->assertRedirect("/checklist/{$r->token}");
    }
}
