# Beyond Passports — A2 Hosting staging deploy (test mode)

Host: **A2 Hosting**, Drive plan, server `mi3-ss112`, cPanel 134, PHP 8.2+, MariaDB 10.5, SSH available.
Goal: site **reachable + browsable** on the domain in **test mode** (Stripe TEST, `QUEUE=sync`).
Real payments stay OFF until the launch blockers in `DEPLOY.md` are cleared.

Replace `USER` with your cPanel username, `YOURDOMAIN` with your domain.
Repo: `https://github.com/usama658/beyondpassports.git` (branch `master`).

---

## 1. Database (cPanel → MySQL® Databases)
1. Create database: `USER_bp`
2. Create user + strong password: `USER_bpapp`
3. Add user to database → **ALL PRIVILEGES**.
4. Note the three values for `.env` (DB name, user, password).

## 2. PHP version (cPanel → MultiPHP Manager)
Set the (sub)domain to **PHP 8.2** (or 8.3). Confirm `ext-mbstring, ext-pdo_mysql, ext-bcmath, ext-curl, ext-gd, ext-zip` enabled (MultiPHP INI / “PHP Extensions”).

## 3. Clone the code (cPanel → Git™ Version Control)
- **Clone URL:** `https://github.com/usama658/beyondpassports.git`
  - If the repo is **private**: use `https://<TOKEN>@github.com/usama658/beyondpassports.git` (GitHub → Settings → Developer settings → fine-grained token, read-only on this repo) **or** add a cPanel SSH key as a GitHub deploy key.
- **Repository Path:** `/home/USER/beyondpassports`
- **Branch:** `master`

## 4. App setup (cPanel → Terminal, or SSH)
```bash
cd ~/beyondpassports

# deps (no dev, optimised). If composer OOMs: php -d memory_limit=-1 "$(command -v composer)" install ...
composer install --no-dev --optimize-autoloader

# env
cp .env.production.example .env
php artisan key:generate         # fresh APP_KEY — never reuse local
```
Edit `.env` (use `nano .env`) — staging/test values:
```
APP_ENV=production
APP_DEBUG=false
APP_NAME="Beyond Passports"
APP_URL=https://YOURDOMAIN

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=USER_bp
DB_USERNAME=USER_bpapp
DB_PASSWORD=********

QUEUE_CONNECTION=sync          # shared host, no supervisor → jobs run inline
MAIL_MAILER=log                # staging: emails to storage/logs (swap to real SMTP later)

UKV_BASE_URL=https://YOURDOMAIN
UKV_FRONTEND_ORIGIN=https://YOURDOMAIN
UKV_OWNER_EMAIL=you@YOURDOMAIN

# TEST mode — rehearse only, no real money
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_test_xxx
```
Then:
```bash
php artisan migrate --force
php artisan db:seed --force            # destinations + supply nodes (placeholders — see #129)
php artisan storage:link
php artisan config:cache route:cache view:cache
php artisan filament:optimize
chmod -R 775 storage bootstrap/cache

# admin login (pick a strong password)
php artisan tinker --execute "App\Models\User::create(['name'=>'Owner','email'=>'you@YOURDOMAIN','password'=>bcrypt('STRONGPASS'),'role'=>'admin']);"
```

## 5. Point the domain at /public  (NON-destructive: use a subdomain)
cPanel → **Domains** (or Subdomains) → create/edit the domain and set
**Document Root = `/home/USER/beyondpassports/public`**.
- Cleanest: a subdomain like `staging.YOURDOMAIN` → docroot `…/beyondpassports/public`.
- Do **not** repoint the primary `public_html` by deleting it. If you must use the root domain, ask me — the safe move is the subdomain first, swap later.

## 6. HTTPS (cPanel → SSL/TLS Status)
Run **AutoSSL** for the (sub)domain. Once green, the app forces HTTPS via its SecurityHeaders middleware. Keep `APP_URL` on `https://`.

## 7. Cron (cPanel → Cron Jobs) — every minute
```
* * * * * cd /home/USER/beyondpassports && /usr/local/bin/php artisan schedule:run >/dev/null 2>&1
```
(Find the right PHP binary with `which php` or use the `ea-php82` path from MultiPHP.)

## 8. Smoke test (test mode)
Browse: `/` → `/tools` → `/guides` → `/compare` → `/find-a-centre` → `/apply`.
`/admin` → log in as the owner user. Run one apply → Stripe **test card** `4242 4242 4242 4242` → confirmation → `/track`.

## 9. Updating later
cPanel → Git Version Control → **Pull**, then:
```bash
cd ~/beyondpassports && composer install --no-dev --optimize-autoloader \
  && php artisan migrate --force && php artisan config:cache route:cache view:cache
```

---

### Before REAL payments (do NOT skip — see DEPLOY.md §7)
- [ ] Verify every destination's fees / processing / passport-validity vs gov.uk (#129) — seeded values are placeholders.
- [ ] Live Stripe keys + webhook `https://YOURDOMAIN/stripe/webhook` (#98).
- [ ] Rotate HubSpot token (#181); Filament 2FA on (#197).
- [ ] ICO data-protection fee registered (#215).
