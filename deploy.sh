#!/bin/bash
set -e

echo "==> 🚀 DÉMARRAGE DU DÉPLOIEMENT STUDUP"
echo "=========================================="

# Étape 1: Aller dans le dossier de l'application
cd /var/www/studup-backend
echo "📁 Dossier: $(pwd)"

# Étape 2: Donner les permissions temporaires à l'utilisateur courant
echo "🔧 Correction des permissions temporaires..."
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
mkdir -p vendor  # ← AJOUT IMPORTANT
echo "✅ Structure des dossiers créée"

# Étape 5: Installation des dépendances COMPOSER D'ABORD
echo "📦 INSTALLATION DES DÉPENDANCES COMPOSER"
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
echo "✅ Dépendances Composer installées"

# Étape 6: MAINTENANT configuration des permissions Apache
echo "🔐 CONFIGURATION DES PERMISSIONS APACHE"
sudo chown -R www-data:www-data /var/www/studup-backend
sudo chmod -R 755 /var/www/studup-backend
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R 777 storage/framework/cache
echo "✅ Permissions Apache configurées"

# Étape 7: Configuration de l'application Laravel
echo "⚙️  CONFIGURATION LARAVEL"

# Génération de la clé API
if ! grep -q "APP_KEY=base64" .env; then
    php artisan key:generate --force
    echo "✅ Clé API générée"
else
    echo "✅ Clé API déjà configurée"
fi

# Configuration de l'URL
if ! grep -q "APP_URL=" .env; then
    echo "APP_URL=https://vps-d91fd27c.vps.ovh.net" >> .env
    echo "✅ APP_URL configuré"
fi

# Étape 8: Nettoyage des caches
echo "🧹 NETTOYAGE DES CACHES"
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
echo "✅ Caches nettoyés"

# Étape 9: Optimisation production
echo "⚡ OPTIMISATION PRODUCTION"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Application optimisée"

# Étape 10: Migrations base de données
echo "🗄️  MIGRATIONS BASE DE DONNÉES"
php artisan migrate --force
echo "✅ Migrations exécutées"

# Étape 11: Lien de stockage
echo "🔗 LIEN DE STOCKAGE"
php artisan storage:link || true
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

echo "=========================================="
echo "🎉 DÉPLOIEMENT RÉUSSI !"
echo "🌐 Votre application est disponible sur:"
echo "   https://vps-d91fd27c.vps.ovh.net"
echo "=========================================="