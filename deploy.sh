#!/bin/bash
set -e

echo "==> 🚀 DÉMARRAGE DU DÉPLOIEMENT STUDUP"
echo "=========================================="

# Étape 1: Aller dans le dossier de l'application
cd /var/www/studup-backend
echo "📁 Dossier: $(pwd)"

# Étape 2: Donner les permissions temporaires pour l'installation
echo "🔧 Configuration des permissions temporaires..."
sudo chown -R ubuntu:ubuntu /var/www/studup-backend
sudo chmod -R 755 /var/www/studup-backend

# Étape 3: Configuration de l'environnement
echo "🔧 CONFIGURATION ENVIRONNEMENT"
if [ ! -f .env ]; then
    echo "📄 Création du fichier .env..."
    cp .env.example .env
    echo "✅ .env créé à partir de .env.example"
    echo "⚠️  IMPORTANT: Configurez les variables dans .env !"
else
    echo "✅ .env existe déjà"
fi

# Étape 4: Création de la structure des dossiers
echo "📁 CRÉATION DES DOSSIERS LARAVEL"
mkdir -p storage/app/public
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p vendor
echo "✅ Structure des dossiers créée"

# Étape 5: Installation des dépendances Composer (avec permissions utilisateur)
echo "📦 INSTALLATION DES DÉPENDANCES COMPOSER"
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
echo "✅ Dépendances Composer installées"

# Étape 6: Configuration des permissions POUR APACHE
echo "🔐 CONFIGURATION DES PERMISSIONS APACHE"
sudo chown -R www-data:www-data /var/www/studup-backend
sudo chmod -R 755 /var/www/studup-backend
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R 777 storage/framework/cache storage/logs

# Donner les permissions d'écriture à Apache sur les fichiers spécifiques
sudo touch storage/logs/laravel.log
sudo chown www-data:www-data storage/logs/laravel.log
sudo chmod 666 storage/logs/laravel.log

sudo touch bootstrap/cache/config.php
sudo chown www-data:www-data bootstrap/cache/config.php
sudo chmod 666 bootstrap/cache/config.php

sudo touch bootstrap/cache/packages.php
sudo chown www-data:www-data bootstrap/cache/packages.php
sudo chmod 666 bootstrap/cache/packages.php

sudo touch bootstrap/cache/services.php
sudo chown www-data:www-data bootstrap/cache/services.php
sudo chmod 666 bootstrap/cache/services.php

echo "✅ Permissions Apache configurées"

# Étape 7: Configuration de l'application Laravel
echo "⚙️  CONFIGURATION LARAVEL"

# Génération de la clé API
if ! grep -q "APP_KEY=base64" .env; then
    sudo -u www-data php artisan key:generate --force
    echo "✅ Clé API générée"
else
    echo "✅ Clé API déjà configurée"
fi

# Configuration de l'URL
if ! grep -q "APP_URL=" .env; then
    echo "APP_URL=https://vps-d91fd27c.vps.ovh.net" >> .env
    echo "✅ APP_URL configuré"
fi

# Étape 8: Nettoyage des caches (en tant qu'Apache)
echo "🧹 NETTOYAGE DES CACHES"
sudo -u www-data php artisan config:clear || true
sudo -u www-data php artisan cache:clear || true
sudo -u www-data php artisan view:clear || true
sudo -u www-data php artisan route:clear || true
echo "✅ Caches nettoyés"

# Étape 9: Optimisation production (en tant qu'Apache)
echo "⚡ OPTIMISATION PRODUCTION"
sudo -u www-data php artisan config:cache || true
sudo -u www-data php artisan route:cache || true
sudo -u www-data php artisan view:cache || true
echo "✅ Application optimisée"

# Étape 10: Migrations base de données (en tant qu'Apache)
echo "🗄️  MIGRATIONS BASE DE DONNÉES"
sudo -u www-data php artisan migrate --force
echo "✅ Migrations exécutées"

# Étape 11: Lien de stockage (en tant qu'Apache)
echo "🔗 LIEN DE STOCKAGE"
sudo -u www-data php artisan storage:link || true
echo "✅ Lien de stockage créé"

# Étape 12: Vérifications finales
echo "✅ VÉRIFICATIONS FINALES"

# Vérifier le dossier public
if [ -d public ] && [ -f public/index.php ]; then
    echo "✅ public/index.php trouvé"
else
    echo "❌ ERREUR: public/index.php introuvable !"
    exit 1
fi

# Vérifier que vendor existe
if [ -d vendor ]; then
    echo "✅ dossier vendor trouvé"
else
    echo "❌ ERREUR: dossier vendor introuvable !"
    exit 1
fi

# Vérifier les permissions des fichiers critiques
if [ -w storage/logs/laravel.log ] && [ -w bootstrap/cache/config.php ]; then
    echo "✅ Permissions d'écriture OK"
else
    echo "❌ ERREUR: Problème de permissions sur les fichiers"
    exit 1
fi

echo "=========================================="
echo "🎉 DÉPLOIEMENT RÉUSSI !"
echo "🌐 Votre application est disponible sur:"
echo "   https://vps-d91fd27c.vps.ovh.net"
echo "=========================================="