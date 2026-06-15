<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Boots every Filament resource index + create page (and the dashboard +
 * production board) as an authenticated admin. Catches form/table render
 * errors that route discovery alone misses (e.g. enum option resolution).
 */
class AdminPanelSmokeTest extends TestCase
{
    use RefreshDatabase;

    private array $resources = [
        'orders', 'destinations', 'supply-nodes', 'barriers', 'appointments',
        'client-updates', 'discounts', 'rejections', 'quotes', 'feedback',
    ];

    public function test_admin_panel_pages_render(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->get('/admin')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/production-board')->assertSuccessful();

        foreach ($this->resources as $slug) {
            $this->actingAs($admin)->get("/admin/{$slug}")->assertSuccessful();
            $this->actingAs($admin)->get("/admin/{$slug}/create")->assertSuccessful();
        }
    }
}
