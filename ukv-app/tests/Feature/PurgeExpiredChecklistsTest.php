<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChecklistRequest;
use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PurgeExpiredChecklistsTest extends TestCase
{
    use RefreshDatabase;

    public function test_purges_old_requests_keeps_recent(): void
    {
        config(['ukv.doc_retention_days' => 90]);
        $d = Destination::factory()->create();

        $old = ChecklistRequest::create(['destination_id' => $d->id, 'inputs' => [], 'items' => []]);
        $old->forceFill(['created_at' => now()->subDays(120)])->save();

        $recent = ChecklistRequest::create(['destination_id' => $d->id, 'inputs' => [], 'items' => []]);

        $this->artisan('ukv:purge-checklists')->assertExitCode(0);

        $this->assertDatabaseMissing('checklist_requests', ['id' => $old->id]);
        $this->assertDatabaseHas('checklist_requests', ['id' => $recent->id]);
    }
}
