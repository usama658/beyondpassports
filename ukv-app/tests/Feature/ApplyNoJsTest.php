<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * No-JS apply regression guard (audit pass-2 LOW).
 *
 * The public apply form must keep working when JavaScript is unavailable / CSP-blocked,
 * i.e. a plain HTML form POST without the JSON Accept header. ApplyController detects the
 * non-JSON request and 302-redirects instead of returning a JSON body:
 *   standard       -> GET /checkout/{order_ref}
 *   manual_review  -> GET /apply  (+ session `status`)
 *
 * Field names mirror ApplyFlowTest / OrderEventsTest. Mail::fake()/Queue::fake() stop the
 * lifecycle email + HubSpot sync side-effects.
 */
final class ApplyNoJsTest extends TestCase
{
    use RefreshDatabase;

    private function makeDestination(array $overrides = []): Destination
    {
        return Destination::create(array_merge([
            'name' => 'Testlandia',
            'slug' => 'testlandia',
            'visa_type' => 'evisa',
            'govt_fee_gbp' => 20.00,
            'tier_standard_gbp' => 39.00,
            'tier_express_gbp' => 59.00,
            'tier_premium_gbp' => 89.00,
            'passport_validity_months' => 6,
        ], $overrides));
    }

    private function standardIntake(Destination $dest, array $overrides = []): array
    {
        return array_merge([
            'applicant_name' => 'Jane Traveller',
            'email' => 'jane@example.com',
            'phone' => '+44 7700 900000',
            'destination' => $dest->name,
            'tier' => 'standard',
            'trip_purpose' => 'tourist',
            'travel_date' => now()->addMonths(3)->toDateString(),
            'nationality' => 'United Kingdom',
            'residence_country' => 'United Kingdom',
            'residency_status' => 'citizen',
            'is_minor' => false,
            'prior_refusal' => false,
            'consent' => true,
            'begin_now' => true,
        ], $overrides);
    }

    public function test_no_js_standard_intake_redirects_to_checkout_not_json(): void
    {
        Mail::fake();
        Queue::fake();

        $dest = $this->makeDestination();

        // Plain form POST (no JSON Accept header) -> server-side redirect fallback.
        $response = $this->post('/apply', $this->standardIntake($dest));

        $order = Order::query()->latest('id')->firstOrFail();

        $response->assertStatus(302);
        $response->assertRedirect(route('checkout.create', ['order' => $order->order_ref]));
        // Guard against the JSON path leaking into a no-JS request.
        $this->assertStringNotContainsString('application/json', strtolower((string) $response->headers->get('Content-Type')));
    }

    public function test_no_js_manual_review_intake_redirects_to_apply_with_status(): void
    {
        Mail::fake();
        Queue::fake();

        $dest = $this->makeDestination();

        // Non-UK nationality routes to manual review; plain form POST -> redirect to /apply.
        $response = $this->post('/apply', $this->standardIntake($dest, [
            'email' => 'kofi@example.com',
            'nationality' => 'Ghana',
            'residence_country' => 'Ghana',
        ]));

        $response->assertStatus(302);
        $response->assertRedirect(route('apply.thanks'));
        $response->assertSessionHas('apply_thanks');
    }
}
