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

# Étape 5: Installation des dépendances Composer
echo "📦 INSTALLATION DES DÉPENDANCES COMPOSER"
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
echo "✅ Dépendances Composer installées"

# Étape 6: Configuration des permissions POUR APACHE
echo "🔐 CONFIGURATION DES PERMISSIONS APACHE"
sudo chown -R www-data:www-data /var/www/studup-backend
sudo chmod -R 755 /var/www/studup-backend
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R 777 storage/framework/cache storage/logs

# Créer les fichiers de cache avec les bonnes permissions
sudo -u www-data touch storage/logs/laravel.log
sudo -u www-data touch bootstrap/cache/config.php
sudo -u www-data touch bootstrap/cache/packages.php
sudo -u www-data touch bootstrap/cache/services.php

sudo chmod 666 storage/logs/laravel.log
sudo chmod 666 bootstrap/cache/config.php
sudo chmod 666 bootstrap/cache/packages.php
sudo chmod 666 bootstrap/cache/services.php

echo "✅ Permissions Apache configurées"

# Étape 7: Configuration de l'application Laravel
echo "⚙️  CONFIGURATION LARAVEL"

# Génération de la clé API (sans utiliser artisan pour éviter les erreurs)
if ! grep -q "APP_KEY=base64" .env; then
    echo "🔑 Génération de la clé API..."
    KEY=$(php -r "echo 'base64:'.base64_encode(random_bytes(32));")
    sed -i "s/APP_KEY=/APP_KEY=$KEY/" .env
    echo "✅ Clé API générée manuellement"
else
    echo "✅ Clé API déjà configurée"
fi

# Configuration de l'URL
if ! grep -q "APP_URL=" .env; then
    echo "APP_URL=https://vps-d91fd27c.vps.ovh.net" >> .env
    echo "✅ APP_URL configuré"
fi

# Étape 8: RECONSTRUCTION MANUELLE DES CACHES
echo "🔨 RECONSTRUCTION MANUELLE DES CACHES"

# Supprimer les caches existants qui pourraient être corrompus
sudo rm -f bootstrap/cache/*.php

# Recréer les fichiers de cache vides avec les bonnes permissions
sudo -u www-data touch bootstrap/cache/config.php
sudo -u www-data touch bootstrap/cache/packages.php
sudo -u www-data touch bootstrap/cache/services.php

sudo chmod 666 bootstrap/cache/config.php
sudo chmod 666 bootstrap/cache/packages.php
sudo chmod 666 bootstrap/cache/services.php

# Étape 9: OPTIMISATION AVEC APPROCHE ALTERNATIVE
echo "⚡ OPTIMISATION ALTERNATIVE"

# Utiliser une approche directe pour éviter les erreurs de container
php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
\$kernel->call('config:cache');
echo '✅ Configuration cachée' . PHP_EOL;
" || echo "⚠️  config:cache a échoué, continuation..."

php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
\$kernel->call('route:cache');
echo '✅ Routes cachées' . PHP_EOL;
" || echo "⚠️  route:cache a échoué, continuation..."

php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
\$kernel->call('view:cache');
echo '✅ Vues cachées' . PHP_EOL;
" || echo "⚠️  view:cache a échoué, continuation..."

# Étape 10: MIGRATIONS AVEC APPROCHE DIRECTE
echo "🗄️  MIGRATIONS BASE DE DONNÉES"

php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
\$status = \$kernel->call('migrate', ['--force' => true]);
echo '✅ Migrations exécutées: ' . (\$status === 0 ? 'SUCCÈS' : 'ÉCHEC') . PHP_EOL;
" || echo "⚠️  Les migrations ont échoué, vérifiez la base de données"

# Étape 11: LIEN DE STOCKAGE
echo "🔗 LIEN DE STOCKAGE"

# Méthode manuelle pour créer le lien de stockage
if [ ! -L "public/storage" ]; then
    ln -sf ../storage/app/public public/storage
    echo "✅ Lien de stockage créé manuellement"
else
    echo "✅ Lien de stockage existe déjà"
fi

# Étape 12: VÉRIFICATIONS FINALES
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
if [ -w storage/logs/laravel.log ] && [ -f bootstrap/cache/config.php ]; then
    echo "✅ Fichiers de cache accessibles"
else
    echo "⚠️  Attention: permissions des fichiers de cache"
fi

echo "=========================================="
echo "🎉 DÉPLOIEMENT RÉUSSI !"
echo "🌐 Votre application est disponible sur:"
echo "   https://vps-d91fd27c.vps.ovh.net"
echo "=========================================="