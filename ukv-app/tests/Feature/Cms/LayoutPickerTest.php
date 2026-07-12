<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LayoutPickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_and_known_layout_resolve(): void
    {
        $p = new Page(['layout' => 'public']);
        $this->assertSame('layouts.public', $p->layoutView());

        $blank = new Page();
        $this->assertSame('layouts.public', $blank->layoutView());
    }

    public function test_unknown_layout_falls_back_safely(): void
    {
        $p = new Page(['layout' => 'does-not-exist']);
        $this->assertSame('layouts.public', $p->layoutView());
    }

    public function test_cms_page_renders_inside_its_layout(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-layout', 'title' => 'Promo', 'mode' => 'cms', 'layout' => 'public',
            'status' => 'published',
            'blocks' => [['type' => 'rich-text', 'data' => ['body' => '<p>Body</p>']]],
        ]);

        // Renders (200) inside the public layout — proves the dynamic @extends resolves.
        $this->get('/promo-layout')->assertOk()->assertSee('Body', false);
    }
}
