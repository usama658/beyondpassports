<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Cms\BlockRegistry;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * Completeness guard for the block library. Guarantees every registered block is fully wired — a
 * stable key, a label, a Blade partial that actually exists, and a schema — so no block can be
 * half-built and 500 when placed on a page. This is the automated backstop that makes "nothing is
 * missed" verifiable rather than eyeballed.
 */
final class BlockRegistryGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_registered_block_is_fully_wired(): void
    {
        $reg = app(BlockRegistry::class);

        foreach ($reg->all() as $key => $class) {
            $this->assertIsString($key, "block key must be a string for {$class}");
            $this->assertNotSame('', $class::label(), "block {$key} needs a label");

            $view = $reg->view($key);
            $this->assertNotNull($view, "block {$key} has no view");
            $this->assertTrue(View::exists($view), "block {$key} view [{$view}] does not exist");

            $this->assertIsArray($class::schema(), "block {$key} schema must be an array");
        }
    }

    public function test_global_allowed_keys_all_exist_in_the_registry(): void
    {
        $all = app(BlockRegistry::class)->all();
        foreach (BlockRegistry::GLOBAL_ALLOWED as $key) {
            $this->assertArrayHasKey($key, $all, "GLOBAL_ALLOWED lists [{$key}] but it isn't registered");
        }
    }

    public function test_a_page_using_every_content_block_renders(): void
    {
        // Place one of every content block on a single page and confirm it renders (no 500), so the
        // whole library is exercised end-to-end, not just unit-checked.
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'kitchen-sink', 'title' => 'All blocks', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [
                ['type' => 'hero', 'data' => ['title' => 'Hero title']],
                ['type' => 'rich-text', 'data' => ['body' => '<p>Body</p>']],
                ['type' => 'cta-band', 'data' => ['heading' => 'CTA', 'button_label' => 'Go', 'button_url' => '/apply']],
                ['type' => 'faq', 'data' => ['heading' => 'Q', 'items' => [['q' => 'A?', 'a' => 'B.']]]],
                ['type' => 'trust-bar', 'data' => ['items' => [['bold' => 'No hidden', 'rest' => 'fees']]]],
                ['type' => 'steps', 'data' => ['heading' => 'How', 'items' => [['title' => 'Step one']]]],
                ['type' => 'feature-grid', 'data' => ['heading' => 'Why', 'items' => [['title' => 'Feature one']]]],
            ],
        ]);

        $this->get('/kitchen-sink')->assertOk()
            ->assertSee('Hero title', false)
            ->assertSee('class="cta-band"', false)
            ->assertSee('class="faq-e"', false)
            ->assertSee('class="tbar-f"', false)
            ->assertSee('class="cms-steps"', false)
            ->assertSee('class="cms-features"', false);
    }
}
