# Security Wiring

How to activate the security hardening added in this change set. These files
are inert until you wire the middleware in `bootstrap/app.php` and set the env
keys. No `composer`/`artisan` commands are required for CORS — Laravel's
`HandleCors` middleware reads `config/cors.php` automatically.

## 1. Register the SecurityHeaders middleware

Edit `bootstrap/app.php`. Add the `import` and the `$middleware->web(append:)`
line inside the existing `withMiddleware` closure. Final state:

```php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SecurityHeaders;   // <-- add this import

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Stripe posts its webhook without a CSRF token.
        $middleware->validateCsrfTokens(except: ['stripe/webhook']);

        // Add defensive security headers to every web response.
        $middleware->web(append: [SecurityHeaders::class]);   // <-- add this line
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

The two lines to add (verbatim):

```php
use App\Http\Middleware\SecurityHeaders;
```
```php
$middleware->web(append: [SecurityHeaders::class]);
```

CORS needs NO registration — `HandleCors` is in Laravel 12's global stack and
picks up the new `config/cors.php` on the next request (clear config cache if
you cache config in prod: `php artisan config:clear`).

## 2. Environment keys (`.env` — never commit)

| Key | Purpose | Dev | Production |
| --- | --- | --- | --- |
| `UKV_FRONTEND_ORIGIN` | Allowed CORS origin(s) for the Netlify front-end. Comma-separated list. | unset → defaults to `*` | **set to the exact origin(s)**, e.g. `https://apply.example.com` |

Example production block:

```dotenv
UKV_FRONTEND_ORIGIN=https://apply.example.com,https://www.example.com
```

Lock this down at cutover. While unset (`*`) any website can POST to
`/apply`, `/track/lookup`, and `/documents/upload` from a browser.

## 3. CSP decision (and /admin handling)

- The public, app-controlled pages get a **strict CSP**: `default-src 'self'`,
  no inline scripts, Stripe.js/Checkout allowlisted for the payment flow,
  `object-src 'none'`, `frame-ancestors 'self'`.
- **`/admin*` (Filament) is NOT excluded from security headers, but it IS
  served a separate, relaxed Filament-safe CSP.** Filament emits inline
  `<style>`/`<script>` and uses `blob:` web workers; a strict policy would
  break the panel. So `SecurityHeaders` detects `admin` / `admin/*` requests
  and applies a policy with `'unsafe-inline'` (styles + scripts) and
  `blob:` (scripts + workers). This is the standard documented Filament
  trade-off — kept scoped to the admin area so the public site stays tight.
- CSP is only attached to HTML responses; JSON API replies and downloads skip
  it. The non-CSP headers (nosniff, X-Frame-Options, Referrer-Policy,
  Permissions-Policy) apply to all responses.
- If the public funnel pages turn out to have no inline `<style>`, tighten
  `strictCsp()` by dropping `'unsafe-inline'` from `style-src` (ideally move to
  nonces).

## 4. Filament admin 2FA

Enable two-factor auth on the Filament panel for admin accounts. In your
Panel provider (e.g. `app/Providers/Filament/AdminPanelProvider.php`):

```php
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...existing config...
        ->multiFactorAuthentication()       // Filament v3 MFA
        ->emailVerification();              // optional, recommended
}
```

(Method names vary by Filament 3.x minor — confirm against the installed
version's auth docs. The intent: require a second factor for `/admin` login.)

## 5. Secrets handling + token rotation

- Live **HubSpot**, **Stripe**, and **Anthropic** secrets live in `.env` only.
  `.env` is gitignored — never commit real keys; keep placeholders in
  `.env.example`.
- **ROTATE the old HubSpot token at cutover.** Generate a fresh
  HubSpot private-app token, put it in production `.env`, deploy, then revoke
  the old token in HubSpot. Assume any previously shared/committed token is
  compromised.
- Rotate Stripe and Anthropic keys too if they were ever exposed; use
  restricted Stripe keys where possible and verify the webhook signing secret
  (`STRIPE_WEBHOOK_SECRET`) is set so `/stripe/webhook` rejects forgeries.
