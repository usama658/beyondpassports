<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChecklistRequest;
use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ChecklistRevealTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // The on-page result is drafted behind a flag; enable it so these tests exercise the page.
        config(['ukv.checklist.result_enabled' => true]);
    }

    private function request(array $attrs = []): ChecklistRequest
    {
        $d = Destination::factory()->create(['name' => 'Turkey', 'tier_standard_gbp' => 35, 'tier_express_gbp' => 55, 'tier_premium_gbp' => 85]);

        $r = ChecklistRequest::create(array_merge([
            'destination_id' => $d->id,
            'inputs' => [],
            'items' => [
                ['document_key' => 'passport', 'label' => 'Valid passport', 'note' => '6 months', 'category' => 'Identity', 'mandatory' => true],
                ['document_key' => 'bank', 'label' => 'Bank statements', 'note' => null, 'category' => 'Finance', 'mandatory' => false],
            ],
        ], array_diff_key($attrs, ['paid_at' => null])));

        if (isset($attrs['paid_at'])) {
            $r->forceFill(['paid_at' => $attrs['paid_at']])->save();
        }

        return $r;
    }

    public function test_unpaid_page_redacts_real_labels_and_shows_tiers(): void
    {
        $r = $this->request();

        $res = $this->get("/checklist/{$r->token}");
        $res->assertOk();
        // Gate integrity: the second (non-teaser) real label must NOT appear in the DOM.
        $res->assertDontSee('Bank statements');
        // Tier prices are shown.
        $res->assertSee('55');
        $res->assertSee('Express');
    }

    public function test_paid_page_reveals_full_list(): void
    {
        $r = $this->request(['paid_at' => now(), 'tier' => 'express']);

        $res = $this->get("/checklist/{$r->token}");
        $res->assertOk();
        $res->assertSee('Valid passport');
        $res->assertSee('Bank statements');
    }
}
