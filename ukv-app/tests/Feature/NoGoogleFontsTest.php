<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The site self-hosts Outfit (privacy + reliability — renders behind ad/privacy
 * blockers, removes a Google Fonts IP transfer). Guard the *source artifacts*: no
 * public-facing template or the design system may reference the Google Fonts CDN
 * or the retired Plus Jakarta family, and ukv.css must declare the self-hosted
 * Outfit @font-face. Source-level assertions are deterministic (no DB/render/cache
 * flakiness) and catch a regression the instant it lands in a Blade or the CSS.
 */
final class NoGoogleFontsTest extends TestCase
{
    /** @return list<string> Files that control fonts across every public page. */
    private function fontBearingSources(): array
    {
        $base = base_path();

        return array_merge(
            [
                $base.'/public/assets/ukv.css',
                $base.'/resources/views/layouts/public.blade.php',
                $base.'/resources/views/partials/site-header.blade.php',
                $base.'/resources/views/partials/site-footer.blade.php',
                $base.'/resources/views/track.blade.php',
                $base.'/resources/views/public/checklist-result.blade.php',
                $base.'/resources/views/confirmation.blade.php',
                $base.'/resources/views/errors/404.blade.php',
            ],
            glob($base.'/resources/views/public/*.blade.php') ?: [],
            glob($base.'/resources/views/partials/*.blade.php') ?: [],
        );
    }

    public function test_no_template_references_google_fonts_cdn_or_plus_jakarta(): void
    {
        foreach (array_unique($this->fontBearingSources()) as $file) {
            if (! is_file($file)) {
                continue;
            }
            $contents = file_get_contents($file);
            $this->assertStringNotContainsString('fonts.googleapis.com', $contents, "Google Fonts CDN in {$file}");
            $this->assertStringNotContainsString('fonts.gstatic.com', $contents, "Google Fonts CDN in {$file}");
            $this->assertStringNotContainsString('Plus Jakarta', $contents, "Retired Plus Jakarta font in {$file}");
        }
    }

    public function test_design_system_self_hosts_outfit(): void
    {
        $css = file_get_contents(base_path().'/public/assets/ukv.css');

        $this->assertStringContainsString('@font-face', $css);
        $this->assertStringContainsString('/fonts/outfit-', $css, 'ukv.css must @font-face the self-hosted Outfit weights');
        $this->assertStringContainsString("'Outfit'", $css);
    }
}
