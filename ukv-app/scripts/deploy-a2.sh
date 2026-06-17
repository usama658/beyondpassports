#!/usr/bin/env bash
# Beyond Passports — one-shot A2 staging deploy (test mode).
# Run in cPanel → Terminal (or SSH). Idempotent: safe to re-run for updates.
#
# REQUIRED env vars (pass on the command line — do NOT hardcode secrets):
#   GHTOKEN   GitHub fine-grained read-only token for usama658/beyondpassports
#   DBNAME    cPanel DB name      (create first in cPanel → MySQL Databases)
#   DBUSER    cPanel DB user
#   DBPASS    DB password         (avoid | and & to keep sed happy)
#   ADMINPASS password for the first admin (Owner) login
# OPTIONAL:
#   DOMAIN    default beyondpassports.co.uk
#   ADMINEMAIL default you@<domain>
#   STRIPE_KEY / STRIPE_SECRET / STRIPE_WEBHOOK_SECRET  (test keys; omit to fill later)
#   APP_DIR   default $HOME/beyondpassports
#   PHP       php binary (default: php)
#
# Example:
#   GHTOKEN=github_pat_xxx DBNAME=usr_bp DBUSER=usr_bpapp DBPASS='s3cret' \
#   ADMINPASS='StrongPass1' bash deploy-a2.sh
set -euo pipefail

DOMAIN="${DOMAIN:-beyondpassports.co.uk}"
APP_DIR="${APP_DIR:-$HOME/beyondpassports}"
ADMINEMAIL="${ADMINEMAIL:-you@${DOMAIN}}"
PHP="${PHP:-php}"
REPO="https://${GHTOKEN:?set GHTOKEN}@github.com/usama658/beyondpassports.git"

say(){ printf '\n\033[1;36m== %s\033[0m\n' "$*"; }

say "1/8  Clone or update repo  ($APP_DIR)"
if [ -d "$APP_DIR/.git" ]; then
  git -C "$APP_DIR" remote set-url origin "$REPO"
  git -C "$APP_DIR" fetch --depth 1 origin master
  git -C "$APP_DIR" reset --hard origin/master
else
  git clone --depth 1 -b master "$REPO" "$APP_DIR"
fi
cd "$APP_DIR"

say "2/8  Composer install (no-dev)"
COMPOSER_BIN="$(command -v composer || true)"
if [ -n "$COMPOSER_BIN" ]; then
  "$PHP" -d memory_limit=-1 "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction
else
  [ -f composer.phar ] || curl -sS https://getcomposer.org/installer | "$PHP"
  "$PHP" -d memory_limit=-1 composer.phar install --no-dev --optimize-autoloader --no-interaction
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

say "4/8  Migrate + seed"
"$PHP" artisan migrate --force
"$PHP" artisan db:seed --force

say "5/8  Storage link + caches"
"$PHP" artisan storage:link || true
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:cache
"$PHP" artisan filament:optimize || true
chmod -R 775 storage bootstrap/cache || true

say "6/8  Admin user (idempotent)"
"$PHP" artisan tinker --execute "\$e='${ADMINEMAIL}'; \App\Models\User::firstOrCreate(['email'=>\$e],['name'=>'Owner','password'=>bcrypt('${ADMINPASS:?set ADMINPASS}'),'role'=>'admin']);" || true

say "7/8  Point the ADDON-domain docroot at public/ (symlink, with backup)"
# beyondpassports.co.uk is an ADDON domain — its docroot is separate from public_html
# (public_html belongs to the MAIN domain). NEVER touch public_html here.
DOCROOT="${DOCROOT:-$HOME/${DOMAIN}}"     # e.g. /home/outlabio/beyondpassports.co.uk
TARGET="$APP_DIR/public"
case "$DOCROOT" in
  *public_html|*public_html/) echo "REFUSING: DOCROOT looks like the main public_html ($DOCROOT). Set DOCROOT to the addon dir."; exit 1;;
esac
if [ -L "$DOCROOT" ]; then
  ln -sfn "$TARGET" "$DOCROOT"
elif [ -d "$DOCROOT" ]; then
  mv "$DOCROOT" "${DOCROOT}_backup_$(date +%s)"
  ln -s "$TARGET" "$DOCROOT"
else
  ln -s "$TARGET" "$DOCROOT"
fi
echo "Docroot $DOCROOT -> $TARGET"

say "8/8  Done"
echo "Visit: https://${DOMAIN}    Admin: https://${DOMAIN}/admin  ($ADMINEMAIL)"
echo "If the apex shows blank/403 (symlinked docroot blocked), use the copy-method in DEPLOY-A2-STAGING.md §6."
echo "Still to do in cPanel: AutoSSL (SSL/TLS Status) + cron (schedule:run, see runbook §8)."
