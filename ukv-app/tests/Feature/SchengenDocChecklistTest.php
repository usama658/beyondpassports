<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Destination;
use App\Services\RequirementService;
use Database\Seeders\SchengenDocumentRequirementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Schengen conditional document checklist (SchengenDocumentRequirementSeeder) — verifies the
 * branches fire on the right wizard inputs through the shared RequirementService.
 */
final class SchengenDocChecklistTest extends TestCase
{
    use RefreshDatabase;

    private function schengenDest(): Destination
    {
        $d = Destination::create([
            'name' => 'France', 'slug' => 'france', 'visa_type' => 'Schengen',
            'govt_fee_gbp' => 0, 'tier_standard_gbp' => 39, 'tier_express_gbp' => 59,
            'tier_premium_gbp' => 89, 'passport_validity_months' => 3,
        ]);
        $this->seed(SchengenDocumentRequirementSeeder::class);

        return $d;
    }

    /** @return list<string> */
    private function keys(Destination $d, array $ctx): array
    {
        return array_map(fn ($i) => $i['document_key'], app(RequirementService::class)->preview($d, $ctx));
    }

    public function test_all_schengen_applicants_get_core_items(): void
    {
        $keys = $this->keys($this->schengenDest(), ['trip_purpose' => 'tourist']);

        foreach (['cover-letter', 'appointment-confirmation', 'bank-statements-3m-recent', 'closing-balance', 'uk-ties-evidence'] as $k) {
            $this->assertContains($k, $keys);
        }
    }

    public function test_employed_branch(): void
    {
        $keys = $this->keys($this->schengenDest(), ['employment_status' => 'employed']);
        $this->assertContains('employer-leave-letter', $keys);
        $this->assertContains('payslips-3m', $keys);
        $this->assertNotContains('university-enrolment-letter', $keys);
    }

    public function test_student_branch(): void
    {
        $keys = $this->keys($this->schengenDest(), ['employment_status' => 'student']);
        $this->assertContains('university-enrolment-letter', $keys);
        $this->assertNotContains('employer-leave-letter', $keys);
    }

    public function test_sponsor_branch_and_residency_share_code(): void
    {
        $d = $this->schengenDest();

        $sponsor = $this->keys($d, ['funding_source' => 'sponsor', 'residency_status' => 'visa_holder']);
        $this->assertContains('sponsor-passport-id', $sponsor);
        $this->assertContains('sponsor-funds-evidence', $sponsor);
        $this->assertContains('uk-status-share-code', $sponsor, 'Non-British (visa_holder) needs a UK status share code.');

        $self = $this->keys($d, ['funding_source' => 'self', 'residency_status' => 'citizen']);
        $this->assertNotContains('sponsor-passport-id', $self);
        $this->assertNotContains('uk-status-share-code', $self, 'British citizen needs no share code.');
    }
}
