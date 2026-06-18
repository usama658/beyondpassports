<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The site self-hosts Outfit (privacy + reliability). No public page may pull from
 * the Google Fonts CDN, and the legacy Plus Jakarta font must be gone.
 */
final class NoGoogleFontsTest extends TestCase
{
    /** @return list<array{0:string}> */
    public static function publicRoutes(): array
    {
        return [['/'], ['/track'], ['/tools'], ['/about'], ['/contact'], ['/reviews']];
    }

    /** @dataProvider publicRoutes */
    public function test_page_does_not_load_google_fonts(string $path): void
    {
        $html = $this->get($path)->getContent();
        $this->assertStringNotContainsString('fonts.googleapis.com', $html);
        $this->assertStringNotContainsString('Plus Jakarta', $html);
    }
}
