<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CmsSeoTest extends TestCase
{
    use RefreshDatabase;

    private function cmsPage(array $overrides = []): Page
    {
        return Page::create(array_merge([
            'slug' => 'promo',
            'title' => 'Promo',
            'mode' => 'cms',
            'status' => 'published',
            'blocks' => [['type' => 'rich-text', 'data' => ['body' => '<p>x</p>']]],
        ], $overrides));
    }

    public function test_noindex_page_emits_robots_meta(): void
    {
        config(['ukv.cms.enabled' => true]);
        $this->cmsPage(['noindex' => true]);

        $this->get('/promo')->assertOk()->assertSee('name="robots" content="noindex,nofollow"', false);
    }

    public function test_indexable_page_has_no_robots_noindex(): void
    {
        config(['ukv.cms.enabled' => true]);
        $this->cmsPage(['noindex' => false]);

        $this->get('/promo')->assertOk()->assertDontSee('noindex');
    }

    public function test_og_image_emitted_when_set(): void
    {
        config(['ukv.cms.enabled' => true]);
        $this->cmsPage(['og_image' => 'https://cdn.example.com/promo.png']);

        $this->get('/promo')->assertOk()->assertSee('property="og:image" content="https://cdn.example.com/promo.png"', false);
    }

    public function test_sitemap_includes_published_cms_page_not_noindex(): void
    {
        config(['ukv.cms.enabled' => true]);
        $this->cmsPage(['slug' => 'campaign-a', 'in_sitemap' => true, 'noindex' => false]);
        $this->cmsPage(['slug' => 'hidden-b', 'in_sitemap' => true, 'noindex' => true]);
        $this->cmsPage(['slug' => 'draft-c', 'status' => 'draft', 'in_sitemap' => true]);

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringContainsString('/campaign-a', $xml);
        $this->assertStringNotContainsString('/hidden-b', $xml);   // noindex excluded
        $this->assertStringNotContainsString('/draft-c', $xml);    // draft excluded
    }

    public function test_sitemap_excludes_cms_pages_when_flag_off(): void
    {
        config(['ukv.cms.enabled' => false]);
        $this->cmsPage(['slug' => 'campaign-a', 'in_sitemap' => true]);

        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/campaign-a');
    }
}
