#!/usr/bin/env bash
set -euo pipefail

# Deploy helper: refresh Laravel caches and restart services
# Usage: sudo ./scripts/deploy-refresh.sh

ROOT_DIR="/var/www/sgmrtexas"

if [ ! -d "$ROOT_DIR" ]; then
  echo "Error: project directory $ROOT_DIR not found"
  exit 1
fi

cd "$ROOT_DIR"

echo "Running artisan cache/optimize commands..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize || true

echo "Restarting PHP-FPM and reloading Nginx..."
sudo systemctl restart php8.4-fpm
sudo systemctl reload nginx

echo "Done. If the script is not executable, run: chmod +x scripts/deploy-refresh.sh"
