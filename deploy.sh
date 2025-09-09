#!/bin/bash
set -e

echo "==> Déploiement démarré"

# Se placer dans le dossier du projet
cd "$(dirname "$0")"

# Mettre à jour le code depuis Git
echo "==> Récupération du code depuis Git"
git fetch origin
git reset --hard origin/main

# Vérifier si .env existe, sinon copier depuis .env.example
if [ ! -f .env ]; then
    echo "⚠️  .env introuvable. Copie de .env.example vers .env"
    cp .env.example .env
    echo "✅ .env créé"
fi

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
sudo -u www-data php artisan config:clear || true
sudo -u www-data php artisan cache:clear || true
sudo -u www-data php artisan config:cache || true

# Exécuter les migrations
echo "==> Exécution des migrations"
sudo -u www-data php artisan migrate --force

echo "==> Redémarrage du serveur web"
if systemctl is-active --quiet apache2; then
    sudo systemctl restart apache2
elif systemctl is-active --quiet nginx; then
    sudo systemctl restart nginx
else
    echo "⚠️ Aucun serveur web détecté à redémarrer automatiquement"
fi

echo "==> Déploiement terminé avec succès"