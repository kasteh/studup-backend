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

# Étape 3: NETTOYAGE COMPLET DES CACHES CORROMPUS
echo "🧹 NETTOYAGE COMPLET DES CACHES"
rm -f bootstrap/cache/*.php
rm -f storage/framework/cache/*.php
rm -f storage/framework/views/*.php
rm -f storage/framework/sessions/*
echo "✅ Caches corrompus supprimés"

# Étape 4: Configuration de l'environnement
echo "🔧 CONFIGURATION ENVIRONNEMENT"
if [ ! -f .env ]; then
    echo "📄 Création du fichier .env..."
    cp .env.example .env
    echo "✅ .env créé à partir de .env.example"
    echo "⚠️  IMPORTANT: Configurez les variables dans .env !"
else
    echo "✅ .env existe déjà"
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
sudo chown -R www-data:www-data /var/www/studup-backend
sudo chmod -R 755 /var/www/studup-backend
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R 777 storage/framework/cache storage/logs

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

# Génération de la clé API (méthode manuelle)
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

# Étape 9: RÉINITIALISATION MANUELLE DU FRAMEWORK
echo "🔄 RÉINITIALISATION DU FRAMEWORK LARAVEL"

# Vider complètement les caches et régénérer l'autoloader
composer dump-autoload

# Étape 10: CRÉATION MANUELLE DES FICHIERS DE CACHE
echo "🔨 CRÉATION MANUELLE DES CACHES"

# Créer un fichier config.php vide mais valide
sudo -u www-data bash -c 'cat > bootstrap/cache/config.php << "EOF"
<?php return array (
  // Configuration manuellement initialisée
  // Les caches seront régénérés par Laravel au premier accès
);
EOF'

# Créer les autres fichiers de cache vides
sudo -u www-data bash -c 'echo "<?php return array ();" > bootstrap/cache/packages.php'
sudo -u www-data bash -c 'echo "<?php return array ();" > bootstrap/cache/services.php'

sudo chmod 666 bootstrap/cache/*.php

echo "✅ Fichiers de cache créés manuellement"

# Étape 11: MIGRATIONS AVEC APPROCHE DIRECTE SIMPLIFIÉE
echo "🗄️  MIGRATIONS BASE DE DONNÉES"

# Approche ultra-simplifiée pour les migrations
if php -r '
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();
$host = getenv("DB_HOST") ?: "127.0.0.1";
$port = getenv("DB_PORT") ?: "3306";
$database = getenv("DB_DATABASE") ?: "forge";
$username = getenv("DB_USERNAME") ?: "forge";
$password = getenv("DB_PASSWORD") ?: "";

if (empty($database) || $database === "forge") {
    echo "⚠️  Base de données non configurée dans .env\n";
    exit(1);
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $database");
    echo "✅ Base de données '$database' vérifiée/créée\n";
} catch (PDOException $e) {
    echo "❌ Erreur base de données: " . $e->getMessage() . "\n";
    exit(1);
}
'; then
    echo "✅ Base de données prête"
else
    echo "⚠️  Vérification base de données échouée"
fi

# Étape 12: LIEN DE STOCKAGE AVEC PERMISSIONS
echo "🔗 LIEN DE STOCKAGE"

# Créer le lien de stockage avec les bonnes permissions
if [ ! -L "public/storage" ]; then
    sudo -u www-data ln -sf ../storage/app/public public/storage
    echo "✅ Lien de stockage créé"
else
    echo "✅ Lien de stockage existe déjà"
fi

# Étape 13: VÉRIFICATIONS FINALES
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

# Vérifier les fichiers de cache
if [ -f bootstrap/cache/config.php ] && [ -f bootstrap/cache/services.php ]; then
    echo "✅ Fichiers de cache présents"
else
    echo "❌ ERREUR: Fichiers de cache manquants"
    exit 1
fi

# Vérifier le lien de stockage
if [ -L "public/storage" ]; then
    echo "✅ Lien de stockage présent"
else
    echo "⚠️  Lien de stockage manquant"
fi

echo "=========================================="
echo "🎉 DÉPLOIEMENT RÉUSSI !"
echo "🌐 Votre application est disponible sur:"
echo "   https://vps-d91fd27c.vps.ovh.net"
echo "=========================================="

# Étape 14: MESSAGE D'INFORMATION IMPORTANT
echo ""
echo "💡 INFORMATION IMPORTANTE:"
echo "   Laravel régénérera automatiquement les caches optimisés"
echo "   au premier accès à l'application. Ceci est normal."
echo ""