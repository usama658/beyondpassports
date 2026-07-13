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

    public function test_trustpilot_widget_block_renders(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-tp', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'trustpilot', 'data' => ['theme' => 'dark', 'align' => 'center']]],
        ]);

        // Renders the coded trustpilot-cta partial (no 500); internals are config-driven.
        $this->get('/promo-tp')->assertOk();
    }

    public function test_pricing_widget_block_renders(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-price', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'pricing', 'data' => []]],
        ]);

        // Renders the coded pricing partial driven by config('ukv.pricing').
        $this->get('/promo-price')->assertOk();
    }

    public function test_accordion_renders_details_rows(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-acc', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'accordion', 'data' => [
                'heading' => 'More detail',
                'items' => [['title' => 'How long does it take?', 'body' => 'Usually two weeks.']],
            ]]],
        ]);

        $this->get('/promo-acc')->assertOk()
            ->assertSee('class="cms-accordion"', false)
            ->assertSee('<summary>How long does it take?</summary>', false)
            ->assertSee('Usually two weeks.', false);
    }

    public function test_callout_renders_with_tone(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-call', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'callout', 'data' => [
                'tone' => 'warning', 'title' => 'Check your passport', 'body' => 'It must be valid for six months.',
            ]]],
        ]);

        $this->get('/promo-call')->assertOk()
            ->assertSee('class="cms-callout"', false)
            ->assertSee('Check your passport', false)
            ->assertSee('It must be valid for six months.', false);
    }

    public function test_testimonials_render_grid(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-tm', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'testimonials', 'data' => [
                'heading' => 'What travellers say',
                'items' => [['quote' => 'Smooth and simple.', 'name' => 'Priya', 'detail' => 'France']],
            ]]],
        ]);

        $this->get('/promo-tm')->assertOk()
            ->assertSee('class="cms-testimonials"', false)
            ->assertSee('Smooth and simple.', false)
            ->assertSee('Priya', false);
    }

    public function test_timeline_renders_milestones(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-tl', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'timeline', 'data' => [
                'heading' => 'Your journey',
                'items' => [['label' => 'Day 1', 'title' => 'You apply', 'text' => 'We open your case.']],
            ]]],
        ]);

        $this->get('/promo-tl')->assertOk()
            ->assertSee('class="cms-timeline"', false)
            ->assertSee('You apply', false)
            ->assertSee('Day 1', false);
    }

    public function test_video_parses_youtube_into_safe_embed(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-vid', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'video', 'data' => [
                'heading' => 'How it works', 'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'caption' => 'A short intro.',
            ]]],
        ]);

        $this->get('/promo-vid')->assertOk()
            ->assertSee('class="cms-video"', false)
            ->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
            ->assertSee('A short intro.', false);
    }

    public function test_video_ignores_non_whitelisted_host(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-vid2', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'video', 'data' => ['url' => 'https://evil.example.com/clip.mp4']]],
        ]);

        // Non-YouTube/Vimeo URL resolves to no embed, so the block renders nothing (no iframe injected).
        $this->get('/promo-vid2')->assertOk()
            ->assertDontSee('class="cms-video"', false)
            ->assertDontSee('<iframe', false);
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
