#!/usr/bin/env bash
# Convenience script to pull the feature branch on a server or local clone.
# Usage: cd /var/www/sgmrtexas && sudo -u www-data bash scripts/pull-feature.sh

set -euo pipefail

BRANCH="feature/alerts-audit-ui"
REMOTE="origin"

echo "Fetching all..."
git fetch --all --prune

echo "Checking out ${BRANCH}..."
git checkout ${BRANCH}

echo "Pulling ${REMOTE}/${BRANCH}..."
git pull ${REMOTE} ${BRANCH}

echo "Pull complete. If you need to build assets or run migrations, run the build steps manually." 
echo "Recommended next steps:"
echo "  composer install --no-dev --optimize-autoloader"
echo "  npm ci && npm run build"
echo "  php artisan migrate --force (only if needed)"
echo "  php artisan storage:link"
echo "  php artisan config:cache && php artisan route:cache && php artisan view:cache"
echo "  sudo systemctl restart php8.4-fpm && sudo systemctl reload nginx"

exit 0
