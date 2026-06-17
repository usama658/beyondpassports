#!/usr/bin/env bash
# Beyond Passports — A2 update (runs on every push via GitHub Actions).
# Assumes first deploy already done by deploy-a2.sh (repo cloned, .env set, docroot symlinked).
# No secrets needed: git remote already carries the token; .env already on disk.
#
# Override the PHP binary if `php` is the wrong version on A2:
#   PHP=/usr/local/bin/ea-php82 bash update-a2.sh
set -euo pipefail
APP_DIR="${APP_DIR:-$HOME/beyondpassports}"
PHP="${PHP:-php}"
cd "$APP_DIR"

echo "== Sync to origin/master"
git fetch --depth 1 origin master
git reset --hard origin/master

echo "== Composer (no-dev)"
COMPOSER_BIN="$(command -v composer || true)"
if [ -n "$COMPOSER_BIN" ]; then
  "$PHP" -d memory_limit=-1 "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction
else
  [ -f composer.phar ] || curl -sS https://getcomposer.org/installer | "$PHP"
  "$PHP" -d memory_limit=-1 composer.phar install --no-dev --optimize-autoloader --no-interaction
fi

echo "== Migrate + re-cache"
"$PHP" artisan migrate --force
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:cache
"$PHP" artisan filament:optimize || true
chmod -R 775 storage bootstrap/cache || true

echo "== Updated to $(git rev-parse --short HEAD)"
