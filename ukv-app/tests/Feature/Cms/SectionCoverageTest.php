<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Cms\SectionManifest;
use Tests\TestCase;

/**
 * Flags CMS coverage gaps automatically. If any public <section> class isn't classified in
 * SectionManifest::KNOWN, this fails — forcing whoever added it to decide: give it an editable CMS
 * block, or mark it locked/functional/layout. This is the guard that catches a "section with no
 * block" the moment it ships, now and in future.
 */
final class SectionCoverageTest extends TestCase
{
    public function test_every_public_section_is_classified(): void
    {
        $live = SectionManifest::liveSectionClasses();
        $known = SectionManifest::KNOWN;

        $unmapped = array_values(array_filter($live, fn (string $c) => ! array_key_exists($c, $known)));

        $this->assertSame([], $unmapped,
            'Unmapped public <section> class(es): '.implode(', ', $unmapped).
            '. Add a CMS block for it, or classify it (block|locked|functional|layout) in App\Cms\SectionManifest::KNOWN.');
    }

    public function test_manifest_has_no_stale_entries(): void
    {
        // Keeps the manifest honest — an entry for a section that no longer exists should be removed.
        $live = SectionManifest::liveSectionClasses();
        $stale = array_values(array_filter(array_keys(SectionManifest::KNOWN), fn (string $c) => ! in_array($c, $live, true)));

        $this->assertSame([], $stale, 'Stale SectionManifest entries (no longer in any view): '.implode(', ', $stale));
    }

    public function test_block_disposition_classes_map_to_real_blocks(): void
    {
        // Any class tagged "block" should correspond to a themed section a CMS block renders.
        $blockClasses = array_keys(array_filter(SectionManifest::KNOWN, fn ($d) => $d === 'block'));
        $this->assertContains('cta-band', $blockClasses);
        $this->assertContains('faq-e', $blockClasses);
        $this->assertContains('tbar-f', $blockClasses);
    }
}
