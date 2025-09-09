#!/bin/bash
set -e

echo "==> Déploiement démarré"

# Se placer dans le dossier du projet
cd "$(dirname "$0")"

# Vérifier si .env existe, sinon copier depuis .env.example
if [ ! -f .env ]; then
    echo "⚠️  .env introuvable. Copie de .env.example vers .env"
    cp .env.example .env
    echo "✅ .env créé"
fi

# Mettre à jour le code depuis Git
echo "==> Récupération du code depuis Git"
git fetch origin
git reset --hard origin/main

# Installer les dépendances Composer
echo "==> Installation des dépendances"
composer install --no-interaction --prefer-dist --optimize-autoloader

# Créer les dossiers nécessaires et régler les permissions
echo "==> Préparation des dossiers"
mkdir -p storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Créer le fichier de log si nécessaire
if [ ! -f storage/logs/laravel.log ]; then
    touch storage/logs/laravel.log
    chown www-data:www-data storage/logs/laravel.log
    chmod 664 storage/logs/laravel.log
fi

# Nettoyer et mettre en cache la configuration Laravel
echo "==> Gestion du cache Laravel"
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:cache

# Exécuter les migrations
echo "==> Exécution des migrations"
sudo -u www-data php artisan migrate --force

# Redémarrer Apache (ou autre service si nécessaire)
echo "==> Redémarrage du serveur web"
sudo systemctl restart apache2 || echo "⚠️ Impossible de redémarrer apache2 automatiquement"

echo "==> Déploiement terminé avec succès"
