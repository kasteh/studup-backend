#!/bin/bash
set -e

echo "==> 🚀 Démarrage du déploiement Studup Backend"

# Vérification qu'on est dans le bon dossier
cd /var/www/studup-backend || exit 1

echo "📦 Vérification de l'environnement..."

# Créer .env s'il n'existe pas (avec exemple)
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ .env créé à partir de .env.example"
    echo "⚠️  ATTENTION: Configurez les variables d'environnement dans .env !"
fi

echo "📁 Création des dossiers de stockage..."

# Créer toute l'arborescence nécessaire pour Laravel
mkdir -p \
    storage/app \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache

echo "🔧 Configuration des permissions..."

# Définir le propriétaire Apache (www-data) et permissions
sudo chown -R www-data:www-data /var/www/studup-backend
sudo chmod -R 755 /var/www/studup-backend
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R 777 storage/framework/cache

echo "📦 Installation des dépendances Composer..."

# Installer les dépendances (production uniquement)
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

echo "🔑 Vérification de la clé d'application..."

# Générer une clé d'application si elle n'existe pas
if ! grep -q "APP_KEY=base64" .env; then
    php artisan key:generate --force
    echo "✅ Nouvelle clé d'application générée"
else
    echo "✅ Clé d'application déjà configurée"
fi

echo "🧹 Nettoyage des caches..."

# Nettoyer tous les caches existants
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "⚡ Optimisation pour la production..."

# Créer les caches optimisés
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🗄️  Exécution des migrations..."

# Exécuter les migrations de base de données
php artisan migrate --force

echo "✅ Vérification finale..."

# Vérifier que tout fonctionne
php artisan about | grep "Application Name"
php artisan storage:link || true

echo "==> 🎉 Déploiement terminé avec succès !"
echo "==> 🌐 L'application est maintenant disponible"