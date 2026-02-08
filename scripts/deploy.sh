#!/usr/bin/env bash
set -euo pipefail

# Simple deploy script for /var/www/sgmrtexas
# Usage:
#   sudo bash scripts/deploy.sh
# Optional env overrides:
#   APP_DIR (default /var/www/sgmrtexas)
#   BRANCH (default origin/main)
#   PHP_FPM_SERVICE (default php8.2-fpm)
#   RUN_MIGRATIONS=1  (to run migrations)

APP_DIR="${APP_DIR:-/var/www/sgmrtexas}"
BRANCH="${BRANCH:-origin/main}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.2-fpm}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-0}"

echo "--> Deploy script started"
echo "    APP_DIR=$APP_DIR"
echo "    BRANCH=$BRANCH"

if [ ! -d "$APP_DIR" ]; then
  echo "ERROR: directory $APP_DIR not found"
  exit 1
fi

cd "$APP_DIR"

echo "--> Fetching and resetting to $BRANCH"
git fetch --all --prune
git reset --hard "$BRANCH"

echo "--> Installing PHP dependencies (composer)"
composer install --no-dev --optimize-autoloader

echo "--> Installing front-end dependencies and building assets"
npm ci
npm run build

if [ "$RUN_MIGRATIONS" = "1" ]; then
  echo "--> Running migrations (force)"
  php artisan migrate --force
fi

echo "--> Clearing and rebuilding caches"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "--> Fixing permissions"
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo "--> Restarting services"
sudo systemctl restart "$PHP_FPM_SERVICE"
sudo systemctl reload nginx

echo "--> Deploy finished"

exit 0
