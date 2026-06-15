/* ukv-config.js — front-end → backend wiring.
 *
 * Sets the base URL of the Laravel API the static site talks to.
 * The static site (Netlify) and the Laravel app are deployed SEPARATELY,
 * so the front-end must know where the app lives.
 *
 * CHANGE THIS PER ENVIRONMENT:
 *   • Local dev   →  http://localhost:8000   (php artisan serve default)
 *   • Production  →  https://api.your-laravel-host.example   (no trailing slash)
 *
 * Endpoints consumed (relative to UKV_API_BASE):
 *   POST  /apply                  → { lane, order_ref, next, checkout_hint }
 *   POST  /checkout/{order_ref}   → redirects browser to Stripe
 *   GET   /track                  → server-rendered status page
 *   POST  /track/lookup           → status lookup (field: ref)
 *
 * NOTE: the Laravel host must send CORS headers that allow this site's origin
 * (see README-deploy.md → "Backend hand-off / CORS").
 */
window.UKV_API_BASE = 'http://localhost:8000';
