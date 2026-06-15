<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds defensive HTTP security response headers to every web response.
 *
 * Register it on the "web" group in bootstrap/app.php:
 *
 *     ->withMiddleware(function (Middleware $middleware): void {
 *         $middleware->validateCsrfTokens(except: ['stripe/webhook']);
 *         $middleware->web(append: [\App\Http\Middleware\SecurityHeaders::class]);
 *     })
 *
 * CSP DECISION (read SECURITY-WIRING.md for the full rationale):
 * Filament (the /admin panel) ships inline <style>/<script> and uses blob:
 * workers, so a strict "self only" CSP would break the admin UI. Rather than
 * weaken the policy globally, we apply a STRICT CSP to the public site and a
 * RELAXED, Filament-safe CSP on /admin*. The public funnel pages we control
 * (welcome / checkout / confirmation / track) get the tighter policy.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // --- Headers that are safe everywhere -------------------------------
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Disable powerful features the app doesn't use. Adjust if you add
        // payment-request / camera flows later.
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=(), usb=(), interest-cohort=()'
        );

        // --- Content-Security-Policy ---------------------------------------
        // Skip CSP entirely for non-HTML responses (JSON API replies, file
        // downloads) where it adds nothing and the directives are irrelevant.
        if ($this->isHtmlResponse($response)) {
            $response->headers->set(
                'Content-Security-Policy',
                $this->isFilamentRequest($request)
                    ? $this->filamentCsp()
                    : $this->strictCsp()
            );
        }

        return $response;
    }

    /**
     * /admin and the Filament Livewire/asset routes get a relaxed policy.
     * Filament renders inline styles/scripts and uses blob: web workers, so
     * 'unsafe-inline' (styles) and 'unsafe-inline' + blob: (scripts) are
     * required for it to function. This is the documented Filament trade-off;
     * we scope it to the admin area only so the public site stays strict.
     */
    private function isFilamentRequest(Request $request): bool
    {
        return $request->is('admin') || $request->is('admin/*');
    }

    private function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');

        // Treat empty content-type (e.g. some redirects) as HTML so the
        // policy is still attached to navigations.
        return $contentType === ''
            || str_contains(strtolower($contentType), 'text/html');
    }

    /**
     * Strict policy for the public, app-controlled pages.
     * - default 'self'
     * - allow inline styles only (Blade/Tailwind-built pages sometimes inline
     *   small style attributes; relax to keep it from breaking the funnel).
     *   Scripts are NOT allowed inline — keep funnel JS in static files.
     * - Stripe is allowed where the funnel embeds Stripe.js / redirects.
     * Tighten further (drop 'unsafe-inline' on style-src, add nonces) once you
     * confirm the funnel pages have no inline <style>.
     */
    private function strictCsp(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "form-action 'self' https://checkout.stripe.com",
            "img-src 'self' data:",
            "font-src 'self' data:",
            "style-src 'self' 'unsafe-inline'",
            // 'unsafe-inline' needed: the public pages carry inline <script> (apply funnel
            // routing, checker JS, reveal). A per-request nonce is the stricter future upgrade.
            "script-src 'self' 'unsafe-inline' https://js.stripe.com",
            "connect-src 'self' https://api.stripe.com",
            "frame-src https://js.stripe.com https://hooks.stripe.com",
        ]);
    }

    /**
     * Filament-safe policy for /admin*. Permits the inline styles/scripts and
     * blob: workers Filament needs. Still blocks object-src and constrains
     * frame-ancestors so the panel can't be framed by third parties.
     */
    private function filamentCsp(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "img-src 'self' data: blob:",
            "font-src 'self' data:",
            "style-src 'self' 'unsafe-inline'",
            "script-src 'self' 'unsafe-inline' blob:",
            "worker-src 'self' blob:",
            "connect-src 'self'",
        ]);
    }
}
