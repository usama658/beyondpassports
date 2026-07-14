# Beyond Passports — Go-Live Runbook (owner edition)

A step-by-step walkthrough to take the Laravel app live, written so someone with **no prior
Laravel knowledge** can follow it. Work through the phases **in order** — each one ends with a
**"Done when"** check. Don't move on until that check passes.

This expands `DEPLOY.md` and `.env.production.example` (read those for the short version). Task
numbers in `(#…)` refer to the project tracker.

**Conventions used below**
- `yourdomain.com` — replace with your real domain everywhere it appears.
- `/var/www/ukv` — the folder the app lives in on the server (a VPS path; adjust for your host).
- Lines starting with `$` are commands you paste into the server terminal (SSH), **without** the `$`.
- "the app folder" = the directory that contains `artisan`, `composer.json`, and `public/`.

A quick mental model: this is a **Laravel 12 PHP app**. The web server (nginx/Apache) serves the
`public/` folder only; everything else is private. A **queue worker** sends emails/CRM syncs in the
background, and a **cron** runs scheduled jobs once a minute. Payments go through **Stripe**.

---

## Phase 0 — Pre-flight: don't start until these are true

**What/why.** Three things will block a real launch no matter how clean the deploy is. Confirm them
before spending money on a server.

1. **Legal sign-offs cleared** (see Phase 9 for detail): GDPR (#124), VAT registration decision
   (#125), and OISC/immigration-advice positioning (#130). The site takes real PII and money — do
   not go live without these resolved.
2. **You have a domain** you control the DNS for.
3. **You have payment + integration accounts**: Stripe, an SMTP/email provider, and (optionally)
   HubSpot, Anthropic, WhatsApp Business.

**Done when:** you can tick all three. If any is "no", stop and resolve it first.

---

## Phase 1 — Server, PHP, database, domain, SSL (#188, #207)

**What/why.** The app needs a Linux host with **PHP 8.2+**, **MySQL/MariaDB**, a web server, and
**HTTPS**. A VPS (DigitalOcean/Hetzner) with a managed Laravel layer (**Laravel Forge** or **Ploi**)
is strongly recommended for a non-developer — Forge/Ploi install PHP, nginx, MySQL, the queue worker,
the cron, and the SSL certificate for you through a web UI. The commands below are the manual VPS
path; if you use Forge/Ploi, you do these through their dashboard instead and can skip straight to
Phase 2.

**1.1 Get a server.** Provision an Ubuntu 22.04/24.04 VPS (2 GB RAM minimum). Note its IP address.

**1.2 Point your domain at it.** In your DNS provider, create an **A record**:
`@  →  <server IP>` and `www  →  <server IP>`. (DNS can take up to an hour. You can do app setup
in parallel.)

**1.3 Install the stack** (manual VPS only):
```
$ sudo apt update && sudo apt upgrade -y
$ sudo apt install -y nginx mariadb-server unzip git \
    php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl \
    php8.2-zip php8.2-bcmath php8.2-gd php8.2-intl
$ sudo mysql_secure_installation        # set a root DB password, answer Y to the rest
```

**1.4 Install Composer (PHP package manager) and Node.js (for the front-end build):**
```
$ curl -sS https://getcomposer.org/installer | php
$ sudo mv composer.phar /usr/local/bin/composer
$ curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
$ sudo apt install -y nodejs
```

**1.5 Create the database and a DB user** (remember these — they go in `.env` in Phase 3):
```
$ sudo mysql
mysql> CREATE DATABASE ukv CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
mysql> CREATE USER 'ukv'@'localhost' IDENTIFIED BY 'CHOOSE-A-STRONG-PASSWORD';
mysql> GRANT ALL PRIVILEGES ON ukv.* TO 'ukv'@'localhost';
mysql> FLUSH PRIVILEGES;  EXIT;
```

**1.6 SSL via Let's Encrypt** (after the web server is configured in Phase 2; do this once nginx
serves the site):
```
$ sudo apt install -y certbot python3-certbot-nginx
$ sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```
Choose "redirect HTTP → HTTPS" when prompted. Certbot auto-renews.

**Done when:** `php8.2 -v` shows PHP 8.2+, `mysql -u ukv -p ukv` connects, your domain resolves to the
server IP (`ping yourdomain.com`), and (after Phase 2) `https://yourdomain.com` shows a valid padlock.

---

## Phase 2 — Get the code on the server + build it

**What/why.** Pull the app onto the server, install only the production PHP libraries, build the CSS/JS
assets with Vite, and set folder permissions so the app can write logs and cache.

**2.1 Clone the repo into the app folder:**
```
$ sudo mkdir -p /var/www/ukv && sudo chown $USER:$USER /var/www/ukv
$ git clone <your-repo-url> /var/www/ukv
$ cd /var/www/ukv/ukv-app          # the Laravel app lives in the ukv-app/ subfolder
```
> All remaining commands run **inside `/var/www/ukv/ukv-app`** (the folder with `artisan`).

**2.2 Install production PHP dependencies** (`--no-dev` skips test-only packages):
```
$ composer install --no-dev --optimize-autoloader
```

**2.3 Build the front-end assets** (Vite — produces the compiled CSS/JS in `public/build`):
```
$ npm ci
$ npm run build
```

**2.4 Storage permissions + symlink.** The web server user (`www-data` on Ubuntu) must be able to
write to `storage/` and `bootstrap/cache/`. `storage:link` exposes uploaded files publicly.
```
$ sudo chown -R www-data:www-data storage bootstrap/cache
$ sudo chmod -R 775 storage bootstrap/cache
$ php artisan storage:link
```

**2.5 Point nginx at `public/`.** The web root **must** be `/var/www/ukv/ukv-app/public` (never the
app root — that would expose `.env`). Configure an nginx server block for `yourdomain.com` with that
root and PHP-FPM, then reload nginx. (Forge/Ploi do this for you.) After nginx serves the site, run
the certbot command from Phase 1.6.

**Done when:** `composer install` and `npm run build` both finish with no errors, `public/build/`
exists, and `ls -la storage/logs` shows the folder is owned by `www-data`.

---

## Phase 3 — Production `.env` (the configuration file)

**What/why.** `.env` holds every secret and setting. Copy the template, fill in real values, then
generate a unique encryption key. **Never reuse the local `APP_KEY`** and never commit `.env`.

**3.1 Create it from the template:**
```
$ cp .env.production.example .env
$ php artisan key:generate          # fills APP_KEY automatically
```

**3.2 Edit `.env`** (`nano .env`) and set every variable below. Variables marked **(optional)** can be
left blank — blank simply turns that feature off (the app degrades gracefully).

### Core app
| Var | Set to | Notes |
|---|---|---|
| `APP_NAME` | `Beyond Passports` | |
| `APP_ENV` | `production` | must be `production` |
| `APP_KEY` | *(auto)* | set by `key:generate` — leave it |
| `APP_DEBUG` | `false` | **never** `true` in production (leaks secrets) |
| `APP_URL` | `https://yourdomain.com` | |

### Public site config (UKV_*)
| Var | Set to | Notes |
|---|---|---|
| `UKV_BASE_URL` | `https://yourdomain.com` | used in emails, canonicals, sitemap host |
| `UKV_FRONTEND_ORIGIN` | `https://yourdomain.com` | CORS allowlist — **NOT** `*` |
| `UKV_OWNER_EMAIL` | your ops inbox | recipient of the daily owner digest |
| `UKV_DOC_RETENTION_DAYS` | `90` | GDPR: days after order closure before uploaded docs are purged |
| `UKV_PHONE` | `+44 20 1234 5678` | **(optional)** display number; blank = placeholder |
| `UKV_PHONE_E164` | `+442012345678` | **(optional)** for `tel:` links |
| `UKV_WHATSAPP` | `442012345678` | wa.me number, digits only. **Powers the home hero "Chat to our UK team" form + every wa.me link. Blank → `440000000000` placeholder = dead chat. Set the real WhatsApp business number before launch.** |
| `UKV_EMAIL` | `hello@beyondpassports.co.uk` | **(optional)** public enquiries address |

### Document-checklist tool (UKV_CHECKLIST_*) — all optional, sensible defaults
| Var | Default | Notes |
|---|---|---|
| `UKV_CHECKLIST_PROCESSING_DAYS` | `21` | assumed processing time for the .ics "apply by" deadline |
| `UKV_CHECKLIST_BUFFER_DAYS` | `7` | safety buffer subtracted from the deadline |
| `UKV_CHECKLIST_STICKY_BAR` | `true` | sticky save/email/share/apply bar on results |

### Appointment slots (UKV_SLOTS_*) — all optional
| Var | Default | Notes |
|---|---|---|
| `UKV_SLOTS_AUTO_HOLD` | `true` | tentatively hold the soonest slot when an in-person order is created |
| `UKV_SLOTS_HOLD_MINUTES` | `60` | how long a hold lasts before `slots:release-expired` frees it |

### Database (from Phase 1.5)
| Var | Set to |
|---|---|
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `127.0.0.1` |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | `ukv` |
| `DB_USERNAME` | `ukv` |
| `DB_PASSWORD` | *(the strong password from 1.5)* |

### Queue / cache / session
| Var | Set to | Notes |
|---|---|---|
| `QUEUE_CONNECTION` | `database` | needs a worker (Phase 6). On a shared host with no worker, use `sync` (jobs run inline — fine at low volume). |
| `CACHE_STORE` | `database` | |
| `SESSION_DRIVER` | `database` | |

### Mail / SMTP (required — without it, no customer or owner emails send)
| Var | Set to | Notes |
|---|---|---|
| `MAIL_MAILER` | `smtp` | |
| `MAIL_HOST` | your provider's host | e.g. Postmark/Resend/SES/Mailgun |
| `MAIL_PORT` | `587` | |
| `MAIL_USERNAME` | from provider | |
| `MAIL_PASSWORD` | from provider | |
| `MAIL_FROM_ADDRESS` | `hello@beyondpassports.co.uk` | |
| `MAIL_FROM_NAME` | `${APP_NAME}` | |

### Stripe (Phase 5 covers obtaining these)
| Var | Set to | Notes |
|---|---|---|
| `STRIPE_SECRET` | `sk_test_…` first, then `sk_live_…` | rehearse in test mode before live |
| `STRIPE_WEBHOOK_SECRET` | `whsec_…` | from the webhook you create in Phase 5 |

### Integrations
| Var | Set to | Notes |
|---|---|---|
| `HUBSPOT_TOKEN` | new token | **ROTATE FIRST** — the old `pat-na2-…` is burned (#181). **(optional)** — blank = no CRM sync. Create the custom deal properties (see HubSpotService) or they're silently dropped. |
| `ANTHROPIC_API_KEY` | your key | **(optional)** — AI assist / guide drafting; no-op if blank |
| `ANTHROPIC_MODEL` | `claude-opus-4-8` | leave as-is unless told otherwise |
| `WHATSAPP_TOKEN` | Meta Cloud API token | **(optional)** — blank = no customer WhatsApp pings |
| `WHATSAPP_PHONE_ID` | Meta phone-number ID | **(optional)** |
| `WHATSAPP_TEMPLATE` | `order_status_update` | the Meta-approved template name for out-of-24h-window sends |
| `UKV_INSURANCE_PARTNER` | partner name | **(optional)** FCA-safe introducer signpost; blank = neutral "ask us" note |
| `UKV_INSURANCE_URL` | partner landing URL | **(optional)** |

**Done when:** `php artisan about` runs without errors and shows `Environment: production`,
`Debug Mode: OFF`, and your database name. (It connects to the DB to do this — a DB error here means
fix the `DB_*` values before continuing.)

---

## Phase 4 — Database migrations + reference seed data (#129, #95, #96)

**What/why.** `migrate` builds the database tables. The **reference seeders** load the destinations
catalogue and document-requirement rules the site needs to function. The default `db:seed` also loads
**demo/test data you must NOT keep in production**.

**4.1 Build the tables:**
```
$ php artisan migrate --force        # --force = "yes, run in production"
```

**4.2 Seed only the reference data** (run the two reference seeders individually — do **not** run the
bare `db:seed`, which also creates a test user and demo orders):
```
$ php artisan db:seed --class=DestinationSeeder --force
$ php artisan db:seed --class=DocumentRequirementSeeder --force
$ php artisan db:seed --class=SupplyNodeSeeder --force      # supply nodes (optional but harmless)
```

### ⚠️ WARNING — replace demo data with REAL verified data before launch (#129, #95, #96, #130)
The seeded data is **placeholder/demo** and is a **commercial + legal blocker** if shipped as-is:
- **`DestinationSeeder`** fees, government fees, processing times, passport-validity rules and
  required-docs are **PLACEHOLDERS**. Verify **every** destination against gov.uk and the issuing
  authority, then correct them in **Filament → Destinations** (#129).
- **`FinderDemoSeeder`** seeds **DEMO centres + slots** (rows literally named "DEMO — …"). **Do NOT
  run it in production.** If it was ever run, delete those rows and enter **real verified centres,
  addresses and appointment slots** before launch (#95, #96).
- `DemoOrderSeeder` / `TurkeyGoldGuidesSeeder` / the test user — demo/sample content only; do not run
  in production (except guides you've reviewed and want to publish).

**Done when:** `/destinations` lists real destinations, no centre named "DEMO —" appears under
**Filament → Supply Nodes**, and a spot-check of one destination's fees/processing time matches gov.uk.

**4.3 Create your real admin user** (replace the demo creds):
```
$ php artisan tinker --execute="App\Models\User::create(['name'=>'Owner','email'=>'hello@beyondpassports.co.uk','password'=>bcrypt('A-STRONG-PASSWORD'),'role'=>'admin']);"
```

**Done when:** you can log in at `https://yourdomain.com/admin` with that account.

---

## Phase 5 — Stripe: live keys, webhook, test a payment (#98)

**What/why.** Stripe takes the payments. You first rehearse in **test mode**, then flip to **live**.
The **webhook** is how Stripe tells the app a payment succeeded — without it, orders never move to
`paid`. The manual-review ("bespoke") lane also uses the Payment Links API, which needs a live key.

**5.1 Test mode rehearsal first.** In the Stripe Dashboard, toggle **Test mode** (top right). Copy
the **test secret key** (`sk_test_…`) into `STRIPE_SECRET`.

**5.2 Create the webhook endpoint:**
- Stripe Dashboard → **Developers → Webhooks → Add endpoint**.
- Endpoint URL: `https://yourdomain.com/stripe/webhook`
- Events to send: **`checkout.session.completed`**.
- Save, then click the endpoint and **Reveal signing secret** → copy the `whsec_…` value into
  `STRIPE_WEBHOOK_SECRET`.
- Re-run `php artisan config:cache` after editing `.env` (see Phase 7) so the new value is picked up.

**5.3 Test a payment end-to-end** (test mode): go through `/apply`, reach Stripe Checkout, pay with
test card **`4242 4242 4242 4242`**, any future expiry, any CVC. Then confirm the webhook fired
(Stripe Dashboard → Webhooks → your endpoint shows a `200` delivery) and the order flipped to `paid`
in `/admin`.

**5.4 Go live.** Toggle **Test mode OFF** in Stripe. Repeat 5.1–5.2 with the **live** values:
`STRIPE_SECRET=sk_live_…` and a **new live** webhook + its own `whsec_…` (test and live have separate
signing secrets). Update `.env`, then `php artisan config:cache`.

**Done when:** in test mode a `4242…` payment moves an order to `paid` (`paid_at` set) and the Stripe
webhook log shows `200`. Then, with live keys saved, the live webhook endpoint shows a `200` on its
first real or `$1` test transaction.

---

## Phase 6 — Queue worker + scheduler cron (#196)

**What/why.** Emails and HubSpot sync run on a **queue** (a background worker). Recurring jobs
(purge, reconcile, digests, freshness, slot release) run via Laravel's **scheduler**, which a system
**cron** triggers every minute. Without the worker, queued emails never send; without the cron, none
of the scheduled jobs run.

**6.1 Queue worker via supervisor** (keeps `queue:work` running and restarts it if it dies). Create
`/etc/supervisor/conf.d/ukv-worker.conf`:
```
[program:ukv-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ukv/ukv-app/artisan queue:work --tries=3 --timeout=60
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/ukv/ukv-app/storage/logs/worker.log
stopwaitsecs=3600
```
Then:
```
$ sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start ukv-worker:*
```
> **Shared host with no supervisor?** Set `QUEUE_CONNECTION=sync` in `.env` instead — jobs run inline
> at request time. Fine at low volume; you still need the cron below.

**6.2 Scheduler cron.** Add one line to the crontab (`sudo crontab -u www-data -e`):
```
* * * * * cd /var/www/ukv/ukv-app && php artisan schedule:run >> /dev/null 2>&1
```

**What the schedule runs** (defined in `routes/console.php`):
| Job | When | Purpose |
|---|---|---|
| `ukv:purge-documents` | daily | GDPR — delete uploaded docs past the retention window |
| `ukv:reconcile-stripe` | daily 06:00 | catch any Stripe payments the webhook missed |
| `ukv:owner-digest` | daily 08:00 | email the owner the day's pending actions |
| `destinations:freshness` | daily 07:00 | flag stale destination data |
| `destinations:check-changes` | weekly Mon 05:00 | AI change-detection on source pages |
| `slots:release-expired` | every 5 min | return expired appointment-slot holds to the pool |

**Done when:** `sudo supervisorctl status ukv-worker:*` shows `RUNNING`, and after a minute
`php artisan schedule:list` plus the cron log confirm the scheduler is firing (a test order's
confirmation email actually arrives, proving the worker drains the queue).

---

## Phase 7 — Cache + optimise for production

**What/why.** Compiling config, routes and views into cache makes the app noticeably faster.
**Re-run these every time you change `.env` or pull new code** — stale caches cause "my change
isn't showing" confusion.

```
$ php artisan config:cache
$ php artisan route:cache
$ php artisan view:cache
$ php artisan optimize
$ php artisan filament:optimize        # admin panel (Filament) asset/component cache
```
> If you edit `.env` later, the quickest reset is: `php artisan optimize:clear` then re-run the five
> commands above.

**Done when:** all five run without errors and the site still loads correctly at
`https://yourdomain.com`.

---

## Phase 8 — Smoke test (do this in Stripe TEST mode before flipping live)

**What/why.** Walk the real user journeys to prove the deployed app works before sending traffic. Tick
each one.

- [ ] **Home** `https://yourdomain.com/` loads over HTTPS, padlock valid, no console errors.
- [ ] **Apply** `https://yourdomain.com/apply` — complete the full journey through to **Stripe
  Checkout**; pay with test card `4242 4242 4242 4242`; land on the confirmation page; order shows
  `paid` in `/admin`; confirmation email arrives.
- [ ] **Track** `https://yourdomain.com/track` — look up the order you just placed; the current stage
  shows.
- [ ] **Documents** `https://yourdomain.com/documents` — upload a test file; it stores and lists.
- [ ] **Find a centre** `https://yourdomain.com/find-a-centre` — search a postcode; **real** centres
  return (no "DEMO —" rows).
- [ ] **Admin + 2FA** `https://yourdomain.com/admin` — log in with your owner account; **2FA enrolment
  is enforced** before real PII is exposed (#197). If 2FA isn't active yet, enable it now.
- [ ] **Sitemap** `https://yourdomain.com/sitemap.xml` returns valid XML with your real host.
- [ ] **A guide page** — open one destination guide, e.g. `https://yourdomain.com/visa/{slug}` and a
  guide article, and confirm content renders.

**Done when:** every box is ticked in test mode. Then switch Stripe to **live** keys (Phase 5.4) and
re-run the **Apply** flow once with a small real card to confirm the live webhook returns `200`.

---

## Phase 9 — Legal / compliance sign-offs (clear BEFORE go-live)

**What/why.** These are non-technical launch blockers. The site processes real personal data, takes
payments, and operates in a regulated advice space.

- [ ] **ICO registration (#215).** Register as a data controller with the UK ICO (data-protection
  fee). Keep the registration reference on file.
- [ ] **Rotate the HubSpot token (#181).** Reconfirm the old `pat-na2-…` token is revoked in HubSpot
  and only the new token is in production `.env`.
- [ ] **GDPR (#124).** Privacy policy live, lawful basis documented, the `UKV_DOC_RETENTION_DAYS`
  purge job confirmed working (Phase 6), data-subject request process in place.
- [ ] **VAT (#125).** Decision on VAT registration made; if registered, pricing/invoicing reflects it.
- [ ] **OISC / immigration advice (#130).** Confirm the service is positioned as **guided
  self-service**, not regulated immigration advice, and copy is consistent with that. Get sign-off.

**Done when:** every box is ticked and you have written confirmation/records for each.

---

## Phase 10 — Go-live + retire WordPress (#189)

**What/why.** The final cutover: send live traffic to the new Laravel app and decommission the old
WordPress site and the static `frontend/` prototype.

**10.1 Final pre-cutover checks:**
- Stripe is in **live** mode with live keys + live webhook returning `200` (Phase 5.4).
- Demo data removed; real destination + centre data verified (Phase 4).
- `APP_DEBUG=false`, caches rebuilt (Phase 7), worker + cron running (Phase 6).

**10.2 DNS cutover.** In your DNS provider, point the live domain's **A record** at the new server's
IP (and `www`). Lower the TTL beforehand (e.g. to 300s) so the switch propagates fast.

**10.3 Verify after propagation** (10–60 min): visit `https://yourdomain.com`, confirm it serves the
**Laravel** app (not the old WordPress), padlock valid, and re-run the Phase 8 smoke list quickly
against the live domain. Place one small live test order and confirm the webhook `200` + email.

**10.4 Monitor for the first 24–48h:**
- App log: `tail -f /var/www/ukv/ukv-app/storage/logs/laravel.log`
- Worker log: `/var/www/ukv/ukv-app/storage/logs/worker.log`
- Stripe Dashboard → Webhooks (watch for non-`200` deliveries).
- The daily reconciliation report / owner digest emails arrive.

**10.5 Retire WordPress.** Once the new site is confirmed stable, take down the old WordPress build
and the static `frontend/` prototype (the Laravel app is canonical). Keep a backup of the old site
before deleting.

**Done when:** the live domain serves the Laravel app over HTTPS, a real payment completes with a
`200` webhook, logs are clean, and the old WordPress site is offline (backed up).

---

## Quick launch-blocker checklist (the one-glance version)

- [ ] Host + PHP 8.2 + MariaDB + domain + SSL (Phase 1, #188/#207)
- [ ] Code cloned, `composer install --no-dev`, `npm run build`, storage perms (Phase 2)
- [ ] Production `.env` complete + fresh `APP_KEY` (Phase 3)
- [ ] `migrate --force` + reference seeders; **real data replaces demo** (Phase 4, #129/#95/#96)
- [ ] Stripe live keys + webhook + test payment passed (Phase 5, #98)
- [ ] Queue worker + scheduler cron running (Phase 6, #196)
- [ ] config/route/view caches built (Phase 7)
- [ ] Full smoke test passed in test mode (Phase 8)
- [ ] ICO + HubSpot rotation + GDPR/VAT/OISC sign-offs (Phase 9, #215/#181/#124/#125/#130)
- [ ] 2FA enforced on admin (#197)
- [ ] **Real WhatsApp number set** (`UKV_WHATSAPP`) — home hero chat form + all wa.me links; blank = `440000000000` dead-chat placeholder
- [ ] DNS cutover + verified + WordPress retired (Phase 10, #189)
