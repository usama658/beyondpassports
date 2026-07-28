<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use Database\Seeders\CmsContentPagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Golden master for the migrated content pages: the CMS-served page (flag on, published locked-include
 * of the body partial) must be HTML-identical to the coded page (flag off) after whitespace
 * normalisation. This is the mechanical proof that "the design and every section look the same" — both
 * render the exact same extracted partial inside the same layout, so there is nothing that can drift.
 */
final class ContentPagesGoldenTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{0: string}> */
    public static function migratedPages(): array
    {
        return ['legal' => ['/legal'], 'compare' => ['/compare'], 'tour-packages' => ['/tour-packages']];
    }

    /**
     * Collapse insignificant differences so only meaningful markup can fail the assert: whitespace,
     * and the per-render random SVG id the EU-flag partial generates (uk-eu-flags.blade.php mints a
     * fresh `euf<hash>` each render in BOTH modes — a volatile DOM id, not a design difference).
     */
    private function normalize(string $html): string
    {
        $html = preg_replace('/euf[0-9a-f]{8}/', 'euf', $html);

        return trim(preg_replace('/\s+/', ' ', preg_replace('/>\s+</', '><', $html)));
    }

    /**
     * @dataProvider migratedPages
     */
    public function test_cms_render_is_identical_to_coded(string $url): void
    {
        // Coded render (flag off): the route falls back to the coded Blade view.
        config(['ukv.cms.enabled' => false]);
        $coded = $this->get($url)->assertOk()->getContent();

        // CMS render (flag on + published locked-include page).
        config(['ukv.cms.enabled' => true]);
        (new CmsContentPagesSeeder())->run();
        $cms = $this->get($url)->assertOk()->getContent();

        $this->assertSame(
            $this->normalize($coded),
            $this->normalize($cms),
            "CMS render of {$url} drifted from the coded page"
        );
    }
}
