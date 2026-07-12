<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Cms\BlockRegistry;
use App\Enums\UserRole;
use App\Models\Page;
use App\Models\User;
use App\Services\PagePublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class CmsFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cms_flag_defaults_off_and_editor_role_exists(): void
    {
        // Default env has no UKV_CMS_ENABLED, so the flag resolves to false.
        $this->assertFalse((bool) env('UKV_CMS_ENABLED', false));
        $this->assertSame('editor', UserRole::Editor->value);
    }

    public function test_is_published_cms_requires_mode_status_and_blocks(): void
    {
        $block = [['type' => 'rich-text', 'data' => ['body' => 'x']]];
        $this->assertFalse(Page::create(['slug' => 'a', 'title' => 'A', 'mode' => 'cms', 'status' => 'draft', 'blocks' => $block])->isPublishedCms());
        $this->assertFalse(Page::create(['slug' => 'b', 'title' => 'B', 'mode' => 'coded', 'status' => 'published', 'blocks' => $block])->isPublishedCms());
        $this->assertFalse(Page::create(['slug' => 'c', 'title' => 'C', 'mode' => 'cms', 'status' => 'published', 'blocks' => []])->isPublishedCms());
        $this->assertTrue(Page::create(['slug' => 'd', 'title' => 'D', 'mode' => 'cms', 'status' => 'published', 'blocks' => $block])->isPublishedCms());
    }

    public function test_block_registry_has_rich_text(): void
    {
        $reg = app(BlockRegistry::class);
        $this->assertArrayHasKey('rich-text', $reg->all());
        $this->assertSame('cms.blocks.rich-text', $reg->view('rich-text'));
        $this->assertNull($reg->view('nope'));
    }

    public function test_flag_off_hides_cms_page(): void
    {
        config(['ukv.cms.enabled' => false]);
        Page::create(['slug' => 'promo', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published', 'blocks' => [['type' => 'rich-text', 'data' => ['body' => '<p>Live promo</p>']]]]);
        $this->get('/promo')->assertNotFound();
    }

    public function test_flag_on_renders_published_cms_page(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create(['slug' => 'promo', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published', 'blocks' => [['type' => 'rich-text', 'data' => ['body' => '<p>Live promo</p>']]]]);
        $this->get('/promo')->assertOk()->assertSee('Live promo', false);
    }

    public function test_flag_on_but_draft_falls_back_to_404(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create(['slug' => 'promo', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'draft', 'blocks' => [['type' => 'rich-text', 'data' => ['body' => '<p>x</p>']]]]);
        $this->get('/promo')->assertNotFound();
    }

    public function test_publish_busts_cache(): void
    {
        config(['ukv.cms.enabled' => true]);
        $p = Page::create(['slug' => 'promo', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published', 'blocks' => [['type' => 'rich-text', 'data' => ['body' => '<p>One</p>']]]]);
        $this->get('/promo')->assertSee('One', false);
        $this->assertTrue(Cache::has('cms:page:promo'));

        $p->update(['blocks' => [['type' => 'rich-text', 'data' => ['body' => '<p>Two</p>']]]]);
        $this->assertFalse(Cache::has('cms:page:promo'));
        $this->get('/promo')->assertSee('Two', false);
    }

    public function test_snapshot_then_revert_restores_blocks(): void
    {
        $page = Page::create(['slug' => 'p', 'title' => 'P', 'mode' => 'cms', 'status' => 'published', 'blocks' => [['type' => 'rich-text', 'data' => ['body' => 'v1']]]]);
        $pub = app(PagePublisher::class);
        $rev = $pub->snapshot($page, null);
        $page->update(['blocks' => [['type' => 'rich-text', 'data' => ['body' => 'v2']]]]);
        $this->assertSame('v2', $page->fresh()->blocks[0]['data']['body']);
        $pub->revertTo($page->fresh(), $rev);
        $this->assertSame('v1', $page->fresh()->blocks[0]['data']['body']);
    }

    public function test_editor_can_list_pages_but_not_orders(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $this->actingAs($editor)->get('/admin/pages')->assertOk();
        $this->actingAs($editor)->get('/admin/orders')->assertForbidden();
    }
}
