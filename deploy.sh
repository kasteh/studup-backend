#!/bin/bash
set -e

echo "==> Déploiement démarré"

cd /var/www || exit 1

# Supprimer l'ancien code et récupérer la dernière version
rm -rf studup-backend
git clone https://github.com/kasteh/studup-backend.git
cd studup-backend || exit 1

# Créer .env si absent
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ .env créé"
fi

# Créer les dossiers nécessaires avec permissions modifiables par tout le monde
mkdir -p bootstrap/cache storage/logs
chmod -R 777 bootstrap/cache storage

# Installer les dépendances Composer
composer install --no-interaction --prefer-dist --optimize-autoloader

# Nettoyer et mettre en cache Laravel
php artisan config:clear || true
php artisan cache:clear || true
php artisan config:cache || true

# Exécuter les migrations
php artisan migrate --force

echo "==> Déploiement terminé"
