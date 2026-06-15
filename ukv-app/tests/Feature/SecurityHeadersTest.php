<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Audit L-3 regression: SecurityHeaders middleware (app/Http/Middleware/SecurityHeaders.php)
 * is appended to the web group in bootstrap/app.php, so every HTML web response must carry the
 * defensive headers. The public funnel gets the STRICT CSP; /admin* gets the relaxed Filament CSP.
 *
 * Contract assertions only (header presence + CSP directive substrings) — no brittle HTML.
 */
final class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_includes_baseline_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_home_page_has_strict_csp_with_stripe_script_src(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp, 'Public home page must carry a Content-Security-Policy.');

        // script-src must allow inline (funnel JS) + Stripe.js.
        $this->assertStringContainsString('script-src', $csp);
        $this->assertStringContainsString("'unsafe-inline'", $csp);
        $this->assertStringContainsString('https://js.stripe.com', $csp);
    }

    public function test_public_csp_is_the_strict_variant_without_admin_relaxations(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);

        // The strict (public) variant constrains Stripe connect/frame hosts and form-action.
        $this->assertStringContainsString('connect-src', $csp);
        $this->assertStringContainsString('https://api.stripe.com', $csp);
        $this->assertStringContainsString('form-action', $csp);

        // It must NOT carry the admin-only Filament relaxations (blob: worker/script + worker-src).
        $this->assertStringNotContainsString('worker-src', $csp);
        $this->assertStringNotContainsString('blob:', $csp);
        $this->assertStringNotContainsString("script-src 'self' 'unsafe-inline' blob:", $csp);
    }

    public function test_admin_login_returns_a_relaxed_csp_too(): void
    {
        // /admin/login is a public Filament HTML page (no auth needed to view the form).
        $response = $this->get('/admin/login');

        // It renders (200) rather than redirecting; either way it must carry the relaxed CSP.
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp, 'Admin login must carry a (relaxed) Content-Security-Policy.');

        // The relaxed Filament variant permits blob: workers/scripts the public strict CSP forbids.
        $this->assertStringContainsString('worker-src', $csp);
        $this->assertStringContainsString('blob:', $csp);

        // Baseline headers apply everywhere.
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }
}
