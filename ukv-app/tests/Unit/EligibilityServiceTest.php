<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\EligibilityLane;
use App\Enums\OrderStatus;
use App\Enums\ResidencyStatus;
use App\Enums\TripPurpose;
use App\Models\Order;
use App\Services\EligibilityService;
use Tests\TestCase;

/**
 * Covers the eligibility router (evaluate), apply/override protection, and the
 * clearance gate. Pure logic — no DB persistence required; Order models are
 * built in-memory so casts apply but nothing is saved.
 */
final class EligibilityServiceTest extends TestCase
{
    private EligibilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EligibilityService;
    }

    /** @return array<string, mixed> A fully-qualifying standard-lane axis set. */
    private function standardAxes(array $overrides = []): array
    {
        return array_merge([
            'nationality' => 'United Kingdom',
            'residence_country' => 'GB',
            'residency_status' => 'citizen',
            'trip_purpose' => 'tourist',
            'prior_refusal' => false,
            'is_minor' => false,
        ], $overrides);
    }

    public function test_standard_lane_happy_path(): void
    {
        $this->assertSame(
            EligibilityLane::Standard,
            $this->service->evaluate($this->standardAxes())
        );
    }

    public function test_each_uk_alias_qualifies_for_standard(): void
    {
        $aliases = [
            'uk', 'gb', 'gbr', 'united kingdom', 'great britain', 'britain',
            'england', 'scotland', 'wales', 'northern ireland',
            '  UK  ', 'United Kingdom', 'ENGLAND',
        ];

        foreach ($aliases as $alias) {
            $this->assertSame(
                EligibilityLane::Standard,
                $this->service->evaluate($this->standardAxes([
                    'nationality' => $alias,
                    'residence_country' => $alias,
                ])),
                "Alias [{$alias}] should route to standard"
            );
        }
    }

    public function test_manual_review_when_non_uk_passport(): void
    {
        $this->assertSame(
            EligibilityLane::ManualReview,
            $this->service->evaluate($this->standardAxes(['nationality' => 'France']))
        );
    }

    public function test_manual_review_when_non_uk_residence(): void
    {
        $this->assertSame(
            EligibilityLane::ManualReview,
            $this->service->evaluate($this->standardAxes(['residence_country' => 'Spain']))
        );
    }

    public function test_manual_review_when_non_citizen(): void
    {
        foreach (['permanent', 'visa_holder', 'other', ''] as $status) {
            $this->assertSame(
                EligibilityLane::ManualReview,
                $this->service->evaluate($this->standardAxes(['residency_status' => $status])),
                "Status [{$status}] should route to manual_review"
            );
        }
    }

    public function test_manual_review_when_non_tourist(): void
    {
        foreach (['business', 'transit', 'study', 'other'] as $purpose) {
            $this->assertSame(
                EligibilityLane::ManualReview,
                $this->service->evaluate($this->standardAxes(['trip_purpose' => $purpose])),
                "Purpose [{$purpose}] should route to manual_review"
            );
        }
    }

    public function test_missing_trip_purpose_defaults_to_tourist(): void
    {
        $axes = $this->standardAxes();
        unset($axes['trip_purpose']);

        $this->assertSame(EligibilityLane::Standard, $this->service->evaluate($axes));
    }

    public function test_manual_review_when_minor(): void
    {
        $this->assertSame(
            EligibilityLane::ManualReview,
            $this->service->evaluate($this->standardAxes(['is_minor' => true]))
        );
    }

    public function test_manual_review_when_prior_refusal(): void
    {
        $this->assertSame(
            EligibilityLane::ManualReview,
            $this->service->evaluate($this->standardAxes(['prior_refusal' => true]))
        );

        // WP `! empty()` semantics: '1' is truthy, '' / '0' are falsey.
        $this->assertSame(
            EligibilityLane::ManualReview,
            $this->service->evaluate($this->standardAxes(['prior_refusal' => '1']))
        );
        $this->assertSame(
            EligibilityLane::Standard,
            $this->service->evaluate($this->standardAxes(['prior_refusal' => '0']))
        );
    }

    public function test_visa_entries_and_dual_nationality_do_not_change_routing(): void
    {
        // These are captured-only axes; supplying any value must not affect the lane.
        $standard = $this->service->evaluate($this->standardAxes([
            'visa_entries' => 'multiple',
            'dual_nationality' => 'France',
        ]));
        $this->assertSame(EligibilityLane::Standard, $standard);

        $manual = $this->service->evaluate($this->standardAxes([
            'nationality' => 'France',
            'visa_entries' => 'single',
            'dual_nationality' => 'United Kingdom',
        ]));
        $this->assertSame(EligibilityLane::ManualReview, $manual);
    }

    public function test_apply_sets_lane_and_stores_axes(): void
    {
        $order = new Order;
        $this->service->apply($order, $this->standardAxes([
            'visa_entries' => 'multiple',
            'dual_nationality' => 'Ireland',
        ]));

        $this->assertSame(EligibilityLane::Standard, $order->eligibility);
        $this->assertSame('United Kingdom', $order->nationality);
        $this->assertSame(ResidencyStatus::Citizen, $order->residency_status);
        $this->assertSame(TripPurpose::Tourist, $order->trip_purpose);
        $this->assertSame('multiple', $order->visa_entries);
        $this->assertSame('Ireland', $order->dual_nationality);
    }

    public function test_apply_computes_manual_review(): void
    {
        $order = new Order;
        $this->service->apply($order, $this->standardAxes(['nationality' => 'India']));

        $this->assertSame(EligibilityLane::ManualReview, $order->eligibility);
    }

    public function test_apply_never_overwrites_cleared_decision(): void
    {
        $order = new Order;
        $order->eligibility = EligibilityLane::Cleared;

        // Axes would otherwise compute manual_review — agent decision must survive.
        $this->service->apply($order, $this->standardAxes(['nationality' => 'Brazil']));

        $this->assertSame(EligibilityLane::Cleared, $order->eligibility);
        // ...but the axes are still stored.
        $this->assertSame('Brazil', $order->nationality);
    }

    public function test_apply_never_overwrites_referred_decision(): void
    {
        $order = new Order;
        $order->eligibility = EligibilityLane::Referred;

        // Axes would otherwise compute standard — referred must survive.
        $this->service->apply($order, $this->standardAxes());

        $this->assertSame(EligibilityLane::Referred, $order->eligibility);
    }

    public function test_is_cleared(): void
    {
        $this->assertTrue($this->isClearedFor(EligibilityLane::Standard));
        $this->assertTrue($this->isClearedFor(EligibilityLane::Cleared));
        $this->assertFalse($this->isClearedFor(EligibilityLane::ManualReview));
        $this->assertFalse($this->isClearedFor(EligibilityLane::Referred));

        // Null lane is not cleared.
        $this->assertFalse($this->service->isCleared(new Order));
    }

    private function isClearedFor(EligibilityLane $lane): bool
    {
        $order = new Order;
        $order->eligibility = $lane;

        return $this->service->isCleared($order);
    }

    public function test_gate_allows_cleared_orders_to_advance_past_paid(): void
    {
        foreach ([EligibilityLane::Standard, EligibilityLane::Cleared] as $lane) {
            $order = new Order;
            $order->eligibility = $lane;

            $this->assertTrue(
                $this->service->canAdvancePastPaid($order, OrderStatus::DocReview),
                "{$lane->value} should advance past paid"
            );
        }
    }

    public function test_gate_blocks_non_cleared_orders_past_paid(): void
    {
        foreach ([EligibilityLane::ManualReview, EligibilityLane::Referred] as $lane) {
            $order = new Order;
            $order->eligibility = $lane;

            // Can rest at 'paid'...
            $this->assertTrue(
                $this->service->canAdvancePastPaid($order, OrderStatus::Paid),
                "{$lane->value} may sit at paid"
            );

            // ...but cannot advance past it.
            $this->assertFalse(
                $this->service->canAdvancePastPaid($order, OrderStatus::AwaitingDocs),
                "{$lane->value} must be blocked past paid"
            );
            $this->assertFalse(
                $this->service->canAdvancePastPaid($order, 'submitted'),
                "{$lane->value} must be blocked when attempting a string status"
            );
        }
    }
}
