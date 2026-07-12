<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AnnouncementBarTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_bar_by_default(): void
    {
        $this->get('/services')->assertOk()->assertDontSee('ann-bar');
    }

    public function test_bar_shows_when_enabled_with_text(): void
    {
        Setting::put('announcement_enabled', '1');
        Setting::put('announcement_text', 'Holiday hours: replies within 24h');

        $this->get('/services')->assertOk()
            ->assertSee('ann-bar')
            ->assertSee('Holiday hours: replies within 24h');
    }

    public function test_bar_hidden_when_disabled_even_with_text(): void
    {
        Setting::put('announcement_enabled', '0');
        Setting::put('announcement_text', 'Some text');

        $this->get('/services')->assertOk()->assertDontSee('ann-bar');
    }

    public function test_editor_can_reach_site_settings_but_agent_cannot(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $this->actingAs($editor)->get('/admin/site-settings')->assertOk();

        $agent = User::factory()->create(['role' => UserRole::Agent]);
        $this->actingAs($agent)->get('/admin/site-settings')->assertForbidden();
    }
}
