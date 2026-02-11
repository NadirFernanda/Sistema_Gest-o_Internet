#!/usr/bin/env bash
set -euo pipefail

# Usage: ./scripts/deploy.sh [branch]
# Example: ./scripts/deploy.sh main

BRANCH=${1:-main}
APP_DIR="/var/www/sgmrtexas"

echo "Deploying branch '$BRANCH' into $APP_DIR"

cd "$APP_DIR"

echo "Fetching latest..."
git fetch --all --prune
git checkout "$BRANCH"
git pull origin "$BRANCH"

echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "Installing/building front-end assets..."
if [ -f package-lock.json ] || [ -f pnpm-lock.yaml ]; then
  npm ci
else
  npm install
fi
npm run build

echo "Running migrations..."
php artisan migrate --force

echo "Linking storage (if needed)..."
php artisan storage:link || true

echo "Caching config, routes and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Setting permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo "Restarting services..."
sudo systemctl restart php8.4-fpm
sudo systemctl reload nginx

echo "Deploy finished successfully."
#!/usr/bin/env bash
set -euo pipefail

# Deploy script for sgmrtexas
# Usage: ./scripts/deploy.sh [branch]
# Example: ./scripts/deploy.sh main

BRANCH="${1:-main}"
PROJECT_ROOT="$(cd "$(dirname "$0")/.." && pwd)"

echo "[deploy] project root: $PROJECT_ROOT"
cd "$PROJECT_ROOT"

echo "[deploy] fetching from origin..."
git fetch origin --prune

echo "[deploy] checking out branch $BRANCH"
git checkout "$BRANCH"
git pull origin "$BRANCH"

echo "[deploy] installing PHP dependencies (no-dev)..."
composer install --no-dev --optimize-autoloader

if command -v npm >/dev/null 2>&1; then
  echo "[deploy] npm found — installing node deps and building assets"
  # prefer CI for reproducible installs; falls back to npm install if package-lock.json missing
  if [ -f package-lock.json ]; then
    npm ci
  else
    npm install
  fi
  npm run build
else
  echo "[deploy] npm not found — skipping frontend build (you may copy public/build manually)"
fi

echo "[deploy] ensure storage link"
php artisan storage:link || true

echo "[deploy] (optional) apply migrations (set APPLY_MIGRATIONS=1 to enable)"
if [ "${APPLY_MIGRATIONS:-0}" = "1" ]; then
  php artisan migrate --force
fi

echo "[deploy] clearing and rebuilding caches"
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[deploy] fix ownership of runtime dirs"
sudo chown -R www-data:www-data storage bootstrap/cache || true

echo "[deploy] restart PHP-FPM and reload webserver (best-effort)"
sudo systemctl restart php8.4-fpm 2>/dev/null || sudo systemctl restart php8.2-fpm 2>/dev/null || true
sudo systemctl reload nginx 2>/dev/null || true

echo "[deploy] done"
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
