<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\DiscountContext;
use App\Models\Destination;
use App\Models\Discount;
use App\Models\Order;
use App\Services\GroupService;
use App\Services\LoyaltyService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * L2.7 / #177 — loyalty discount, review-incentive issuance, and trip-group linking.
 */
final class LoyaltyAndGroupsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake(); // intake fires lifecycle hooks; we don't assert on email here
    }

    private function makeDestination(): Destination
    {
        return Destination::create([
            'name' => 'Testlandia',
            'slug' => 'testlandia',
            'visa_type' => 'evisa',
            'tier_standard_gbp' => 39,
            'tier_express_gbp' => 59,
            'tier_premium_gbp' => 89,
            'govt_fee_gbp' => 20,
            'passport_validity_months' => 6,
        ]);
    }

    private function intake(Destination $dest, string $email): array
    {
        return [
            'applicant_name' => 'Jane Traveller',
            'email' => $email,
            'destination' => $dest->slug,
            'tier' => 'standard',
            'nationality' => 'UK',
            'residence_country' => 'UK',
            'residency_status' => 'citizen',
            'trip_purpose' => 'tourist',
            'prior_refusal' => false,
            'is_minor' => false,
            'consent' => true,
            'begin_now' => true,
        ];
    }

    // --- Loyalty discount -------------------------------------------------------------

    public function test_returning_customer_gets_a_loyalty_discount(): void
    {
        $dest = $this->makeDestination();
        $svc = app(OrderService::class);

        // First order at full price (service 39 + govt 20 = 59).
        $first = $svc->createFromIntake($this->intake($dest, 'repeat@example.com'));
        $this->assertEqualsWithDelta(39.00, (float) $first->service_fee, 0.001);
        $this->assertEqualsWithDelta(59.00, (float) $first->total, 0.001);

        // Second order, same email -> £10 off the service fee (govt untouched).
        $second = $svc->createFromIntake($this->intake($dest, 'repeat@example.com'));
        $this->assertEqualsWithDelta(29.00, (float) $second->service_fee, 0.001);
        $this->assertEqualsWithDelta(20.00, (float) $second->govt_fee, 0.001);
        $this->assertEqualsWithDelta(49.00, (float) $second->total, 0.001);

        // An auditable `loyal` Discount row was minted against the second order.
        $loyal = Discount::query()->where('context', DiscountContext::Loyal->value)->first();
        $this->assertNotNull($loyal);
        $this->assertSame($second->order_ref, $loyal->order_ref);
        $this->assertTrue((bool) $loyal->used);
        $this->assertEqualsWithDelta(10.00, (float) $loyal->amount, 0.001);

        // And a system event records the discount.
        $this->assertTrue(
            $second->events()->where('text', 'like', '%loyalty discount%')->exists()
        );
    }

    public function test_first_timer_does_not_get_a_discount(): void
    {
        $dest = $this->makeDestination();
        $svc = app(OrderService::class);

        $order = $svc->createFromIntake($this->intake($dest, 'newbie@example.com'));

        // Full price, no loyalty row, no discount event.
        $this->assertEqualsWithDelta(39.00, (float) $order->service_fee, 0.001);
        $this->assertEqualsWithDelta(59.00, (float) $order->total, 0.001);
        $this->assertSame(0, Discount::query()->where('context', DiscountContext::Loyal->value)->count());
        $this->assertFalse($order->events()->where('text', 'like', '%loyalty discount%')->exists());
    }

    public function test_loyalty_discount_caps_at_service_fee(): void
    {
        $svc = app(LoyaltyService::class);

        // Service fee below the fixed £10 reward -> discount is capped at the fee.
        $order = Order::create(['email' => 'cap@example.com', 'service_fee' => 4.00, 'total' => 24.00]);
        $this->assertEqualsWithDelta(4.00, $svc->loyaltyDiscountFor($order), 0.001);
    }

    // --- Review incentive -------------------------------------------------------------

    public function test_review_code_is_minted(): void
    {
        $order = Order::create(['email' => 'reviewer@example.com']);

        $discount = app(LoyaltyService::class)->issueReviewIncentive($order);

        $this->assertTrue($discount->exists);
        $this->assertSame(DiscountContext::Review, $discount->context);
        $this->assertStringStartsWith('REVIEW-', $discount->code);
        $this->assertFalse((bool) $discount->used);       // valid on a FUTURE order
        $this->assertNull($discount->order_ref);
        $this->assertSame('reviewer@example.com', $discount->email);
        $this->assertEqualsWithDelta(10.00, (float) $discount->amount, 0.001);
    }

    // --- Group linking ----------------------------------------------------------------

    public function test_group_linking_associates_orders(): void
    {
        $a = Order::create(['email' => 'a@family.com', 'name' => 'A']);
        $b = Order::create(['email' => 'b@family.com', 'name' => 'B']);
        $c = Order::create(['email' => 'c@family.com', 'name' => 'C']);

        $grp = app(GroupService::class);
        $gid = $grp->link([$a, $b, $c]);

        $this->assertStringStartsWith('GRP-', $gid);

        // All three share the id; deterministic (same set -> same id regardless of order).
        $this->assertSame($gid, $a->fresh()->group_id);
        $this->assertSame($gid, $b->fresh()->group_id);
        $this->assertSame($gid, $c->fresh()->group_id);
        $this->assertSame($gid, $grp->groupIdFor([$c->id, $a->id, $b->id]));

        // Members + siblings.
        $this->assertCount(3, $grp->members($gid));
        $this->assertCount(2, $grp->siblings($a->fresh()));

        // Each got a link audit event.
        $this->assertTrue($a->fresh()->events()->where('text', 'like', '%trip group%')->exists());
    }

    public function test_create_linked_order_groups_with_existing(): void
    {
        $dest = $this->makeDestination();
        $grp = app(GroupService::class);

        $first = app(OrderService::class)->createFromIntake($this->intake($dest, 'lead@family.com'));
        $second = $grp->createLinkedOrder($this->intake($dest, 'partner@family.com'), [$first]);

        $gid = $second->group_id;
        $this->assertNotEmpty($gid);
        $this->assertSame($gid, $first->fresh()->group_id);
        $this->assertCount(2, $grp->members($gid));
    }
}
