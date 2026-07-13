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

    public function test_every_block_has_a_valid_picker_category(): void
    {
        // A new block must be categorised, or it silently lands in the picker with no group. This
        // fails the build until CATEGORY has an entry whose category is a known one.
        $reg = app(BlockRegistry::class);
        foreach (array_keys($reg->all()) as $key) {
            $this->assertArrayHasKey($key, BlockRegistry::CATEGORY, "block [{$key}] is missing a picker category");
            $cat = BlockRegistry::CATEGORY[$key]['cat'];
            $this->assertContains($cat, BlockRegistry::CATEGORY_ORDER, "block [{$key}] has unknown category [{$cat}]");
            $this->assertNotSame('', BlockRegistry::CATEGORY[$key]['icon'], "block [{$key}] needs an icon");
        }
    }

    public function test_builder_blocks_are_category_ordered_and_complete(): void
    {
        $reg = app(BlockRegistry::class);
        $blocks = $reg->builderBlocks();
        $this->assertCount(count($reg->all()), $blocks, 'every registered block must appear in the picker');

        // Labels are category-prefixed and the categories appear in CATEGORY_ORDER sequence.
        $seenOrder = [];
        foreach ($blocks as $block) {
            $label = $block->getLabel();
            $this->assertStringContainsString(' · ', $label, "builder label [{$label}] should be category-prefixed");
            $cat = trim(explode(' · ', $label)[0]);
            if (empty($seenOrder) || end($seenOrder) !== $cat) {
                $seenOrder[] = $cat;
            }
        }
        // Each category block appears contiguously (no category repeats after another begins).
        $this->assertSame(array_values(array_unique($seenOrder)), $seenOrder, 'categories must be contiguous in the picker');
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
                ['type' => 'stats', 'data' => ['items' => [['number' => '98%', 'label' => 'Approval']]]],
                ['type' => 'quote', 'data' => ['quote' => 'Great', 'name' => 'A', 'stars' => 5]],
                ['type' => 'split', 'data' => ['heading' => 'Split heading', 'body' => 'text']],
                ['type' => 'accordion', 'data' => ['heading' => 'More', 'items' => [['title' => 'Row one', 'body' => 'Detail.']]]],
                ['type' => 'callout', 'data' => ['tone' => 'info', 'title' => 'Note', 'body' => 'Callout body.']],
                ['type' => 'testimonials', 'data' => ['heading' => 'Voices', 'items' => [['quote' => 'Loved it', 'name' => 'B']]]],
                ['type' => 'timeline', 'data' => ['heading' => 'Journey', 'items' => [['label' => 'Day 1', 'title' => 'Kick off', 'text' => 'x']]]],
                ['type' => 'video', 'data' => ['heading' => 'Watch', 'url' => 'https://youtu.be/dQw4w9WgXcQ']],
                ['type' => 'gallery', 'data' => ['heading' => 'Gallery', 'items' => [['src' => 'https://example.com/a.jpg', 'alt' => 'A']]]],
                ['type' => 'logo-strip', 'data' => ['heading' => 'Featured', 'items' => [['src' => 'https://example.com/logo.png', 'name' => 'Acme']]]],
                ['type' => 'compare-table', 'data' => ['heading' => 'Us vs them', 'col_a' => 'Us', 'col_b' => 'Them', 'items' => [['label' => 'Human check', 'has_a' => true, 'has_b' => false]]]],
                ['type' => 'contact-cards', 'data' => ['heading' => 'Reach us', 'items' => [['title' => 'WhatsApp', 'text' => 'Chat now', 'button_label' => 'Open', 'button_url' => '/contact']]]],
                ['type' => 'buttons', 'data' => ['heading' => 'Pick one', 'items' => [['label' => 'Apply', 'url' => '/apply', 'style' => 'primary']]]],
                ['type' => 'notice-bar', 'data' => ['tone' => 'brand', 'text' => 'Seasonal note']],
                ['type' => 'tabs', 'data' => ['heading' => 'Details', 'items' => [['label' => 'One', 'body' => 'First'], ['label' => 'Two', 'body' => 'Second']]]],
                ['type' => 'checklist', 'data' => ['heading' => 'Included', 'items' => [['text' => 'Human check']]]],
                ['type' => 'map-embed', 'data' => ['heading' => 'Find us', 'query' => 'London']],
                ['type' => 'fine-print', 'data' => ['text' => 'We are not a government body.']],
                ['type' => 'divider', 'data' => ['size' => 'm', 'style' => 'line']],
            ],
        ]);

        $this->get('/kitchen-sink')->assertOk()
            ->assertSee('Hero title', false)
            ->assertSee('class="cta-band"', false)
            ->assertSee('class="faq-e"', false)
            ->assertSee('class="tbar-f"', false)
            ->assertSee('class="cms-steps"', false)
            ->assertSee('class="cms-features"', false)
            ->assertSee('class="cms-stats"', false)
            ->assertSee('class="cms-quote"', false)
            ->assertSee('class="cms-split"', false)
            ->assertSee('class="cms-accordion"', false)
            ->assertSee('class="cms-callout"', false)
            ->assertSee('class="cms-testimonials"', false)
            ->assertSee('class="cms-timeline"', false)
            ->assertSee('class="cms-video"', false)
            ->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
            ->assertSee('class="cms-gallery"', false)
            ->assertSee('class="cms-logos"', false)
            ->assertSee('class="cms-compare"', false)
            ->assertSee('class="cms-contact"', false)
            ->assertSee('class="cms-buttons"', false)
            ->assertSee('class="cms-notice"', false)
            ->assertSee('class="cms-tabs"', false)
            ->assertSee('class="cms-checklist"', false)
            ->assertSee('class="cms-map"', false)
            ->assertSee('class="cms-fineprint"', false)
            ->assertSee('cms-divider', false);
    }
}
