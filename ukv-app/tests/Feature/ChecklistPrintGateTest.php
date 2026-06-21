<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChecklistRequest;
use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Gate tests: /print and /calendar.ics must be 403 until paid_at is set.
 */
final class ChecklistPrintGateTest extends TestCase
{
    use RefreshDatabase;

    private function makeRequest(array $attrs = []): ChecklistRequest
    {
        $d = Destination::factory()->create([
            'name'              => 'Turkey',
            'tier_standard_gbp' => 35,
            'tier_express_gbp'  => 55,
            'tier_premium_gbp'  => 85,
        ]);

        $r = ChecklistRequest::create(array_merge([
            'destination_id' => $d->id,
            'inputs'         => ['travel_date' => '2026-09-01'],
            'items'          => [
                ['document_key' => 'passport', 'label' => 'Valid passport', 'note' => '6 months', 'category' => 'Identity', 'mandatory' => true],
                ['document_key' => 'bank',     'label' => 'Bank statements', 'note' => null,        'category' => 'Finance',  'mandatory' => false],
            ],
        ], array_diff_key($attrs, ['paid_at' => null])));

        if (isset($attrs['paid_at'])) {
            $r->forceFill(['paid_at' => $attrs['paid_at']])->save();
        }

        return $r;
    }

    // --- UNPAID ---

    public function test_unpaid_print_returns_403(): void
    {
        $r = $this->makeRequest();
        $this->get("/checklist/{$r->token}/print")
            ->assertStatus(403)
            ->assertDontSee('Bank statements');
    }

    public function test_unpaid_calendar_returns_403(): void
    {
        $r = $this->makeRequest();
        $this->get("/checklist/{$r->token}/calendar.ics")
            ->assertStatus(403);
    }

    // --- PAID ---

    public function test_paid_print_returns_200_and_shows_full_list(): void
    {
        $r = $this->makeRequest(['paid_at' => now()]);
        $this->get("/checklist/{$r->token}/print")
            ->assertStatus(200)
            ->assertSee('Bank statements');
    }

    public function test_paid_calendar_returns_200(): void
    {
        $r = $this->makeRequest(['paid_at' => now()]);
        $this->get("/checklist/{$r->token}/calendar.ics")
            ->assertStatus(200);
    }
}
