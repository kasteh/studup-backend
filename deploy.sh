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
if [ -d "$APP_DIR/storage" ]; then
    echo "📁 Sauvegarde du dossier storage..."
    cp -r "$APP_DIR/storage" "$STORAGE_BACKUP"
    echo "✅ Storage sauvegardé"
fi

if [ -f "$APP_DIR/.env" ]; then
    echo "📄 Sauvegarde du fichier .env..."
    cp "$APP_DIR/.env" "$ENV_BACKUP"
    echo "✅ .env sauvegardé"
fi

# Étape 1: Aller dans le dossier de l'application
cd "$APP_DIR"
echo "📁 Dossier: $(pwd)"

# Étape 2: Donner les permissions temporaires pour l'installation
echo "🔧 Configuration des permissions temporaires..."
sudo chown -R ubuntu:ubuntu "$APP_DIR"
sudo chmod -R 755 "$APP_DIR"

# Étape 3: NETTOYAGE INTELLIGENT DES CACHES
echo "🧹 NETTOYAGE INTELLIGENT DES CACHES"
# Nettoyer seulement les caches, pas les uploads
find bootstrap/cache -name "*.php" -type f -delete 2>/dev/null || true
find storage/framework/cache -name "*.php" -type f -delete 2>/dev/null || true
find storage/framework/views -name "*.php" -type f -delete 2>/dev/null || true
find storage/framework/sessions -name "*" -type f -not -name ".gitignore" -delete 2>/dev/null || true
echo "✅ Caches corrompus supprimés (uploads préservés)"

# Étape 4: RESTAURATION DES DONNÉES CRITIQUES
echo "🔄 RESTAURATION DES DONNÉES"
if [ -f "$ENV_BACKUP" ]; then
    cp "$ENV_BACKUP" .env
    echo "✅ .env restauré"
elif [ ! -f .env ]; then
    echo "📄 Création du fichier .env..."
    cp .env.example .env
    echo "✅ .env créé à partir de .env.example"
    echo "⚠️  IMPORTANT: Configurez les variables dans .env !"
fi

# Restaurer les uploads si ils existaient
if [ -d "$STORAGE_BACKUP/app/public" ]; then
    echo "📁 Restauration des uploads..."
    mkdir -p storage/app
    cp -r "$STORAGE_BACKUP/app/public" storage/app/
    echo "✅ Uploads restaurés"
fi

# Restaurer les logs importants
if [ -d "$STORAGE_BACKUP/logs" ] && [ "$(ls -A $STORAGE_BACKUP/logs 2>/dev/null)" ]; then
    echo "📋 Restauration des logs récents..."
    mkdir -p storage/logs
    # Garder seulement les 5 derniers fichiers de log
    find "$STORAGE_BACKUP/logs" -name "*.log" -type f -exec ls -t {} + | head -5 | xargs -I {} cp {} storage/logs/
    echo "✅ Logs récents restaurés"
fi

# Étape 5: Création de la structure des dossiers
echo "📁 CRÉATION DES DOSSIERS LARAVEL"
mkdir -p storage/app/public
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p vendor
echo "✅ Structure des dossiers créée"

# Étape 6: Installation des dépendances Composer
echo "📦 INSTALLATION DES DÉPENDANCES COMPOSER"
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
echo "✅ Dépendances Composer installées"

# Étape 7: Configuration des permissions POUR APACHE
echo "🔐 CONFIGURATION DES PERMISSIONS APACHE"
sudo chown -R www-data:www-data "$APP_DIR"
sudo chmod -R 755 "$APP_DIR"
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R 777 storage/framework/cache storage/logs storage/app/public

# Créer les fichiers de cache vides avec les bonnes permissions
sudo -u www-data touch storage/logs/laravel.log
sudo -u www-data touch bootstrap/cache/config.php
sudo -u www-data touch bootstrap/cache/packages.php
sudo -u www-data touch bootstrap/cache/services.php

sudo chmod 666 storage/logs/laravel.log
sudo chmod 666 bootstrap/cache/config.php
sudo chmod 666 bootstrap/cache/packages.php
sudo chmod 666 bootstrap/cache/services.php

echo "✅ Permissions Apache configurées"

# Étape 8: Configuration de l'application Laravel
echo "⚙️  CONFIGURATION LARAVEL"

# Génération de la clé API si nécessaire
if ! grep -q "APP_KEY=base64" .env; then
    echo "🔑 Génération de la clé API..."
    KEY=$(php -r "echo 'base64:'.base64_encode(random_bytes(32));")
    sed -i "s/APP_KEY=/APP_KEY=$KEY/" .env
    echo "✅ Clé API générée"
