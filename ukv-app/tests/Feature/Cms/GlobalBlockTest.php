<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Cms\BlockRegistry;
use App\Models\GlobalBlock;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GlobalBlockTest extends TestCase
{
    use RefreshDatabase;

    private function pageReferencing(GlobalBlock $gb): Page
    {
        return Page::create([
            'slug' => 'promo',
            'title' => 'Promo',
            'mode' => 'cms',
            'status' => 'published',
            'blocks' => [['type' => 'global', 'data' => ['global_id' => $gb->id]]],
        ]);
    }

    public function test_reference_block_is_registered(): void
    {
        $reg = app(BlockRegistry::class);
        $this->assertArrayHasKey('global', $reg->all());
        $this->assertSame('cms.blocks.global', $reg->view('global'));
    }

    public function test_reference_renders_the_global_blocks_content(): void
    {
        config(['ukv.cms.enabled' => true]);
        $gb = GlobalBlock::create([
            'name' => 'Site-wide CTA',
            'type' => 'hero',
            'data' => ['eyebrow' => 'Ready?', 'title' => 'Talk to a Schengen specialist', 'lede' => 'We help.'],
        ]);
        $this->pageReferencing($gb);

        $this->get('/promo')->assertOk()->assertSee('Talk to a Schengen specialist', false);
    }

    public function test_editing_a_global_block_updates_every_referencing_page(): void
    {
        config(['ukv.cms.enabled' => true]);
        $gb = GlobalBlock::create([
            'name' => 'Site-wide CTA',
            'type' => 'hero',
            'data' => ['title' => 'Original headline'],
        ]);
        $this->pageReferencing($gb);

        // Prime the page cache with the original content.
        $this->get('/promo')->assertOk()->assertSee('Original headline', false);

        // Editing the global block must bust the cached page HTML so the change shows everywhere.
        $gb->update(['data' => ['title' => 'Updated headline']]);

        $this->get('/promo')->assertOk()
            ->assertSee('Updated headline', false)
            ->assertDontSee('Original headline', false);
    }

    public function test_missing_reference_renders_nothing_and_still_200s(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create([
            'slug' => 'promo-empty',
            'title' => 'Promo empty',
            'mode' => 'cms',
            'status' => 'published',
            'blocks' => [['type' => 'global', 'data' => ['global_id' => 9999]]],
        ]);

        $this->get('/promo-empty')->assertOk();
    }
}
