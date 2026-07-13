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

    public function test_gallery_renders_from_one_off_src(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-gal', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'gallery', 'data' => [
                'heading' => 'Our work',
                'items' => [['src' => 'https://example.com/a.jpg', 'alt' => 'Sample', 'caption' => 'Nice']],
            ]]],
        ]);

        $this->get('/promo-gal')->assertOk()
            ->assertSee('class="cms-gallery"', false)
            ->assertSee('https://example.com/a.jpg', false)
            ->assertSee('alt="Sample"', false);
    }

    public function test_logo_strip_renders_linked_and_plain(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-logo', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'logo-strip', 'data' => [
                'heading' => 'As featured in',
                'items' => [
                    ['src' => 'https://example.com/l1.png', 'name' => 'Linked', 'url' => 'https://partner.example'],
                    ['src' => 'https://example.com/l2.png', 'name' => 'Plain'],
                ],
            ]]],
        ]);

        $this->get('/promo-logo')->assertOk()
            ->assertSee('class="cms-logos"', false)
            ->assertSee('alt="Linked"', false)
            ->assertSee('href="https://partner.example"', false)
            ->assertSee('alt="Plain"', false);
    }

    public function test_compare_table_renders_ticks(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-cmp', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'compare-table', 'data' => [
                'heading' => 'Why us', 'col_a' => 'Beyond Passports', 'col_b' => 'DIY',
                'items' => [['label' => 'Human review', 'has_a' => true, 'has_b' => false]],
            ]]],
        ]);

        $this->get('/promo-cmp')->assertOk()
            ->assertSee('class="cms-compare"', false)
            ->assertSee('Beyond Passports', false)
            ->assertSee('Human review', false);
    }

    public function test_contact_cards_render(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-cc', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'contact-cards', 'data' => [
                'heading' => 'Talk to us',
                'items' => [['title' => 'WhatsApp', 'text' => 'Fastest reply.', 'button_label' => 'Message', 'button_url' => '/contact']],
            ]]],
        ]);

        $this->get('/promo-cc')->assertOk()
            ->assertSee('class="cms-contact"', false)
            ->assertSee('WhatsApp', false)
            ->assertSee('href="/contact"', false);
    }

    public function test_buttons_render_primary_and_secondary(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-btn', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'buttons', 'data' => [
                'heading' => 'Choose a path',
                'items' => [
                    ['label' => 'Apply now', 'url' => '/apply', 'style' => 'primary'],
                    ['label' => 'Learn more', 'url' => '/services', 'style' => 'secondary'],
                ],
            ]]],
        ]);

        $this->get('/promo-btn')->assertOk()
            ->assertSee('class="cms-buttons"', false)
            ->assertSee('cb-primary', false)
            ->assertSee('cb-secondary', false)
            ->assertSee('href="/apply"', false);
    }

    public function test_notice_bar_renders(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-nb', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'notice-bar', 'data' => [
                'tone' => 'warning', 'text' => 'Summer slots fill fast.', 'link_label' => 'Book', 'link_url' => '/apply',
            ]]],
        ]);

        $this->get('/promo-nb')->assertOk()
            ->assertSee('class="cms-notice"', false)
            ->assertSee('Summer slots fill fast.', false)
            ->assertSee('href="/apply"', false);
    }

    public function test_tabs_render_no_js_panels(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-tabs', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'tabs', 'data' => [
                'heading' => 'How we help',
                'items' => [
                    ['label' => 'Prepare', 'body' => 'We prepare everything.'],
                    ['label' => 'Check', 'body' => 'We check it twice.'],
                ],
            ]]],
        ]);

        $this->get('/promo-tabs')->assertOk()
            ->assertSee('class="cms-tabs"', false)
            ->assertSee('Prepare', false)
            ->assertSee('We check it twice.', false)
            ->assertSee('type="radio"', false);
    }

    public function test_tabs_need_two_items(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-tabs1', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'tabs', 'data' => ['items' => [['label' => 'Only', 'body' => 'One panel']]]]],
        ]);

        // A single tab is not a tab set, so it renders nothing.
        $this->get('/promo-tabs1')->assertOk()->assertDontSee('class="cms-tabs"', false);
    }

    public function test_checklist_renders_ticked_points(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-ck', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'checklist', 'data' => [
                'heading' => "What's included",
                'items' => [['text' => 'A UK specialist reviews your application'], ['text' => 'Document checklist tailored to your trip']],
            ]]],
        ]);

        $this->get('/promo-ck')->assertOk()
            ->assertSee('class="cms-checklist"', false)
            ->assertSee('A UK specialist reviews your application', false);
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
