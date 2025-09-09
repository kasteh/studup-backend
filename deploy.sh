#!/usr/bin/env bash
set -e  # stoppe le script si une commande échoue

APP_DIR="/var/www/studup-backend"
BRANCH="main"

echo "==> Deployment started at $(date)"

cd $APP_DIR

echo "==> Fetching latest code"
git fetch origin $BRANCH
git reset --hard origin/$BRANCH

echo "==> Installing dependencies"
composer install --no-dev --optimize-autoloader

echo "==> Setting permissions"
sudo chown -R www-data:www-data $APP_DIR/storage $APP_DIR/bootstrap/cache
sudo chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache

echo "==> Running migrations"
php artisan migrate --force

echo "==> Caching config, routes, views"
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache || true

echo "==> Reloading Apache"
sudo systemctl reload apache2

echo "✅ Deployment finished at $(date)"
