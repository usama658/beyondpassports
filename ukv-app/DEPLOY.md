# UKVisaCo — Deploy & Launch Runbook (Laravel app)

The app is **code-complete and tested** (62 passing). What remains to go live is **operational** —
keys, a host, and data verification. Work top to bottom; each step is a real launch blocker.

## 0. Pick a host (#188)
Laravel 12 needs **PHP 8.2+ + MySQL/MariaDB**. Options:
- **cPanel / shared (PHP+MySQL)** — cheapest; upload + `composer install`; cron + a queue alternative needed.
- **VPS** (DigitalOcean/Hetzner) — full control; run nginx+php-fpm+supervisor+cron. Recommended.
- **Forge/Ploi + VPS** — easiest managed Laravel deploy.
Static front (`/public`) is served by the same app — no separate Netlify needed (Laravel is canonical).

## 1. Environment (`.env` in production) — see `.env.production.example`
- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://yourdomain`, `APP_NAME=UKVisaCo`
- `php artisan key:generate` (fresh `APP_KEY` — never reuse the local one)
- `UKV_BASE_URL=https://yourdomain`, `UKV_FRONTEND_ORIGIN=https://yourdomain` (NOT `*`)
- DB creds; `QUEUE_CONNECTION=database` (with a worker) or `redis`
- `MAIL_*` real SMTP/Resend/Postmark (local is `log`)
- `UKV_OWNER_EMAIL=` owner digest recipient

## 2. Stripe (#98 / BLOCKER-2)
- `STRIPE_SECRET=sk_live_…` (use `sk_test_…` to rehearse first)
- Stripe Dashboard → Developers → Webhooks → add `https://yourdomain/stripe/webhook`, event
  `checkout.session.completed`; copy signing secret → `STRIPE_WEBHOOK_SECRET=whsec_…`
- Bespoke (manual-review) lane uses the Payment Links API — live key required.

## 3. Integrations
- `HUBSPOT_TOKEN=` — **ROTATE the old token** before use (the previous `pat-na2-…` is burned).
  Create the custom deal properties noted in HubSpotService, or it silently drops them.
- `ANTHROPIC_API_KEY=` (+ `ANTHROPIC_MODEL`) for AI assist (optional; no-op without).

## 4. Build + migrate + seed
```
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --force          # destinations + supply nodes (NOT demo orders in prod)
php artisan storage:link
php artisan config:cache route:cache view:cache
php artisan filament:optimize
```
Create the admin user (don't use the demo creds):
```
php artisan tinker --execute "App\Models\User::create(['name'=>'Owner','email'=>'YOU@domain','password'=>'STRONG','role'=>'admin']);"
```

## 5. Queue worker + scheduler (#196 / BLOCKER-4)
Emails + HubSpot sync are queued; purge/reconcile/owner-digest are scheduled.
- **Worker** (supervisor): `php artisan queue:work --tries=3 --timeout=60`
- **Cron** (every minute): `* * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1`
- Shared host without supervisor: set `QUEUE_CONNECTION=sync` (jobs run inline — fine at low volume) and a cron for `schedule:run`.

## 6. HTTPS + security (#207, H-1)
- TLS cert (Let's Encrypt). Force HTTPS.
- Confirm SecurityHeaders middleware active (it is, `bootstrap/app.php`); CSP relaxes only on `/admin`.
- Enable Filament 2FA (#197) — requires a 2FA package/plugin; add before taking real PII.
- `config/cors.php` allowlist = your origin only.

## 7. Data accuracy (#129 / H-5) — COMMERCIAL/LEGAL BLOCKER
Seeded fees, government fees, processing times, passport-validity and required-docs are **PLACEHOLDERS**.
Verify every destination against gov.uk + the issuing authority and update via Filament → Destinations
**before** taking real payments.

## 8. Smoke test (test mode) before going live
home → checker → `/apply` → standard lane → Stripe **test card** `4242 4242 4242 4242` → webhook →
order `paid` + `paid_at` set → confirmation page → `/track` shows stage → order_paid email in inbox/log →
order visible in `/admin`. Then repeat the manual-review lane (callback). Then switch to live keys.

## 9. Go-live (#189)
Point DNS, switch Stripe to live, disable demo data, final `config:cache`, monitor logs + the
reconciliation report. Retire the old WordPress build + the static `frontend/` prototype.

## Launch-blocker checklist
- [ ] Host chosen + provisioned (#188)
- [ ] Prod `.env` + fresh `APP_KEY` (#207)
- [ ] Stripe live keys + webhook (#98)
- [ ] HubSpot token rotated (#181 cutover)
- [ ] Queue worker + cron (#196)
- [ ] HTTPS + 2FA (#197)
- [ ] Fees/processing verified vs gov.uk (#129)
- [ ] Test-mode smoke passed
- [ ] DNS + go-live (#189)
