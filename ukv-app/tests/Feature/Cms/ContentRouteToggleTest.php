<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The content pages wired to CmsController::pageOrCoded must keep serving their coded Blade until a
 * published cms page exists for that slug, then switch to the CMS render. This proves the site-wide
 * roll-out is safe: no URL changes behaviour until someone deliberately publishes it.
 */
final class ContentRouteToggleTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{0: string, 1: string}> url => [slug, a marker only the coded view has] */
    public static function wiredRoutes(): array
    {
        return [
            'home' => ['/', 'home'],
            'legal' => ['/legal', 'legal'],
            'compare' => ['/compare', 'compare'],
            'tour-packages' => ['/tour-packages', 'tour-packages'],
            'lp-speed' => ['/schengen-visa-agent', 'schengen-visa-agent'],
            'lp-refused' => ['/schengen-visa-refused', 'schengen-visa-refused'],
        ];
    }

    /**
     * @dataProvider wiredRoutes
     */
    public function test_wired_route_serves_coded_view_when_no_cms_page(string $url, string $slug): void
    {
        config(['ukv.cms.enabled' => true]);
        // No Page row for this slug -> coded Blade must still render (no 404/500).
        $this->assertNull(Page::where('slug', $slug)->first());
        $this->get($url)->assertOk();
    }

    /**
     * @dataProvider wiredRoutes
     */
    public function test_wired_route_switches_to_cms_when_published(string $url, string $slug): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => $slug, 'title' => 'CMS '.$slug, 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'hero', 'data' => ['title' => 'CMS override '.$slug]]],
        ]);

        $this->get($url)->assertOk()->assertSee('CMS override '.$slug, false);
    }

    public function test_flag_off_always_serves_coded_even_with_a_published_page(): void
    {
        config(['ukv.cms.enabled' => false]);
        Page::create([
            'slug' => 'legal', 'title' => 'CMS legal', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'hero', 'data' => ['title' => 'Should not appear']]],
        ]);

        $this->get('/legal')->assertOk()->assertDontSee('Should not appear', false);
    }
}
