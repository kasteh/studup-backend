#!/bin/bash
set -e

echo "==> 🚀 SCRIPT DE DÉPLOIEMENT SIMPLIFIÉ"
echo "=========================================="

# Ce script est maintenant très simple car tout est géré dans le YAML
# Il sert juste de placeholder pour la compatibilité

echo "📁 Vérification de l'environnement..."
if [ -f .env ]; then
    echo "✅ Fichier .env présent"
else
    echo "❌ Fichier .env manquant"
    exit 1
fi

echo "📦 Vérification des dépendances..."
if [ -d vendor ]; then
    echo "✅ Dossier vendor présent"
else
    echo "❌ Dossier vendor manquant"
    exit 1
fi

echo "🔗 Vérification du lien de stockage..."
if [ -L "public/storage" ]; then
    echo "✅ Lien de stockage présent"
else
    echo "⚠️  Lien de stockage manquant - création..."
    sudo ln -sf ../storage/app/public public/storage
    echo "✅ Lien créé"
fi

echo "✅ Vérifications terminées"
echo "=========================================="