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
            "img-src 'self' data: https: ",
            "font-src 'self' data:",
            "style-src 'self' 'unsafe-inline'",
            // 'unsafe-inline' needed: the public pages carry inline <script> (apply funnel
            // routing, checker JS, reveal). A per-request nonce is the stricter future upgrade.
            // Consent-loaded third parties (cookie-consent partial): Trustpilot reviews widget,
            // Google Tag Manager + GA4, Microsoft Clarity, Meta Pixel.
            "script-src 'self' 'unsafe-inline' https://js.stripe.com https://widget.trustpilot.com https://invitejs.trustpilot.com https://www.googletagmanager.com https://www.google-analytics.com https://*.clarity.ms https://connect.facebook.net",
            "connect-src 'self' https://api.stripe.com https://widget.trustpilot.com https://*.trustpilot.com https://www.google-analytics.com https://*.google-analytics.com https://*.analytics.google.com https://www.googletagmanager.com https://*.clarity.ms https://connect.facebook.net https://www.facebook.com",
            // google.com/maps: the /about location embed; widget.trustpilot.com: TrustBox iframe;
            // youtube-nocookie/youtube + player.vimeo: the CMS video block's privacy-friendly embeds.
            "frame-src https://js.stripe.com https://hooks.stripe.com https://www.google.com https://maps.google.com https://widget.trustpilot.com https://www.youtube-nocookie.com https://www.youtube.com https://player.vimeo.com",
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
            // 'unsafe-eval' is REQUIRED by Filament's Alpine.js — it evaluates x-data /
            // x-show expressions via new Function(); without it Alpine never boots and the
            // panel renders blank (content stays hidden under x-cloak). Scoped to /admin* only.
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' blob:",
            "worker-src 'self' blob:",
            "connect-src 'self'",
        ]);
    }
}
