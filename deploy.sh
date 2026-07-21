#!/bin/bash
# =============================================================
#  PICME225 — Script de déploiement en production
#  Environnement : PostgreSQL (production)
#  Auteur : PicMe225 DevOps
# =============================================================
set -e  # Quitte le script en cas d'erreur

echo "════════════════════════════════════════════════════"
echo "  🚀 PICME225 — Déploiement Production"
echo "════════════════════════════════════════════════════"

# --- 1. PASSER EN MODE MAINTENANCE ---
echo "⚙️  [1/9] Activation du mode maintenance..."
php artisan down --message="Mise à jour en cours. Revenez dans quelques minutes." --retry=60

# --- 2. PULL DES DERNIÈRES SOURCES ---
echo "📥 [2/9] Pull des sources depuis Git..."
git pull origin main

# --- 3. DÉPENDANCES COMPOSER (NO DEV) ---
echo "📦 [3/9] Installation des dépendances Composer (production)..."
composer install --optimize-autoloader --no-dev --no-interaction

# --- 4. MIGRATIONS ---
echo "🗄️  [4/9] Exécution des migrations..."
php artisan migrate --force --no-interaction

# --- 5. SEEDERS ESSENTIELS (optionnel, désactiver si déjà fait) ---
# echo "🌱 [5/9] Seeders de base..."
# php artisan db:seed --class=ServiceSeeder --force --no-interaction
# php artisan db:seed --class=ServiceTypeSeeder --force --no-interaction

# --- 6. CACHE LARAVEL ---
echo "⚡ [6/9] Mise en cache des configurations, routes et vues..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# --- 7. STORAGE LINK ---
echo "🔗 [7/9] Lien symbolique du stockage..."
php artisan storage:link || true  # 'true' évite l'erreur si le lien existe déjà

# --- 8. PERMISSIONS FICHIERS ---
echo "🔒 [8/9] Mise à jour des permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# --- 9. RETOUR EN LIGNE ---
echo "✅ [9/9] Désactivation du mode maintenance..."
php artisan up

echo ""
echo "════════════════════════════════════════════════════"
echo "  ✅ Déploiement terminé avec succès !"
echo "════════════════════════════════════════════════════"
