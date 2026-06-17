#!/usr/bin/env bash
# Beyond Passports — one-shot A2 staging deploy (test mode). Layout-aware:
# the Laravel app is the dir that contains this script's parent (…/ukv-app);
# the git repo root may be one level up (monorepo). Run from anywhere.
#
# REQUIRED env vars:
#   DBNAME DBUSER DBPASS      cPanel MySQL (already created: outlabio_bp / outlabio_bpapp)
#   ADMINPASS                 password for the first admin (Owner) login
# OPTIONAL:
#   DOMAIN     default beyondpassports.co.uk
#   DOCROOT    default /home/<you>/<DOMAIN>     (the ADDON-domain docroot — never public_html)
#   ADMINEMAIL default you@<DOMAIN>
#   GHTOKEN    if set, refreshes the git remote URL so pulls work (private repo)
#   STRIPE_KEY / STRIPE_SECRET / STRIPE_WEBHOOK_SECRET   (test keys; omit to fill later)
#   PHP        php binary (default: php)
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"   # …/ukv-app
PHP="${PHP:-php}"
DOMAIN="${DOMAIN:-beyondpassports.co.uk}"
ADMINEMAIL="${ADMINEMAIL:-you@${DOMAIN}}"
DOCROOT="${DOCROOT:-$HOME/${DOMAIN}}"
say(){ printf '\n\033[1;36m== %s\033[0m\n' "$*"; }
cd "$APP_DIR"
[ -f composer.json ] || { echo "composer.json not found in $APP_DIR — wrong location"; exit 1; }

say "0/8  App dir: $APP_DIR"

say "1/8  Update repo to latest master"
REPO_TOP="$(git rev-parse --show-toplevel 2>/dev/null || true)"
if [ -n "$REPO_TOP" ]; then
  [ -n "${GHTOKEN:-}" ] && git -C "$REPO_TOP" remote set-url origin "https://${GHTOKEN}@github.com/usama658/beyondpassports.git" || true
  git -C "$REPO_TOP" fetch --depth 1 origin master && git -C "$REPO_TOP" reset --hard origin/master
else
  echo "(not a git checkout — skipping update)"
fi

say "2/8  Composer install (no-dev)"
COMPOSER_BIN="$(command -v composer || true)"
[ -n "$COMPOSER_BIN" ] || { curl -sS https://getcomposer.org/installer | "$PHP"; COMPOSER_BIN="composer.phar"; }
composer_install(){ "$PHP" -d memory_limit=-1 "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction "$@"; }
# Some A2 PHP CLIs ship without ext-zip; retry ignoring it so deploy isn't blocked.
# (Excel/xlsx exports need zip — enable it in cPanel → Select PHP Version for full function.)
if ! composer_install; then
  echo "Composer failed (likely ext-zip). Retrying with --ignore-platform-req=ext-zip ..."
  composer_install --ignore-platform-req=ext-zip
fi

say "3/8  Write .env"
[ -f .env ] || cp .env.production.example .env
set_env(){ local k="$1" v="$2"
  if grep -q "^${k}=" .env; then sed -i "s|^${k}=.*|${k}=${v}|" .env
  else printf '%s=%s\n' "$k" "$v" >> .env; fi; }
set_env APP_ENV production
set_env APP_DEBUG false
set_env APP_NAME '"Beyond Passports"'
set_env APP_URL "https://${DOMAIN}"
set_env DB_CONNECTION mysql
set_env DB_HOST 127.0.0.1
set_env DB_DATABASE "${DBNAME:?set DBNAME}"
set_env DB_USERNAME "${DBUSER:?set DBUSER}"
set_env DB_PASSWORD "${DBPASS:?set DBPASS}"
set_env QUEUE_CONNECTION sync
set_env MAIL_MAILER log
set_env UKV_BASE_URL "https://${DOMAIN}"
set_env UKV_FRONTEND_ORIGIN "https://${DOMAIN}"
set_env UKV_OWNER_EMAIL "${ADMINEMAIL}"
[ -n "${STRIPE_KEY:-}" ]            && set_env STRIPE_KEY "${STRIPE_KEY}"            || true
[ -n "${STRIPE_SECRET:-}" ]         && set_env STRIPE_SECRET "${STRIPE_SECRET}"         || true
[ -n "${STRIPE_WEBHOOK_SECRET:-}" ] && set_env STRIPE_WEBHOOK_SECRET "${STRIPE_WEBHOOK_SECRET}" || true
grep -q "^APP_KEY=base64" .env || "$PHP" artisan key:generate --force

say "4/8  Migrate + seed (real data only — no demo, no faker/dev deps)"
"$PHP" artisan migrate --force
for S in DestinationSeeder DocumentRequirementSeeder SupplyNodeSeeder TurkeyGoldGuidesSeeder; do
  echo "  seeding $S"
  "$PHP" artisan db:seed --class="$S" --force || echo "  ($S skipped/failed — continuing)"
done

say "5/8  Storage link + caches"
"$PHP" artisan storage:link || true
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:cache
"$PHP" artisan filament:optimize || true
chmod -R 775 storage bootstrap/cache || true

say "6/8  Admin user (idempotent)"
"$PHP" artisan tinker --execute "\$e='${ADMINEMAIL}'; \App\Models\User::firstOrCreate(['email'=>\$e],['name'=>'Owner','password'=>bcrypt('${ADMINPASS:?set ADMINPASS}'),'role'=>'admin']);" || true

say "7/8  Point ADDON docroot at public/ (symlink, with backup) — never public_html"
case "$DOCROOT" in
  *public_html|*public_html/) echo "REFUSING: DOCROOT looks like main public_html ($DOCROOT)"; exit 1;;
esac
TARGET="$APP_DIR/public"
if [ -L "$DOCROOT" ]; then ln -sfn "$TARGET" "$DOCROOT"
elif [ -d "$DOCROOT" ]; then mv "$DOCROOT" "${DOCROOT}_backup_$(date +%s)"; ln -s "$TARGET" "$DOCROOT"
else ln -s "$TARGET" "$DOCROOT"; fi
echo "Docroot $DOCROOT -> $TARGET"

say "8/8  Done"
echo "Visit: https://${DOMAIN}    Admin: https://${DOMAIN}/admin  ($ADMINEMAIL)"
echo "Next in cPanel: AutoSSL + cron (cd $APP_DIR && $PHP artisan schedule:run)."
echo "If apex blank/403 (symlinked docroot blocked), use the copy-method in DEPLOY-A2-STAGING.md §6."
