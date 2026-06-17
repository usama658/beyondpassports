# Beyond Passports — A2 Hosting deploy (test mode) → beyondpassports.co.uk

Host: **A2 Hosting**, Drive plan, server `mi3-ss112`, cPanel 134, PHP 8.2+, MariaDB 10.5, SSH on.
Domain: **beyondpassports.co.uk** (apex).  Repo: **private** `github.com/usama658/beyondpassports` (`master`).
Goal: site **reachable + browsable** in **test mode** (Stripe TEST, `QUEUE=sync`). Real payments stay OFF
until the launch blockers at the bottom are cleared.

Replace `USER` with your cPanel username everywhere. Run shell steps in **cPanel → Terminal** (or SSH).

---

## 0. Point the domain's nameservers at A2 (registrar)
At whoever you bought beyondpassports.co.uk from, set nameservers:
```
ns1.a2hosting.com
ns2.a2hosting.com
```
(or the exact set in your A2 welcome email). Propagation up to 24–48h. Do this first — it runs in the background while you do the rest.

## 1. Database — cPanel → MySQL® Databases
1. Create DB `USER_bp`
2. Create user `USER_bpapp` + strong password
3. Add user → DB → **ALL PRIVILEGES**
4. Keep the 3 values for `.env`.

## 2. PHP — cPanel → MultiPHP Manager
Set **beyondpassports.co.uk → PHP 8.2** (or 8.3). Ensure extensions on (MultiPHP INI / Extensions):
`mbstring, pdo_mysql, bcmath, curl, gd, zip, openssl, fileinfo`.

## 3. Get a GitHub token (private repo)
GitHub → Settings → Developer settings → **Fine-grained tokens** → new token, **Repository access = only `usama658/beyondpassports`**, **Contents: Read-only**. Copy it (starts `github_pat_…`).

## 4. Clone — cPanel → Git™ Version Control → Create
- **Clone URL:** `https://github_pat_TOKEN@github.com/usama658/beyondpassports.git`
- **Repository Path:** `/home/USER/beyondpassports`
- **Branch:** `master`
(Or clone in Terminal: `git clone https://github_pat_TOKEN@github.com/usama658/beyondpassports.git ~/beyondpassports`)

## 5. App setup — Terminal
```bash
cd ~/beyondpassports
composer install --no-dev --optimize-autoloader
# if composer runs out of memory:
#   php -d memory_limit=-1 "$(command -v composer)" install --no-dev --optimize-autoloader

cp .env.production.example .env
php artisan key:generate
nano .env
```
Set these in `.env`:
```
APP_ENV=production
APP_DEBUG=false
APP_NAME="Beyond Passports"
APP_URL=https://beyondpassports.co.uk

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=USER_bp
DB_USERNAME=USER_bpapp
DB_PASSWORD=********

QUEUE_CONNECTION=sync
MAIL_MAILER=log

UKV_BASE_URL=https://beyondpassports.co.uk
UKV_FRONTEND_ORIGIN=https://beyondpassports.co.uk
UKV_OWNER_EMAIL=you@beyondpassports.co.uk

# TEST mode — rehearse only
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_test_xxx
```
Then:
```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache route:cache view:cache
php artisan filament:optimize
chmod -R 775 storage bootstrap/cache
php artisan tinker --execute "App\Models\User::create(['name'=>'Owner','email'=>'you@beyondpassports.co.uk','password'=>bcrypt('STRONGPASS'),'role'=>'admin']);"
```

## 6. Serve apex from /public  (symlink — non-destructive, keeps a backup)
The Laravel public dir must be the web root. On A2 the apex root is `~/public_html`.
```bash
cd ~
mv public_html public_html_backup_$(date +%s)     # backs up whatever's there
ln -s ~/beyondpassports/public public_html        # apex now serves the app
```
Check `~/beyondpassports/public/.htaccess` exists (it does). Visit the site.
**If A2 won't follow a symlinked docroot** (blank page / 403), use the copy-method fallback instead:
```bash
rm public_html                                     # remove the symlink
mkdir ~/public_html
cp -r ~/beyondpassports/public/. ~/public_html/
# edit ~/public_html/index.php: change the two require paths from __DIR__.'/../'
# to '/home/USER/beyondpassports/' (vendor/autoload.php and bootstrap/app.php)
```
(Symlink is preferred — updates are automatic. Copy-method needs re-copying `public/` after each deploy.)

## 7. HTTPS — cPanel → SSL/TLS Status
After DNS resolves, run **AutoSSL** for beyondpassports.co.uk (+ www). App forces HTTPS once `APP_URL` is `https`.

## 8. Cron — cPanel → Cron Jobs (every minute)
```
* * * * * cd /home/USER/beyondpassports && /usr/local/bin/php artisan schedule:run >/dev/null 2>&1
```
(`which php` to confirm the binary; or the `ea-php82` path from MultiPHP.)

## 9. Smoke test
`/` → `/tools` → `/guides` → `/compare` → `/find-a-centre` → `/apply` (Stripe test card `4242 4242 4242 4242`) → confirmation → `/track`. `/admin` → owner login.

## 10. Update later
cPanel → Git → **Pull** (or `git pull` in Terminal), then:
```bash
cd ~/beyondpassports && composer install --no-dev --optimize-autoloader \
 && php artisan migrate --force && php artisan config:cache route:cache view:cache
```

---

### Before REAL payments (do NOT skip — DEPLOY.md §7)
- [ ] Verify each destination's fees / processing / passport-validity vs gov.uk (#129) — seeded = placeholders.
- [ ] Live Stripe keys + webhook `https://beyondpassports.co.uk/stripe/webhook` (#98).
- [ ] Rotate HubSpot token (#181); Filament 2FA on (#197).
- [ ] ICO data-protection fee registered (#215).
