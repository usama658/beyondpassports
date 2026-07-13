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
