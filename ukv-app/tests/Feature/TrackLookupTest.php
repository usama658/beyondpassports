<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EligibilityLane;
use App\Enums\OrderStatus;
use App\Models\Destination;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Public status tracker (POST /track/lookup -> TrackController::lookup).
 *
 * Privacy is the load-bearing contract: a hit shows a stage timeline but NEVER the
 * customer's name/email/destination/fees; a miss returns a generic not-found (no 500,
 * no PII), and the copy is identical regardless of why the lookup failed.
 */
final class TrackLookupTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        $dest = Destination::create([
            'name' => 'Secretland',
            'slug' => 'secretland',
            'govt_fee_gbp' => 20.00,
            'tier_standard_gbp' => 39.00,
            'tier_express_gbp' => 59.00,
            'tier_premium_gbp' => 89.00,
            'passport_validity_months' => 6,
        ]);

        return Order::create(array_merge([
            'name' => 'Wilhelmina Featherstonehaugh',
            'applicant_name' => 'Wilhelmina Featherstonehaugh',
            'email' => 'wilhelmina.secret@example.com',
            'destination_id' => $dest->getKey(),
            'destination_name' => 'Secretland',
            'status' => OrderStatus::Submitted->value,
            'eligibility' => EligibilityLane::Standard->value,
        ], $overrides));
    }

    public function test_known_ref_returns_200_and_shows_a_stage(): void
    {
        $order = $this->makeOrder();

        $response = $this->post('/track/lookup', ['ref' => $order->order_ref]);

        $response->assertStatus(200);
        // A real result renders the stage timeline; `aria-current="step"` marks the live
        // stage and appears ONLY on a hit (never in the CSS or on the not-found page).
        $response->assertSee('aria-current="step"', escape: false);
    }

    public function test_known_ref_never_leaks_customer_pii(): void
    {
        $order = $this->makeOrder();

        $response = $this->post('/track/lookup', ['ref' => $order->order_ref]);

        $response->assertStatus(200);
        // No name / email / fees ever reach the page at all.
        $response->assertDontSee('Wilhelmina', escape: false);
        $response->assertDontSee('Featherstonehaugh', escape: false);
        $response->assertDontSee('wilhelmina.secret@example.com', escape: false);
        // The destination name legitimately appears in the global destinations mega-menu
        // (public catalog nav), so the leak check is scoped to the tracker output itself:
        // TrackController must never pass the order's destination into the status view.
        $main = \Illuminate\Support\Str::between($response->getContent(), '<main', '</main>');
        $this->assertStringNotContainsString('Secretland', $main);
    }

    public function test_unknown_ref_returns_generic_not_found_without_pii_or_500(): void
    {
        // Create a real order so we can prove an unknown ref doesn't surface it.
        $order = $this->makeOrder();

        $response = $this->post('/track/lookup', ['ref' => 'UKV-2099-999999']);

        $response->assertStatus(200); // generic page, not an error
        $response->assertDontSee('Wilhelmina', escape: false);
        $response->assertDontSee('wilhelmina.secret@example.com', escape: false);
        // Destination names live in the global mega-menu (public catalog); scope the
        // not-found leak check to the tracker output, which must reveal nothing.
        $main = \Illuminate\Support\Str::between($response->getContent(), '<main', '</main>');
        $this->assertStringNotContainsString('Secretland', $main);
        // Generic miss copy is shown.
        $response->assertSee('find an application', escape: false);
    }

    public function test_lookup_is_exact_match_not_partial(): void
    {
        $order = $this->makeOrder();

        // A prefix of a real ref must NOT match (no enumeration via LIKE).
        $prefix = substr($order->order_ref, 0, 8);

        $response = $this->post('/track/lookup', ['ref' => $prefix]);

        $response->assertStatus(200);
        $response->assertSee('find an application', escape: false); // treated as a miss
        // No stage timeline on a miss. (Bare aria-current="page" lives in the nav, so assert
        // the timeline-specific step marker is absent.)
        $response->assertDontSee('aria-current="step"', escape: false);
    }
}
