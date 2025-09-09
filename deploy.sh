#!/bin/bash
set -e

echo "==> Déploiement démarré"

cd /var/www/studup-backend || exit 1

# Récupérer le code depuis Git
git fetch origin
git reset --hard origin/main

# Vérifier si .env existe
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ .env créé"
fi

# Créer les dossiers nécessaires AVANT composer install
mkdir -p bootstrap/cache storage/logs
chown -R www-data:www-data bootstrap/cache storage
chmod -R 775 bootstrap/cache storage

# Installer les dépendances Composer
composer install --no-interaction --prefer-dist --optimize-autoloader

# Nettoyer et mettre en cache Laravel
sudo -u www-data php artisan config:clear || true
sudo -u www-data php artisan cache:clear || true
sudo -u www-data php artisan config:cache || true

# Exécuter les migrations
sudo -u www-data php artisan migrate --force

# Redémarrer le serveur web
if systemctl is-active --quiet apache2; then
    sudo systemctl restart apache2
elif systemctl is-active --quiet nginx; then
    sudo systemctl restart nginx
fi

echo "==> Déploiement terminé"