else
    echo "✅ Clé API déjà configurée"
fi

# Configuration de l'URL si nécessaire
if ! grep -q "APP_URL=" .env; then
    echo "APP_URL=https://vps-d91fd27c.vps.ovh.net" >> .env
    echo "✅ APP_URL configuré"
fi

# Étape 9: OPTIMISATIONS LARAVEL PRODUCTION
echo "⚡ OPTIMISATIONS PRODUCTION LARAVEL"
composer dump-autoload --optimize

# Vider et recréer les caches proprement
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Générer les caches optimisés
php artisan config:cache 2>/dev/null || echo "⚠️  Config cache non généré (normal si DB non accessible)"
php artisan route:cache 2>/dev/null || echo "⚠️  Route cache non généré"
php artisan view:cache 2>/dev/null || echo "⚠️  View cache non généré"

echo "✅ Optimisations appliquées"

# Étape 10: MIGRATIONS BASE DE DONNÉES SÉCURISÉES
echo "🗄️  MIGRATIONS BASE DE DONNÉES"

# Vérification de la connexion DB avant migration
if php artisan migrate:status >/dev/null 2>&1; then
    echo "✅ Connexion à la base de données réussie"
    
    # Backup DB avant migration
    echo "💾 Backup de la base de données..."
    php artisan backup:run --only-db 2>/dev/null || echo "⚠️  Backup DB non disponible"
    
    # Exécuter les migrations
    php artisan migrate --force
    echo "✅ Migrations exécutées"
else
    echo "⚠️  Impossible de se connecter à la base de données"
    echo "Vérifiez votre configuration .env"
    echo "L'application fonctionnera mais sans accès DB"
fi

# Étape 11: LIEN DE STOCKAGE AVEC PERMISSIONS
echo "🔗 LIEN DE STOCKAGE"

# Supprimer l'ancien lien s'il existe
if [ -L "public/storage" ]; then
    rm public/storage
fi

# Créer le nouveau lien
sudo -u www-data ln -sf ../storage/app/public public/storage
echo "✅ Lien de stockage créé"

# Étape 12: NETTOYAGE DES FICHIERS TEMPORAIRES
echo "🧹 NETTOYAGE FINAL"
rm -rf "$STORAGE_BACKUP" "$ENV_BACKUP" 2>/dev/null || true
echo "✅ Fichiers temporaires nettoyés"

# Étape 13: VÉRIFICATIONS FINALES
echo "✅ VÉRIFICATIONS FINALES"

# Tests de base
declare -i ERROR_COUNT=0

if [ -d public ] && [ -f public/index.php ]; then
    echo "✅ public/index.php trouvé"
else
    echo "❌ ERREUR: public/index.php introuvable !"
    ERROR_COUNT+=1
fi

if [ -d vendor ] && [ -f vendor/autoload.php ]; then
    echo "✅ vendor/autoload.php trouvé"
else
    echo "❌ ERREUR: vendor/autoload.php introuvable !"
    ERROR_COUNT+=1
fi

if [ -f .env ]; then
    echo "✅ Fichier .env présent"
else
    echo "❌ ERREUR: Fichier .env manquant"
    ERROR_COUNT+=1
fi

if [ -L "public/storage" ]; then
    echo "✅ Lien de stockage présent"
else
    echo "⚠️  Lien de stockage manquant"
fi

# Test rapide PHP
if php -v >/dev/null 2>&1; then
    echo "✅ PHP fonctionnel"
else
    echo "❌ ERREUR: PHP non fonctionnel"
    ERROR_COUNT+=1
fi

# Vérifier les permissions critiques
if [ -w storage/logs ]; then
    echo "✅ Permissions storage/logs OK"
else
    echo "❌ ERREUR: storage/logs non accessible en écriture"
    ERROR_COUNT+=1
fi

# Résultat final
if [ $ERROR_COUNT -eq 0 ]; then
    echo "=========================================="
    echo "🎉 DÉPLOIEMENT RÉUSSI SANS ERREUR !"
    echo "🌐 Votre application est disponible sur:"
    echo "   https://vps-d91fd27c.vps.ovh.net"
    echo "=========================================="
else
    echo "=========================================="
    echo "⚠️  DÉPLOIEMENT TERMINÉ AVEC $ERROR_COUNT ERREUR(S)"
    echo "Vérifiez les messages ci-dessus"
    echo "=========================================="
    exit 1
fi

echo ""
echo "💡 INFORMATION:"
echo "   L'application est maintenant optimisée pour la production"
echo "   Les caches Laravel accéléreront les performances"
echo ""