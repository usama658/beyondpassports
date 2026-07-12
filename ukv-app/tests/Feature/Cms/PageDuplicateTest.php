<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PageDuplicateTest extends TestCase
{
    use RefreshDatabase;

    private function source(): Page
    {
        return Page::create([
            'slug' => 'services',
            'title' => 'Services',
            'mode' => 'cms',
            'status' => 'published',
            'in_sitemap' => true,
            'published_at' => now(),
            'blocks' => [[
                'type' => 'rich-text',
                'data' => ['body' => '<p>Original body</p>'],
            ]],
        ]);
    }

    public function test_duplicate_is_a_draft_copy_with_blocks(): void
    {
        $copy = $this->source()->duplicateAsDraft();

        $this->assertSame('Services (copy)', $copy->title);
        $this->assertSame('services-copy', $copy->slug);
        $this->assertSame('cms', $copy->mode);
        $this->assertSame('draft', $copy->status);
        $this->assertFalse($copy->in_sitemap);
        $this->assertNull($copy->published_at);
        $this->assertSame('<p>Original body</p>', $copy->blocks[0]['data']['body']);
    }

    public function test_duplicate_never_collides_on_slug(): void
    {
        $source = $this->source();
        $first = $source->duplicateAsDraft();
        $second = $source->duplicateAsDraft();

        $this->assertSame('services-copy', $first->slug);
        $this->assertSame('services-copy-2', $second->slug);
    }

    public function test_duplicate_does_not_touch_the_original(): void
    {
        $source = $this->source();
        $source->duplicateAsDraft();
        $source->refresh();

        $this->assertSame('published', $source->status);
        $this->assertSame('services', $source->slug);
        $this->assertTrue($source->in_sitemap);
    }
}
