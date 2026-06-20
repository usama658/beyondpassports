#!/usr/bin/env bash
# Beyond Passports — A2 update (runs on every push via GitHub Actions). Layout-aware.
# Assumes first deploy already done (repo cloned, .env set, docroot symlinked).
#   PHP=/usr/local/bin/ea-php82 bash update-a2.sh   # override php if needed
set -euo pipefail
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"   # …/ukv-app
# PHP 8.2 detection: honour $PHP, else A2 CloudLinux alt-php, else PATH php
PHP="${PHP:-}"
[ -x "$PHP" ] || PHP=/opt/alt/php82/usr/bin/php
[ -x "$PHP" ] || PHP="$(command -v php)"
cd "$APP_DIR"

echo "== Sync repo to origin/master"
REPO_TOP="$(git rev-parse --show-toplevel)"
git -C "$REPO_TOP" fetch --depth 1 origin master
git -C "$REPO_TOP" reset --hard origin/master

echo "== Composer (no-dev)"
COMPOSER_BIN="$(command -v composer || true)"
[ -n "$COMPOSER_BIN" ] || { curl -sS https://getcomposer.org/installer | "$PHP"; COMPOSER_BIN="composer.phar"; }
composer_install(){ "$PHP" -d memory_limit=-1 "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction "$@"; }
composer_install || { echo "retry ignoring ext-zip"; composer_install --ignore-platform-req=ext-zip; }

echo "== Migrate"
"$PHP" artisan migrate --force

echo "== Seed production reference data (idempotent)"
"$PHP" artisan db:seed --class=ProductionSeeder --force

echo "== Re-cache"
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:cache
"$PHP" artisan filament:optimize || true
chmod -R 775 storage bootstrap/cache || true
echo "== Updated to $(git -C "$REPO_TOP" rev-parse --short HEAD)"
