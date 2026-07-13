<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Models\GlobalBlock;
use App\Models\Page;
use Database\Seeders\CmsPageTemplatesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DraftAndTemplatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_reusable_block_renders_nothing(): void
    {
        config(['ukv.cms.enabled' => true]);
        $gb = GlobalBlock::create([
            'name' => 'Parked CTA', 'type' => 'cta-band', 'status' => 'draft',
            'data' => ['heading' => 'Parked heading'],
        ]);
        Page::create([
            'slug' => 'promo-draftgb', 'title' => 'P', 'mode' => 'cms', 'status' => 'published',
            'blocks' => [['type' => 'global', 'data' => ['global_id' => $gb->id]]],
        ]);

        $this->get('/promo-draftgb')->assertOk()->assertDontSee('Parked heading', false);

        // Publish it -> now it shows everywhere it's referenced.
        $gb->update(['status' => 'published']);
        $this->get('/promo-draftgb')->assertOk()->assertSee('Parked heading', false);
    }

    public function test_template_seeder_creates_draft_starter_pages(): void
    {
        (new CmsPageTemplatesSeeder())->run();

        $landing = Page::where('slug', 'template-landing-page')->first();
        $this->assertNotNull($landing);
        $this->assertSame('draft', $landing->status);
        $this->assertSame('cms', $landing->mode);
        $this->assertTrue((bool) $landing->noindex);
        $this->assertFalse((bool) $landing->in_sitemap);
        $this->assertNotEmpty($landing->blocks);

        // Idempotent.
        (new CmsPageTemplatesSeeder())->run();
        $this->assertSame(1, Page::where('slug', 'template-landing-page')->count());
    }

    public function test_premium_template_showcases_the_expanded_kit(): void
    {
        (new CmsPageTemplatesSeeder())->run();

        $premium = Page::where('slug', 'template-premium-landing')->first();
        $this->assertNotNull($premium);
        $this->assertSame('draft', $premium->status);
        $this->assertTrue((bool) $premium->noindex);

        // It exercises a broad slice of the newer block library.
        $types = collect($premium->blocks)->pluck('type')->all();
        foreach (['notice-bar', 'logo-strip', 'checklist', 'compare-table', 'tabs', 'testimonials', 'contact-cards', 'fine-print'] as $expected) {
            $this->assertContains($expected, $types, "premium template should include a {$expected} block");
        }
    }

    public function test_a_template_is_not_public_but_can_be_duplicated(): void
    {
        config(['ukv.cms.enabled' => true]);
        (new CmsPageTemplatesSeeder())->run();

        // Draft template never renders publicly (catch-all 404s a non-published cms page).
        $this->get('/template-landing-page')->assertNotFound();

        // But it can be cloned into a new editable draft to build from.
        $copy = Page::where('slug', 'template-landing-page')->first()->duplicateAsDraft();
        $this->assertSame('draft', $copy->status);
        $this->assertNotEmpty($copy->blocks);
        $this->assertStringStartsWith('template-landing-page-copy', $copy->slug);
    }
}
