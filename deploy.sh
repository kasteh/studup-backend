#!/bin/bash
set -e

echo "==> 🚀 DÉMARRAGE DU DÉPLOIEMENT STUDUP"
echo "=========================================="

# Variables
APP_DIR="/var/www/studup-backend"
STORAGE_BACKUP="/tmp/studup-storage-backup"
ENV_BACKUP="/tmp/studup-env-backup"

# Étape 0: SAUVEGARDE DES DONNÉES IMPORTANTES
echo "💾 SAUVEGARDE DES DONNÉES CRITIQUES"
if [ -d "$APP_DIR/storage/app/public" ]; then
    echo "📁 Sauvegarde des uploads..."
    cp -r "$APP_DIR/storage/app/public" "$STORAGE_BACKUP" 2>/dev/null || true
    echo "✅ Uploads sauvegardés"
fi

if [ -f "$APP_DIR/.env" ]; then
    echo "📄 Sauvegarde du fichier .env..."
    cp "$APP_DIR/.env" "$ENV_BACKUP"
    echo "✅ .env sauvegardé"
fi

# Étape 1: Aller dans le dossier de l'application
cd "$APP_DIR"
echo "📁 Dossier: $(pwd)"

# Étape 2: Configuration des permissions temporaires
echo "🔧 Configuration des permissions temporaires..."
sudo chown -R ubuntu:ubuntu "$APP_DIR"
sudo chmod -R 755 "$APP_DIR"

