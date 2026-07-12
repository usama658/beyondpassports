<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Enums\UserRole;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CmsPreviewTest extends TestCase
{
    use RefreshDatabase;

    private function draftPage(): Page
    {
        // A DRAFT cms page: mode cms but NOT published. The public catch-all 404s it; only preview shows it.
        return Page::create([
            'slug' => 'draft-promo',
            'title' => 'Draft Promo',
            'mode' => 'cms',
            'status' => 'draft',
            'blocks' => [[
                'type' => 'rich-text',
                'data' => ['body' => '<p>Secret unpublished copy</p>'],
            ]],
        ]);
    }

    public function test_editor_previews_a_draft_page(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $page = $this->draftPage();

        $this->actingAs($editor)->get(route('cms.preview', $page))
            ->assertOk()
            ->assertSee('Secret unpublished copy', false);
    }

    public function test_admin_previews_a_draft_page(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $page = $this->draftPage();

        $this->actingAs($admin)->get(route('cms.preview', $page))
            ->assertOk()
            ->assertSee('Secret unpublished copy', false);
    }

    public function test_preview_works_even_when_global_flag_is_off(): void
    {
        // Preview must not depend on UKV_CMS_ENABLED — the team previews before flipping the switch.
        config(['ukv.cms.enabled' => false]);
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $page = $this->draftPage();

        $this->actingAs($editor)->get(route('cms.preview', $page))
            ->assertOk()
            ->assertSee('Secret unpublished copy', false);
    }

    public function test_agent_cannot_preview(): void
    {
        $agent = User::factory()->create(['role' => UserRole::Agent]);
        $page = $this->draftPage();

        $this->actingAs($agent)->get(route('cms.preview', $page))->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $page = $this->draftPage();

        $this->get(route('cms.preview', $page))->assertRedirect();
    }
}
