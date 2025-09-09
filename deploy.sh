#!/bin/bash
set -e

echo "==> 🚀 DÉPLOIEMENT ULTRA-SIMPLIFIÉ"
echo "=========================================="

APP_DIR="/var/www/studup-backend"
cd "$APP_DIR"

echo "📁 Répertoire: $(pwd)"

# 1. Vérification basique
echo "🔍 Vérifications de base..."
if [ ! -f .env ]; then
    echo "❌ ERREUR: Fichier .env manquant"
    exit 1
fi

if [ ! -f vendor/autoload.php ]; then
    echo "❌ ERREUR: Vendor Composer manquant"
    exit 1
fi

# 2. Test Laravel très basique
echo "🔍 Test Laravel basique..."
if php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; echo '✅ Laravel chargé';"; then
    echo "✅ Laravel fonctionne correctement"
else
    echo "❌ ERREUR: Laravel ne peut pas démarrer"
    echo "📋 Tentative de réparation de l'autoloader..."
    composer dump-autoload
fi

# 3. Structure minimale
echo "📁 Structure minimale..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs bootstrap/cache
echo "✅ Structure créée"

# 4. Permissions minimales
echo "🔐 Permissions minimales..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
echo "✅ Permissions configurées"

# 5. Lien de stockage
echo "🔗 Lien de stockage..."
if [ ! -L "public/storage" ]; then
    ln -sf ../storage/app/public public/storage
    echo "✅ Lien créé"
else
    echo "✅ Lien existe déjà"
fi

echo "=========================================="
echo "🎉 DÉPLOIEMENT TERMINÉ"
echo "💡 L'application se initialisera au premier accès"
echo "=========================================="