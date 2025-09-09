#!/bin/bash
set -e

echo "==> 🚀 Démarrage du déploiement Studup Backend"

# Aller dans le dossier de l'application
cd /var/www/studup-backend

echo "📦 Vérification de l'environnement..."

# Créer .env s'il n'existe pas
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ .env créé à partir de .env.example"
    echo "⚠️  IMPORTANT: Configurez les variables dans .env !"
fi

echo "📁 Création de la structure des dossiers..."

# Créer tous les dossiers nécessaires pour Laravel
mkdir -p \
    storage/app/public \
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

# Installer les dépendances pour la production
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

echo "🔑 Configuration de l'application Laravel..."

# Générer la clé d'application si elle n'existe pas
if ! grep -q "APP_KEY=base64" .env; then
    php artisan key:generate --force
    echo "✅ Clé d'application générée"
fi

# Configurer l'URL de l'application
if ! grep -q "APP_URL=" .env; then
    echo "APP_URL=https://vps-d91fd27c.vps.ovh.net" >> .env
    echo "✅ APP_URL configuré"
fi

echo "🧹 Nettoyage des caches..."

# Nettoyer tous les caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "⚡ Optimisation pour la production..."

# Créer les caches optimisés
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🗄️  Exécution des migrations de base de données..."

# Exécuter les migrations
php artisan migrate --force

echo "🔗 Création du lien de stockage..."

# Créer le lien symbolique pour le stockage
php artisan storage:link || true

echo "✅ Vérifications finales..."

# Vérifier que l'application fonctionne
if sudo -u www-data php artisan about > /dev/null 2>&1; then
    echo "✅ Laravel fonctionne correctement"
else
    echo "❌ Erreur avec Laravel, vérifiez les logs"
fi

# Vérifier que le dossier public existe
if [ -d public ] && [ -f public/index.php ]; then
    echo "✅ Dossier public et index.php trouvés"
else
    echo "❌ PROBLEME: public/index.php introuvable !"
    echo "📋 Contenu du dossier:"
    ls -la
    exit 1
fi

echo "==> 🎉 Déploiement terminé avec succès !"
echo "==> 🌐 Votre application est disponible sur: https://vps-d91fd27c.vps.ovh.net"