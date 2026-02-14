#!/usr/bin/env bash
set -euo pipefail

# Usage:
#   ./deploy.sh <ssh_user@host> [branch]
# Example:
#   ./deploy.sh usuario@servidor.example.com restore/clientes-css-to-06661a3

REMOTE="${1:-usuario@SEU_SERVIDOR}"
BRANCH="${2:-restore/clientes-css-to-06661a3}"
APP_DIR="/var/www/sgmrtexas"

echo "Iniciando deploy para ${REMOTE} (branch: ${BRANCH})..."

ssh "${REMOTE}" bash -s <<'EOF'
set -euo pipefail
APP_DIR="/var/www/sgmrtexas"
BRANCH="${BRANCH}"

cd "${APP_DIR}"
git fetch origin
git checkout "${BRANCH}"
git reset --hard "origin/${BRANCH}"

# PHP deps
composer install --no-dev --optimize-autoloader

# Frontend
npm install
npm run build

# Optional (only if needed in your workflow):
# php artisan key:generate --force
# php artisan migrate --force
# php artisan storage:link

php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

sudo chown -R www-data:www-data storage bootstrap/cache || true
sudo chown -R $USER:www-data "${APP_DIR}" || true

sudo systemctl restart php8.4-fpm || true
sudo systemctl reload nginx || true

echo "Deploy concluído."
EOF

echo "deploy.sh finalizado localmente. Lembre-se de tornar executável:"
echo "  chmod +x deploy.sh"
echo "Uso: ./deploy.sh usuario@seu_servidor [branch]"
