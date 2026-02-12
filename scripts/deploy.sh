#!/usr/bin/env bash
set -euo pipefail

# Robust deploy script for sgmrtexas
# Usage: sudo bash scripts/deploy.sh [branch]
# Example: sudo bash scripts/deploy.sh feature/alerts-audit-ui

BRANCH="${1:-main}"
APP_DIR="${APP_DIR:-/var/www/sgmrtexas}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.4-fpm}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-0}"

echo "[deploy] target dir: $APP_DIR"
echo "[deploy] branch: $BRANCH"

if [ ! -d "$APP_DIR" ]; then
  echo "ERROR: $APP_DIR does not exist"
  exit 1
fi

cd "$APP_DIR"

echo "[deploy] fetching and checking out $BRANCH"
git fetch --all --prune
git checkout "$BRANCH"
git pull origin "$BRANCH"

echo "[deploy] composer install (no-dev)"
composer install --no-dev --optimize-autoloader

if command -v npm >/dev/null 2>&1; then
  echo "[deploy] npm detected — installing and building assets"
  if [ -f package-lock.json ]; then
    npm ci
  else
    npm install
  fi
  npm run build
else
  echo "[deploy] npm not found — skipping frontend build"
fi

echo "[deploy] storage link (best-effort)"
php artisan storage:link || true

if [ "$RUN_MIGRATIONS" = "1" ]; then
  echo "[deploy] running migrations"
  php artisan migrate --force
fi

echo "[deploy] clearing and rebuilding caches"
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[deploy] fixing permissions"
sudo chown -R www-data:www-data storage bootstrap/cache || true
sudo chmod -R 775 storage bootstrap/cache || true

echo "[deploy] restarting php-fpm and reloading nginx"
sudo systemctl restart "$PHP_FPM_SERVICE" 2>/dev/null || sudo systemctl restart php8.2-fpm 2>/dev/null || true
sudo systemctl reload nginx 2>/dev/null || true

echo "[deploy] finished"

exit 0
