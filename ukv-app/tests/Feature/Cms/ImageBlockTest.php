<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Cms\BlockRegistry;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ImageBlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_block_registered(): void
    {
        $reg = app(BlockRegistry::class);
        $this->assertArrayHasKey('image', $reg->all());
        $this->assertSame('cms.blocks.image', $reg->view('image'));
    }

    public function test_image_renders_lazy_with_alt_and_dimensions(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo',
            'title' => 'Promo',
            'mode' => 'cms',
            'status' => 'published',
            'blocks' => [[
                'type' => 'image',
                'data' => ['src' => 'cms/hero.jpg', 'alt' => 'Team at work', 'width' => 1200, 'height' => 600],
            ]],
        ]);

        $this->get('/promo')->assertOk()
            ->assertSee('storage/cms/hero.jpg')
            ->assertSee('alt="Team at work"', false)
            ->assertSee('loading="lazy"', false)
            ->assertSee('width="1200"', false)
            ->assertSee('height="600"', false);
    }

    public function test_absolute_url_used_as_is(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo2',
            'title' => 'Promo2',
            'mode' => 'cms',
            'status' => 'published',
            'blocks' => [[
                'type' => 'image',
                'data' => ['src' => 'https://cdn.example.com/x.jpg', 'alt' => 'X'],
            ]],
        ]);

        $this->get('/promo2')->assertOk()
            ->assertSee('src="https://cdn.example.com/x.jpg"', false)
            ->assertDontSee('storage/https');
    }
}
