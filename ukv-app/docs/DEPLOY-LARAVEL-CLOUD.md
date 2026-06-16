# Quick deploy to Laravel Cloud → your first live URL

The fastest way to get UKVisaCo onto a public URL. Laravel Cloud is purpose-built for this stack (PHP, MySQL, queue workers, scheduler, auto-SSL) — unlike Netlify, which can't run it.

> UI labels change over time; this is the flow, verify exact button names in the dashboard. App lives in the **`ukv-app/`** subdirectory of the repo — set that as the app root.

**Before you start:** read `GO-LIVE-RUNBOOK.md` Phase 0 — the three legal sign-offs (#124 GDPR, #125 VAT, #130 OISC) and the ICO fee (#215) should be cleared before taking real customer data/payments. You can deploy to a staging URL first without them.

---

## 1. Create the project
1. Sign in at **laravel.com/cloud** → **Create application**.
2. **Connect GitHub** → pick `usama658/beyondpassports`, branch `master`.
3. **App root / path:** `ukv-app` (the Laravel app is in this subfolder, not the repo root).
4. Region: **London / EU** (UK audience + data residency).

## 2. Add a database
1. In the project, **Create database** → **MySQL** (MariaDB-compatible).
2. Attach it to the app. Cloud injects `DB_*` automatically — don't hardcode them.

## 3. Environment variables
Set these in the app's **Environment** tab (full reference + meaning in `GO-LIVE-RUNBOOK.md` Phase 3). Minimum to boot + transact:

```
APP_NAME=UKVisaCo
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<your-cloud-domain>        # update to custom domain later
APP_KEY=                                     # Generate (see step 4)

# DB_* are injected by the attached database — leave unset.

UKV_BASE_URL=https://<your-cloud-domain>
UKV_FRONTEND_ORIGIN=https://<your-cloud-domain>
UKV_OWNER_EMAIL=you@yourdomain.com
UKV_PHONE="+44 ..."  UKV_PHONE_E164=+44...  UKV_WHATSAPP=44...  UKV_EMAIL=hello@yourdomain.com

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database

MAIL_MAILER=smtp                             # real SMTP (Postmark/SES/Mailgun)
MAIL_HOST=  MAIL_PORT=587  MAIL_USERNAME=  MAIL_PASSWORD=
MAIL_FROM_ADDRESS=hello@yourdomain.com  MAIL_FROM_NAME="UKVisaCo"

# Integrations — blank = feature simply off (safe). Fill when ready:
STRIPE_SECRET=            STRIPE_WEBHOOK_SECRET=        # step 7
HUBSPOT_TOKEN=                                          # ROTATE the old dev token first
ANTHROPIC_API_KEY=  ANTHROPIC_MODEL=claude-opus-4-8     # enables AI assist/vision/guide drafting
WHATSAPP_TOKEN=  WHATSAPP_PHONE_ID=  WHATSAPP_TEMPLATE=order_status_update
```

## 4. Build + deploy commands
- **Build / install:** `composer install --no-dev --optimize-autoloader` and (if assets) `npm ci && npm run build`.
- **App key:** run once via the dashboard console/SSH: `php artisan key:generate --force` (or set `APP_KEY` manually).
- **Post-deploy hook / release command** (runs every deploy):
  ```
  php artisan migrate --force
  php artisan config:cache && php artisan route:cache && php artisan view:cache
  ```
- Click **Deploy**. First deploy gives you a `https://<app>.laravel.cloud` URL.

## 5. Seed reference data (one-off, via console/SSH) — NOT the demo seeders
```
php artisan db:seed --class=DestinationSeeder --force
php artisan db:seed --class=DocumentRequirementSeeder --force
```
- **Do NOT run** `php artisan db:seed` bare (it seeds demo orders + a test user) and **do NOT run `FinderDemoSeeder`** in production (rows are literally prefixed "DEMO —").
- Replace destinations/centres/slots with **real verified data** before launch (#95/#96/#129).

## 6. Background processing
1. **Queue worker:** add a worker process running `php artisan queue:work` (emails, HubSpot sync, WhatsApp, lead sync, AI jobs all queue).
2. **Scheduler:** enable the scheduler (Laravel Cloud has a toggle; it runs `schedule:run` every minute). It drives: `ukv:purge-documents`, `ukv:reconcile-stripe`, `ukv:owner-digest`, `destinations:freshness`, `destinations:check-changes`, `slots:release-expired`.

## 7. Stripe webhook
1. In Stripe → **Developers → Webhooks → Add endpoint:** `https://<your-domain>/stripe/webhook`.
2. Subscribe to `checkout.session.completed` (+ `payment_intent.*`, `charge.refunded` as needed).
3. Copy the **signing secret** → set `STRIPE_WEBHOOK_SECRET` in env → redeploy.
4. Test a payment with Stripe test keys before switching to live keys.

## 8. Smoke test (the new live URL)
`/` · `/apply` (run a full journey to Stripe test checkout) · `/track` · `/documents` · `/find-a-centre` · `/document-checklist` · `/visa/turkey` · `/guides` · `/sitemap.xml` · `/admin` (log in, set up 2FA via My Profile).

## 9. Custom domain + SSL
1. App → **Domains** → add `yourdomain.com`.
2. Point DNS (CNAME/A) as shown; Laravel Cloud provisions **SSL automatically**.
3. Update `APP_URL`, `UKV_BASE_URL`, `UKV_FRONTEND_ORIGIN` to the custom domain → redeploy.

## 10. Go live
- Switch Stripe to **live** keys (+ live webhook secret).
- Confirm legal sign-offs (#124/#125/#130) + ICO registration (#215) are done.
- Final smoke (step 8) on the custom domain → announce. Retire any old WordPress (#189).

---

**Result:** a public `https://` URL running the full app. Until real data + keys are in, it runs on demo/eVisa data with integrations dormant (safe). Everything else (host requirements, full env reference, supervisor/cron details for a non-Cloud VPS) is in `GO-LIVE-RUNBOOK.md`.
