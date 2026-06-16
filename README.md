# Beyond Passports

Independent UK **outbound** visa, eVisa, ETA & IDP facilitation platform — for British travellers going abroad. We check, prepare, submit and track applications; we are **not** a government website, our service fee is separate from any government fee, "express" speeds *our* handling (not the government's decision), and we never guarantee approval.

The product is a single **Laravel + MariaDB + Filament** application that serves the public marketing/SEO site, the customer apply→pay→track journey, and the back-office operations admin. The legacy WordPress build has been retired.

## Stack

- **Laravel 12** (PHP 8.2) · **MariaDB/MySQL** · **Filament 3** admin (filament-breezy 2FA)
- **Stripe** Checkout + webhooks + Payment Links · **HubSpot** CRM sync · **Anthropic** (text + vision, model `claude-opus-4-8`)
- Queued Mailables · scheduled commands · Vite assets
- Tests: PHPUnit on sqlite `:memory:` — **141 passing**

## Layout

```
ukv-app/                 The Laravel application (canonical product)
  app/                   Services, Filament resources, controllers, models, jobs, commands
  resources/views/       Public Blade silo + emails + partials
  database/migrations    Schema  ·  database/seeders  Reference + demo data
  tests/Feature          Feature/contract tests
  docs/                  GO-LIVE-RUNBOOK.md, PROFESSIONAL-SIGNOFF-EMAILS.md,
                         superpowers/specs (design specs) + port (audits, compliance, training)
  DEPLOY.md              Deployment notes  ·  .env.production.example
exports/                 site-structure.csv
```

## Local development

> `php` is not on PATH in the reference dev box; commands shell to the XAMPP binary. Adjust to your environment. Run all `artisan` commands from inside `ukv-app/`.

```bash
cd ukv-app
composer install
cp .env.example .env          # then set DB + any integration keys (all optional; blank = feature off)
php artisan key:generate
php artisan migrate
php artisan db:seed --class=DestinationSeeder
php artisan db:seed --class=DocumentRequirementSeeder
npm ci && npm run build
php artisan serve
php artisan test               # full suite
```

Admin panel: `/admin` (role-gated, 2FA via My Profile).

## What's built

- **Apply funnel** — eligibility capture + routing (standard self-serve vs manual-review), Stripe checkout, confirmation, lifecycle emails, public status tracker, post-pay document upload with GDPR auto-purge.
- **Operations (Filament)** — orders hub, production-line board + stage gates + SOPs, eligibility clear/refer, barriers, supply-chain registry, QA gate, appointments, groups/loyalty, refunds, Stripe reconciliation, owner digest, reports.
- **Document Requirements engine** — conditional, data-driven per-traveller checklist (engine, not hardcoded); surfaced on destination/apply/confirmation/track + awaiting-docs email.
- **Guide engine + SEO silo** — DB-driven guides, hybrid country-cluster URLs (`/visa/{country}/{topic}`), Article/FAQ/HowTo schema, E-E-A-T byline, AI drafting with a no-invention validator + publish gate, plus freshness + AI change-detection modules.
- **Document-checklist tool** — public `/document-checklist` (value-first), multi-channel delivery (email · WhatsApp · PDF · `.ics` · shareable link) + lead capture with a transactional/marketing consent split.
- **Centre finder + slots** — `/find-a-centre` (postcode / geolocation), nearest IDP/VAC/partner centres, held-slot inventory, hold-on-apply (in-person destinations only), home + IDP-page surfaces.
- **Integrations (all guarded/leak-gated)** — HubSpot sync, AI assist + vision doc-review, fraud/risk guard, WhatsApp notifications.
- **Compliance** — full legal copy (privacy/terms/complaints/disclaimer), CCRs 2013 14-day cancellation consent, FCA-safe insurance introducer, WCAG fixes.

## Go-live

The code is launch-ready. Remaining steps are owner/professional, **not** engineering — see **`ukv-app/docs/GO-LIVE-RUNBOOK.md`**:

- **Ops:** PHP host + domain + SSL (Laravel Cloud/Forge/Ploi/VPS — **not** Netlify; this is a server app, not static), production `.env`, live Stripe keys, queue worker + scheduler cron + SMTP, ICO data-protection fee, rotate the HubSpot token, set `ANTHROPIC_API_KEY`.
- **Data:** real centre geo + slots, verified per-destination docs/fees/validity, more destinations, real guide content via `php artisan guides:draft` → review/publish.
- **Legal sign-off (gating):** GDPR cross-border transfers, VAT treatment, OISC/IAA scope — ready-to-send drafts in **`ukv-app/docs/PROFESSIONAL-SIGNOFF-EMAILS.md`**.

## Compliance constraints (must hold)

Not a government website · service fee separate from government fee · express = our handling, not the decision · no approval guarantee · IDP = guided self-service at PayPoint (in person) · never send passport numbers/scans to AI unredacted · AI content is draft-only + human-reviewed · never commit live secrets.
