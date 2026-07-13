<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Cms\BlockRegistry;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ContentBlocksTest extends TestCase
{
    use RefreshDatabase;

    public function test_cta_and_faq_are_registered(): void
    {
        $reg = app(BlockRegistry::class);
        $this->assertArrayHasKey('cta-band', $reg->all());
        $this->assertArrayHasKey('faq', $reg->all());
    }

    public function test_cta_band_renders_themed_section(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-cta', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'cta-band', 'data' => [
                'heading' => 'Ready to apply?', 'subtext' => 'We handle the rest.',
                'button_label' => 'Start now', 'button_url' => '/apply',
            ]]],
        ]);

        $this->get('/promo-cta')->assertOk()
            ->assertSee('class="cta-band"', false)
            ->assertSee('Ready to apply?', false)
            ->assertSee('href="/apply"', false)
            ->assertSee('Start now', false);
    }

    public function test_faq_renders_details_accordion(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-faq', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'faq', 'data' => [
                'heading' => 'Common questions',
                'items' => [
                    ['q' => 'Is it free?', 'a' => 'The checker is free.'],
                    ['q' => 'Do you guarantee?', 'a' => 'No, the embassy decides.'],
                ],
            ]]],
        ]);

        $this->get('/promo-faq')->assertOk()
            ->assertSee('class="faq-e"', false)
            ->assertSee('<summary>Is it free?</summary>', false)
            ->assertSee('No, the embassy decides.', false);
    }

    public function test_steps_render_auto_numbered(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-steps', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'steps', 'data' => [
                'heading' => 'How it works',
                'items' => [['title' => 'Send documents', 'text' => 'Upload them.'], ['title' => 'We check', 'text' => 'We review.']],
            ]]],
        ]);

        $this->get('/promo-steps')->assertOk()
            ->assertSee('class="cms-steps"', false)
            ->assertSee('How it works', false)
            ->assertSee('Send documents', false);
    }

    public function test_feature_grid_renders_cards(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-feat', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'feature-grid', 'data' => [
                'heading' => 'Why us',
                'items' => [['title' => 'Human-checked', 'text' => 'A UK lead reviews.']],
            ]]],
        ]);

        $this->get('/promo-feat')->assertOk()
            ->assertSee('class="cms-features"', false)
            ->assertSee('Human-checked', false);
    }

    public function test_stats_render(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-stats', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'stats', 'data' => ['items' => [['number' => '98%', 'label' => 'Approval']]]]],
        ]);

        $this->get('/promo-stats')->assertOk()
            ->assertSee('class="cms-stats"', false)->assertSee('98%', false)->assertSee('Approval', false);
    }

    public function test_quote_renders_with_stars(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-quote', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'quote', 'data' => ['quote' => 'They sorted everything.', 'name' => 'Aisha', 'stars' => 5]]],
        ]);

        $this->get('/promo-quote')->assertOk()
            ->assertSee('class="cms-quote"', false)->assertSee('They sorted everything.', false)->assertSee('Aisha', false);
    }

    public function test_split_renders(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-split', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'split', 'data' => ['heading' => 'Why us', 'body' => 'We check everything.']]],
        ]);

        $this->get('/promo-split')->assertOk()
            ->assertSee('class="cms-split"', false)->assertSee('Why us', false);
    }

    public function test_empty_blocks_render_nothing(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-empty2', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [
                ['type' => 'cta-band', 'data' => ['heading' => '']],
                ['type' => 'faq', 'data' => ['heading' => 'x', 'items' => []]],
            ],
        ]);

        $this->get('/promo-empty2')->assertOk()
            ->assertDontSee('class="cta-band"', false)
            ->assertDontSee('class="faq-e"', false);
    }
}
