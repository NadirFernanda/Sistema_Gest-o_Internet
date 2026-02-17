#!/usr/bin/env bash
set -euo pipefail

# Safe deploy script for the project.
# Usage: ./deploy.sh [--yes] [--branch main] [--remote origin]
# By default asks for confirmation. Use --yes to run non-interactively.

ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT_DIR"

BRANCH="main"
REMOTE="origin"
ASSUME_YES=0

while [[ $# -gt 0 ]]; do
  case "$1" in
    --yes) ASSUME_YES=1; shift ;;
    --branch) BRANCH="$2"; shift 2 ;;
    --remote) REMOTE="$2"; shift 2 ;;
    -h|--help)
      sed -n '1,200p' "$0" ; exit 0 ;;
    *) echo "Unknown option: $1"; exit 2 ;;
  esac
done

echo "Deploy script starting in: $ROOT_DIR"
echo "Remote: $REMOTE  Branch: $BRANCH"

if [[ $ASSUME_YES -ne 1 ]]; then
  read -p "Proceed with deploy? [y/N]: " yn
  case "$yn" in
    [Yy]*) ;;
    *) echo "Abort."; exit 0 ;;
  esac
fi

echo "Fetching latest from $REMOTE/$BRANCH"
git fetch "$REMOTE" --prune
git checkout "$BRANCH"
git reset --hard "$REMOTE/$BRANCH"

echo "Installing PHP dependencies (composer)"
if command -v composer >/dev/null 2>&1; then
  composer install --no-dev --optimize-autoloader
else
  echo "composer not found in PATH. Please install composer or run this as the deploy user.";
  exit 1
fi

echo "Installing front-end dependencies and building assets"
if command -v npm >/dev/null 2>&1; then
  npm ci
  npm run build
else
  echo "npm not found in PATH. Skipping front-end build.";
fi

echo "Running artisan tasks"
php artisan view:clear || true
php artisan migrate --force || true

# Ensure storage link exists
if [[ ! -L public/storage ]]; then
  php artisan storage:link || true
fi

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Adjusting permissions (may ask for sudo)"
sudo chown -R www-data:www-data storage bootstrap/cache || true
sudo chmod -R 775 storage bootstrap/cache || true

# Restart appropriate php-fpm service (detect installed version)
PHP_FPM_SERVICE=""
if systemctl list-units --type=service --no-legend 'php*-fpm.service' | grep -q 'php'; then
  PHP_FPM_SERVICE=$(systemctl list-units --type=service --no-legend 'php*-fpm.service' | awk '{print $1; exit}')
fi

if [[ -n "$PHP_FPM_SERVICE" ]]; then
  echo "Restarting $PHP_FPM_SERVICE"
  sudo systemctl restart "$PHP_FPM_SERVICE"
else
  echo "No php-fpm service found via systemctl; skipping php-fpm restart. If you need restart, run: sudo systemctl restart php8.4-fpm"
fi

echo "Reloading nginx"
sudo systemctl reload nginx || true

echo "Deploy finished. Check logs and status if anything failed." 
