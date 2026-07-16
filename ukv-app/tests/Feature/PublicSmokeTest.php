<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Renders every parameterless public URL and asserts it does not 500. Includes the ORPHANED landing
 * pages (noindex, not in the sitemap/nav) that no other test or the link-crawler ever hits — exactly
 * where a latent fatal (e.g. a stripped-backslash class name) hid until now. A page may 200 or
 * redirect (3xx) or 404, but must never throw a server error.
 */
final class PublicSmokeTest extends TestCase
{
    use RefreshDatabase;

    public static function publicUrls(): array
    {
        return array_map(fn ($u) => [$u], [
            // Core pages
            '/', '/services', '/tour-packages', '/tools', '/find-a-centre',
            '/about', '/contact', '/contact/thank-you', '/legal', '/compare',
            '/guides', '/reviews', '/document-checklist', '/apply', '/apply/thank-you',
            '/schengen-visa', '/schengen-visa-consultancy', '/sitemap.xml',
            '/documents', '/driving-abroad',
            // Orphaned landing pages (noindex; reachable by URL only)
            '/schengen-visa-agent', '/schengen-visa-agent-premium', '/schengen-visa-refusal-risk',
            '/schengen-visa-appointment', '/honest-schengen-visa-service', '/schengen-visa-refused',
            '/schengen-visa-help',
            // Health endpoints
            '/health/stripe', '/health/mail',
        ]);
    }

    /**
     * @dataProvider publicUrls
     */
    public function test_public_url_does_not_error(string $url): void
    {
        $status = $this->get($url)->baseResponse->getStatusCode();

        $this->assertLessThan(500, $status, "GET {$url} returned {$status}");
    }
}
