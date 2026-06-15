<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChecklistRequest;
use App\Models\Destination;
use App\Models\DocumentRequirement;
use App\Services\ChecklistService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Public document-checklist tool (core) — build() reuses the engine, create() snapshots
 * the computed items behind a minted token so the saved request is stable across rule
 * changes.
 */
final class ChecklistServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): ChecklistService
    {
        return app(ChecklistService::class);
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

    public function test_build_returns_engine_items_for_the_case(): void
    {
        $dest = $this->makeDestination(['slug' => 'turkey']);

        $this->makeRule([
            'document_key' => 'passport',
            'label' => 'Passport',
            'conditions' => [],
        ]);
        $this->makeRule([
            'document_key' => 'turkey_form',
            'label' => 'Turkey entry form',
            'conditions' => ['destinations' => ['turkey']],
        ]);
        $this->makeRule([
            'document_key' => 'employer_letter',
            'label' => 'Employer letter',
            'conditions' => ['trip_purpose' => ['business']],
        ]);

        $touristKeys = $this->keys($this->service()->build($dest, ['trip_purpose' => 'tourist']));
        $businessKeys = $this->keys($this->service()->build($dest, ['trip_purpose' => 'business']));

        // Engine item shape is preserved.
        $first = $this->service()->build($dest, ['trip_purpose' => 'tourist'])[0];
        $this->assertSame(
            ['document_key', 'label', 'note', 'category', 'mandatory'],
            array_keys($first),
        );

        $this->assertContains('passport', $touristKeys);
        $this->assertContains('turkey_form', $touristKeys);
        $this->assertNotContains('employer_letter', $touristKeys);

        $this->assertContains('employer_letter', $businessKeys);
    }

    public function test_create_snapshots_items_and_mints_a_token(): void
    {
        $dest = $this->makeDestination();

        $this->makeRule(['document_key' => 'passport', 'label' => 'Passport', 'conditions' => []]);

        $inputs = ['trip_purpose' => 'tourist', 'is_minor' => false];
        $contact = [
            'email' => 'jane@example.com',
            'phone' => '+447700900000',
            'channels' => ['email', 'whatsapp'],
            'marketing_consent' => true,
            'ip' => '203.0.113.7',
        ];

        $request = $this->service()->create($dest, $inputs, $contact);

        $this->assertTrue($request->exists);
        $this->assertNotEmpty($request->token);
        $this->assertSame($dest->id, $request->destination_id);
        $this->assertSame($inputs, $request->inputs);
        $this->assertSame('jane@example.com', $request->email);
        $this->assertSame('+447700900000', $request->phone);
        $this->assertSame(['email', 'whatsapp'], $request->channels);
        $this->assertTrue($request->marketing_consent);
        $this->assertSame('203.0.113.7', $request->ip);

        // Stored items match what build() computes for the same case.
        $this->assertSame(
            $this->keys($this->service()->build($dest, $inputs)),
            $this->keys($request->items),
        );
        $this->assertContains('passport', $this->keys($request->items));
    }

    public function test_create_defaults_marketing_consent_false_without_contact(): void
    {
        $dest = $this->makeDestination();
        $this->makeRule(['document_key' => 'passport', 'label' => 'Passport', 'conditions' => []]);

        $request = $this->service()->create($dest, ['trip_purpose' => 'tourist']);

        $this->assertFalse($request->marketing_consent);
        $this->assertNull($request->email);
        $this->assertNull($request->channels);
    }

    public function test_snapshot_is_stable_when_rules_change_after_create(): void
    {
        $dest = $this->makeDestination();

        $passport = $this->makeRule(['document_key' => 'passport', 'label' => 'Passport', 'conditions' => []]);
        $extra = $this->makeRule(['document_key' => 'photo', 'label' => 'Photo', 'conditions' => []]);

        $request = $this->service()->create($dest, ['trip_purpose' => 'tourist']);
        $snapshotKeys = $this->keys($request->items);

        // Mutate the rules AFTER creation: relabel + deactivate one of them.
        $passport->update(['label' => 'CHANGED PASSPORT LABEL']);
        $extra->update(['active' => false]);

        $stored = ChecklistRequest::query()->whereKey($request->getKey())->first();

        // The persisted snapshot is unchanged: same keys, original label retained.
        $this->assertSame($snapshotKeys, $this->keys($stored->items));
        $labels = array_column($stored->items, 'label', 'document_key');
        $this->assertSame('Passport', $labels['passport']);
        $this->assertArrayHasKey('photo', $labels);
    }

    public function test_route_key_is_token(): void
    {
        $this->assertSame('token', (new ChecklistRequest)->getRouteKeyName());
    }
}
