<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\DocumentRequirement;
use App\Models\Order;
use App\Services\RequirementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Document-requirements engine — rule matching, computed conditions, de-dupe + ordering.
 *
 * The orders table gains employment_status / accommodation_type / funding_source /
 * return_date / payer_is_applicant from a sibling migration (2026_06_16_000011); those
 * columns are assumed present at test runtime and set via Order::create / forceFill.
 */
final class RequirementServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): RequirementService
    {
        return app(RequirementService::class);
    }

    private function makeDestination(array $overrides = []): Destination
    {
        return Destination::create(array_merge([
            'name' => 'Turkey',
            'slug' => 'turkey',
            'visa_type' => 'evisa',
            'govt_fee_gbp' => 20.00,
            'tier_standard_gbp' => 39.00,
            'tier_express_gbp' => 59.00,
            'tier_premium_gbp' => 89.00,
            'passport_validity_months' => 6,
        ], $overrides));
    }

    private function makeOrder(Destination $dest, array $attrs = []): Order
    {
        $order = new Order;
        $order->forceFill(array_merge([
            'name' => 'Jane Traveller',
            'email' => 'jane@example.com',
            'destination_id' => $dest->id,
            'destination_name' => $dest->name,
            'tier' => 'standard',
            'status' => 'paid',
            'trip_purpose' => 'tourist',
            'is_minor' => false,
            'prior_refusal' => false,
            'payer_is_applicant' => true,
        ], $attrs));
        $order->save();

        return $order->fresh();
    }

    private function makeRule(array $attrs): DocumentRequirement
    {
        return DocumentRequirement::create(array_merge([
            'document_key' => 'doc-'.uniqid(),
            'label' => 'A document',
            'note' => null,
            'category' => 'core',
            'conditions' => null,
            'mandatory' => true,
            'active' => true,
            'sort_order' => 0,
        ], $attrs));
    }

    private function keys(array $items): array
    {
        return array_map(static fn (array $i): string => $i['document_key'], $items);
    }

    public function test_empty_conditions_rule_is_always_returned(): void
    {
        $dest = $this->makeDestination();
        $order = $this->makeOrder($dest);

        $this->makeRule(['document_key' => 'passport', 'label' => 'Passport', 'conditions' => null]);
        $this->makeRule(['document_key' => 'photo', 'label' => 'Photo', 'conditions' => []]);

        $keys = $this->keys($this->service()->for($order));

        $this->assertContains('passport', $keys);
        $this->assertContains('photo', $keys);
    }

    public function test_destination_scoped_rule_only_for_that_destination(): void
    {
        $turkey = $this->makeDestination(['name' => 'Turkey', 'slug' => 'turkey']);
        $india = $this->makeDestination(['name' => 'India', 'slug' => 'india']);

        $this->makeRule([
            'document_key' => 'turkey_form',
            'label' => 'Turkey entry form',
            'conditions' => ['destinations' => ['turkey']],
        ]);

        $turkeyKeys = $this->keys($this->service()->for($this->makeOrder($turkey)));
        $indiaKeys = $this->keys($this->service()->for($this->makeOrder($india)));

        $this->assertContains('turkey_form', $turkeyKeys);
        $this->assertNotContains('turkey_form', $indiaKeys);
    }

    public function test_trip_purpose_business_rule_excluded_for_tourist(): void
    {
        $dest = $this->makeDestination();

        $this->makeRule([
            'document_key' => 'employer_letter',
            'label' => 'Employer letter',
            'conditions' => ['trip_purpose' => ['business']],
        ]);

        $touristKeys = $this->keys($this->service()->for($this->makeOrder($dest, ['trip_purpose' => 'tourist'])));
        $businessKeys = $this->keys($this->service()->for($this->makeOrder($dest, ['trip_purpose' => 'business'])));

        $this->assertNotContains('employer_letter', $touristKeys);
        $this->assertContains('employer_letter', $businessKeys);
    }

    public function test_is_minor_rule_only_for_a_minor(): void
    {
        $dest = $this->makeDestination();

        $this->makeRule([
            'document_key' => 'parental_consent',
            'label' => 'Parental consent letter',
            'conditions' => ['is_minor' => true],
        ]);

        $minorKeys = $this->keys($this->service()->for($this->makeOrder($dest, ['is_minor' => true])));
        $adultKeys = $this->keys($this->service()->for($this->makeOrder($dest, ['is_minor' => false])));

        $this->assertContains('parental_consent', $minorKeys);
        $this->assertNotContains('parental_consent', $adultKeys);
    }

    public function test_computed_passport_validity_short_true_and_false(): void
    {
        // Destination requires 6 months passport validity beyond travel.
        $dest = $this->makeDestination(['passport_validity_months' => 6]);

        $this->makeRule([
            'document_key' => 'passport_renewal',
            'label' => 'Renew your passport',
            'conditions' => ['passport_validity_short' => true],
        ]);

        // travel + 6 months = required date. Expiry BEFORE that => short (true).
        $shortOrder = $this->makeOrder($dest, [
            'travel_date' => '2026-07-01',
            'passport_expiry' => '2026-09-01', // only ~2 months after travel -> short
        ]);
        // Expiry well beyond required date => not short.
        $longOrder = $this->makeOrder($dest, [
            'travel_date' => '2026-07-01',
            'passport_expiry' => '2027-07-01', // 12 months after travel -> not short
        ]);

        $this->assertContains('passport_renewal', $this->keys($this->service()->for($shortOrder)));
        $this->assertNotContains('passport_renewal', $this->keys($this->service()->for($longOrder)));
    }

    public function test_passport_short_rule_excluded_when_dates_missing(): void
    {
        $dest = $this->makeDestination();

        $this->makeRule([
            'document_key' => 'passport_renewal',
            'label' => 'Renew your passport',
            'conditions' => ['passport_validity_short' => true],
        ]);

        // No travel_date / passport_expiry set -> computed value unknown -> excluded.
        $order = $this->makeOrder($dest);

        $this->assertNotContains('passport_renewal', $this->keys($this->service()->for($order)));
    }

    public function test_stay_length_min_and_max(): void
    {
        $dest = $this->makeDestination();

        $this->makeRule([
            'document_key' => 'long_stay_proof',
            'label' => 'Proof of funds for a long stay',
            'conditions' => ['min_stay_days' => 30],
        ]);
        $this->makeRule([
            'document_key' => 'short_trip_note',
            'label' => 'Short trip note',
            'conditions' => ['max_stay_days' => 14],
        ]);

        // 45-day stay: matches min_stay_days>=30, fails max_stay_days<=14.
        $longStay = $this->makeOrder($dest, [
            'travel_date' => '2026-07-01',
            'return_date' => '2026-08-15',
        ]);
        // 7-day stay: fails min, matches max.
        $shortStay = $this->makeOrder($dest, [
            'travel_date' => '2026-07-01',
            'return_date' => '2026-07-08',
        ]);
        // Missing return_date -> both stay rules excluded.
        $unknownStay = $this->makeOrder($dest, ['travel_date' => '2026-07-01']);

        $longKeys = $this->keys($this->service()->for($longStay));
        $shortKeys = $this->keys($this->service()->for($shortStay));
        $unknownKeys = $this->keys($this->service()->for($unknownStay));

        $this->assertContains('long_stay_proof', $longKeys);
        $this->assertNotContains('short_trip_note', $longKeys);

        $this->assertContains('short_trip_note', $shortKeys);
        $this->assertNotContains('long_stay_proof', $shortKeys);

        $this->assertNotContains('long_stay_proof', $unknownKeys);
        $this->assertNotContains('short_trip_note', $unknownKeys);
    }

    public function test_dedupe_by_document_key_mandatory_wins(): void
    {
        $dest = $this->makeDestination();
        $order = $this->makeOrder($dest, ['trip_purpose' => 'business']);

        // A recommended baseline rule comes first (lower sort_order)...
        $this->makeRule([
            'document_key' => 'employer_letter',
            'label' => 'Employer letter (recommended)',
            'conditions' => [],
            'mandatory' => false,
            'sort_order' => 1,
        ]);
        // ...and a mandatory business-scoped rule for the same key comes later.
        $this->makeRule([
            'document_key' => 'employer_letter',
            'label' => 'Employer letter (required)',
            'conditions' => ['trip_purpose' => ['business']],
            'mandatory' => true,
            'sort_order' => 2,
        ]);

        $items = $this->service()->for($order);
        $matches = array_values(array_filter($items, static fn (array $i): bool => $i['document_key'] === 'employer_letter'));

        $this->assertCount(1, $matches, 'document_key must be de-duped to a single entry.');
        $this->assertTrue($matches[0]['mandatory'], 'A mandatory match must upgrade the de-duped entry.');
    }

    public function test_results_are_ordered_by_sort_order_then_label(): void
    {
        $dest = $this->makeDestination();
        $order = $this->makeOrder($dest);

        $this->makeRule(['document_key' => 'c', 'label' => 'Charlie', 'sort_order' => 10]);
        $this->makeRule(['document_key' => 'a', 'label' => 'Alpha', 'sort_order' => 5]);
        $this->makeRule(['document_key' => 'b', 'label' => 'Bravo', 'sort_order' => 5]);

        // sort_order: a(5,Alpha), b(5,Bravo), c(10,Charlie)
        $this->assertSame(['a', 'b', 'c'], $this->keys($this->service()->for($order)));
    }

    public function test_preview_evaluates_destination_and_context(): void
    {
        $dest = $this->makeDestination(['slug' => 'turkey']);

        $this->makeRule([
            'document_key' => 'turkey_form',
            'label' => 'Turkey form',
            'conditions' => ['destinations' => ['turkey']],
        ]);
        $this->makeRule([
            'document_key' => 'employer_letter',
            'label' => 'Employer letter',
            'conditions' => ['trip_purpose' => ['business']],
        ]);

        $touristKeys = $this->keys($this->service()->preview($dest, ['trip_purpose' => 'tourist']));
        $businessKeys = $this->keys($this->service()->preview($dest, ['trip_purpose' => 'business']));

        $this->assertContains('turkey_form', $touristKeys);
        $this->assertNotContains('employer_letter', $touristKeys);

        $this->assertContains('turkey_form', $businessKeys);
        $this->assertContains('employer_letter', $businessKeys);
    }

    public function test_inactive_rules_are_ignored(): void
    {
        $dest = $this->makeDestination();
        $order = $this->makeOrder($dest);

        $this->makeRule([
            'document_key' => 'retired_doc',
            'label' => 'Retired doc',
            'conditions' => [],
            'active' => false,
        ]);

        $this->assertNotContains('retired_doc', $this->keys($this->service()->for($order)));
    }
}
