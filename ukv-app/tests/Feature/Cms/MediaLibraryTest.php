<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Models\Media;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_url_and_label(): void
    {
        $m = Media::create(['disk' => 'public', 'path' => 'cms/hero.jpg', 'name' => 'Hero', 'alt' => 'A hero']);

        $this->assertStringContainsString('storage/cms/hero.jpg', $m->url());
        $this->assertSame('Hero', $m->label());

        $abs = Media::create(['path' => 'https://cdn.example.com/x.jpg']);
        $this->assertSame('https://cdn.example.com/x.jpg', $abs->url());
        $this->assertSame('x.jpg', $abs->label()); // falls back to filename
    }

    public function test_image_block_renders_from_the_library_with_its_alt(): void
    {
        config(['ukv.cms.enabled' => true]);
        $m = Media::create(['path' => 'cms/team.jpg', 'alt' => 'Our team']);

        Page::create([
            'slug' => 'promo', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'image', 'data' => ['media_id' => $m->id]]],
        ]);

        $this->get('/promo')->assertOk()
            ->assertSee('storage/cms/team.jpg')
            ->assertSee('alt="Our team"', false);
    }

    public function test_block_alt_overrides_library_alt(): void
    {
        config(['ukv.cms.enabled' => true]);
        $m = Media::create(['path' => 'cms/team.jpg', 'alt' => 'Our team']);

        Page::create([
            'slug' => 'promo2', 'title' => 'Promo2', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'image', 'data' => ['media_id' => $m->id, 'alt' => 'Specific caption']]],
        ]);

        $this->get('/promo2')->assertOk()
            ->assertSee('alt="Specific caption"', false)
            ->assertDontSee('alt="Our team"', false);
    }

    public function test_one_off_upload_still_works_without_a_library_ref(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo3', 'title' => 'Promo3', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'image', 'data' => ['src' => 'cms/oneoff.jpg', 'alt' => 'One off']]],
        ]);

        $this->get('/promo3')->assertOk()->assertSee('storage/cms/oneoff.jpg')->assertSee('alt="One off"', false);
    }
}