# Étape 3: NETTOYAGE RADICAL ET SÉCURISÉ
echo "🧹 NETTOYAGE RADICAL DES CACHES"
# Supprimer TOUS les fichiers de cache corrompus
rm -rf bootstrap/cache/*
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/views/*
rm -rf storage/framework/sessions/*
rm -rf storage/logs/laravel.log
echo "✅ Tous les caches supprimés"

# Étape 4: RESTAURATION DES DONNÉES CRITIQUES
echo "🔄 RESTAURATION DES DONNÉES"
# Restaurer le .env en priorité
if [ -f "$ENV_BACKUP" ]; then
    cp "$ENV_BACKUP" .env
    echo "✅ .env restauré"
elif [ ! -f .env ] && [ -f .env.example ]; then
    echo "📄 Création du fichier .env depuis .env.example..."
    cp .env.example .env
    # Générer une clé APP_KEY immédiatement
    KEY=$(php -r "echo 'base64:'.base64_encode(random_bytes(32));")
    sed -i "s|APP_KEY=|APP_KEY=$KEY|" .env
    echo "✅ .env créé avec nouvelle clé"
else
    echo "❌ ERREUR: Impossible de configurer .env"
    exit 1
fi

# Vérifier que APP_KEY existe dans .env
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Correction de la clé API..."
    KEY=$(php -r "echo 'base64:'.base64_encode(random_bytes(32));")
    sed -i "s|APP_KEY=.*|APP_KEY=$KEY|" .env
    echo "✅ Clé API régénérée"
fi

# Configuration URL si manquante
if ! grep -q "APP_URL=" .env; then
    echo "APP_URL=https://vps-d91fd27c.vps.ovh.net" >> .env
    echo "✅ APP_URL ajouté"
fi

# Étape 5: Création de la structure COMPLÈTE
echo "📁 CRÉATION STRUCTURE LARAVEL"
mkdir -p storage/app/public
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views  
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p bootstrap/cache
echo "✅ Structure des dossiers créée"

# Étape 6: Installation Composer SANS SCRIPTS
echo "📦 INSTALLATION DÉPENDANCES COMPOSER"
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --no-scripts
echo "✅ Dépendances Composer installées (sans scripts)"

# Étape 7: PERMISSIONS APACHE AVANT LARAVEL
echo "🔐 CONFIGURATION PERMISSIONS APACHE"
sudo chown -R www-data:www-data "$APP_DIR"
sudo chmod -R 755 "$APP_DIR"
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R 777 storage/framework/cache storage/logs storage/app/public

# Créer les fichiers requis avec bonnes permissions
sudo -u www-data touch storage/logs/laravel.log
sudo chmod 666 storage/logs/laravel.log
echo "✅ Permissions Apache configurées"

# Étape 8: RÉINITIALISATION LARAVEL ÉTAPE PAR ÉTAPE
echo "⚙️  RÉINITIALISATION LARAVEL"

# Test de base de Laravel AVANT toute commande artisan
echo "🔍 Test de base Laravel..."
if ! php artisan --version >/dev/null 2>&1; then
    echo "❌ ERREUR: Laravel ne démarre pas correctement"
    echo "🔧 Vérification du fichier .env..."
    cat .env | head -10
    exit 1
fi
echo "✅ Laravel démarre correctement"

# Maintenant on peut vider les caches proprement
echo "🧹 Vidage des caches Laravel..."
php artisan config:clear >/dev/null 2>&1 || true
php artisan cache:clear >/dev/null 2>&1 || true
php artisan route:clear >/dev/null 2>&1 || true
php artisan view:clear >/dev/null 2>&1 || true
echo "✅ Caches vidés"

# Exécuter les scripts Composer maintenant que Laravel fonctionne
echo "📦 Finalisation Composer..."
composer run-script post-autoload-dump --no-interaction
echo "✅ Scripts Composer exécutés"

# Étape 9: OPTIMISATIONS PRODUCTION SEULEMENT SI TOUT FONCTIONNE
echo "⚡ OPTIMISATIONS PRODUCTION"

# Tester si la base de données est accessible avant les optimisations
echo "🗄️  Test de connexion base de données..."
if php artisan migrate:status >/dev/null 2>&1; then
    echo "✅ Base de données accessible"
    
    # Faire les migrations si nécessaire
    php artisan migrate --force
    echo "✅ Migrations exécutées"
    
    # Générer les caches optimisés
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    echo "✅ Caches optimisés générés"
    
else
    echo "⚠️  Base de données non accessible - optimisations limitées"
    echo "L'application fonctionnera mais sans base de données"
fi

# Étape 10: RESTAURATION DES UPLOADS
echo "📁 RESTAURATION DES UPLOADS"
if [ -d "$STORAGE_BACKUP" ]; then
    echo "📁 Restauration des fichiers uploadés..."
    cp -r "$STORAGE_BACKUP"/* storage/app/public/ 2>/dev/null || true
    sudo chown -R www-data:www-data storage/app/public
    echo "✅ Uploads restaurés"
fi

# Étape 11: LIEN DE STOCKAGE
echo "🔗 CONFIGURATION LIEN STOCKAGE"
if [ -L "public/storage" ]; then
    rm public/storage
fi
sudo -u www-data ln -sf ../storage/app/public public/storage
echo "✅ Lien de stockage créé"

# Étape 12: NETTOYAGE FINAL
echo "🧹 NETTOYAGE FINAL"
rm -rf "$STORAGE_BACKUP" "$ENV_BACKUP" 2>/dev/null || true
echo "✅ Fichiers temporaires supprimés"

# Étape 13: VÉRIFICATIONS COMPLÈTES
echo "✅ VÉRIFICATIONS FINALES"

declare -i ERROR_COUNT=0

# Vérifications critiques
if [ -f public/index.php ]; then
    echo "✅ public/index.php présent"
else
    echo "❌ public/index.php manquant"
    ERROR_COUNT+=1
fi

if [ -f vendor/autoload.php ]; then
    echo "✅ vendor/autoload.php présent"  
else
    echo "❌ vendor/autoload.php manquant"
    ERROR_COUNT+=1
fi

if [ -f .env ]; then
    echo "✅ Fichier .env présent"
else
    echo "❌ Fichier .env manquant"
    ERROR_COUNT+=1
fi

# Test Laravel critique
if php artisan --version >/dev/null 2>&1; then
    echo "✅ Laravel fonctionnel"
else
    echo "❌ Laravel non fonctionnel"
    ERROR_COUNT+=1
fi

# Test permissions
if [ -w storage/logs ]; then
    echo "✅ Permissions storage OK"
else
    echo "❌ Permissions storage NOK"
    ERROR_COUNT+=1
fi

# Test lien stockage
if [ -L public/storage ]; then
    echo "✅ Lien stockage OK"
else
    echo "⚠️  Lien stockage manquant"
fi

# Résultat final
if [ $ERROR_COUNT -eq 0 ]; then
    echo "=========================================="
    echo "🎉 DÉPLOIEMENT RÉUSSI SANS ERREUR !"
    echo "🌐 Application disponible sur:"
    echo "   https://vps-d91fd27c.vps.ovh.net"
    echo "=========================================="
    exit 0
else
    echo "=========================================="
    echo "❌ DÉPLOIEMENT ÉCHOUÉ - $ERROR_COUNT ERREUR(S)"
    echo "Vérifiez les messages ci-dessus"
    echo "=========================================="
    exit 1
fi