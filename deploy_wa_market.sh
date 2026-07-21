#!/bin/bash
echo "=== Déploiement WhatsApp Marketplace IA ==="

# Récupération dynamique des pods Laravel (Deployment et Worker)
LARAVEL_PODS=$(sudo kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
WORKER_PODS=$(sudo kubectl get pods -l app=laravel-worker --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
ALL_PODS="$LARAVEL_PODS $WORKER_PODS"

# 1. Copie des fichiers dans tous les pods
for POD in $ALL_PODS; do
    echo "Transfert vers $POD..."
    
    # Controllers
    sudo kubectl cp /tmp/WhatsAppWebhookController.php default/${POD}:/app/app/Http/Controllers/Api/WhatsAppWebhookController.php
    sudo kubectl cp /tmp/WhatsappGroupController.php default/${POD}:/app/app/Http/Controllers/Admin/WhatsappGroupController.php
    
    # Models
    sudo kubectl cp /tmp/WhatsappMessage.php default/${POD}:/app/app/Models/WhatsappMessage.php
    sudo kubectl cp /tmp/WhatsappGroup.php default/${POD}:/app/app/Models/WhatsappGroup.php
    sudo kubectl cp /tmp/MarketplaceListing.php default/${POD}:/app/app/Models/MarketplaceListing.php
    
    # Jobs & Services
    sudo kubectl cp /tmp/ProcessWhatsappBatchJob.php default/${POD}:/app/app/Jobs/ProcessWhatsappBatchJob.php
    sudo kubectl cp /tmp/AiFailoverService.php default/${POD}:/app/app/Services/AiFailoverService.php
    
    # Routes
    sudo kubectl cp /tmp/admin.php default/${POD}:/app/routes/admin.php
    
    # Vues WhatsApp
    sudo kubectl exec ${POD} -- mkdir -p /app/resources/views/admin/whatsapp_groups
    sudo kubectl cp /tmp/index.blade.php default/${POD}:/app/resources/views/admin/whatsapp_groups/index.blade.php
    sudo kubectl cp /tmp/create.blade.php default/${POD}:/app/resources/views/admin/whatsapp_groups/create.blade.php
    sudo kubectl cp /tmp/edit.blade.php default/${POD}:/app/resources/views/admin/whatsapp_groups/edit.blade.php
    sudo kubectl cp /tmp/nav.blade.php default/${POD}:/app/resources/views/admin/include/nav.blade.php
    
    # Nouvelles vues et Web Routes (Social & Marketplace Details)
    sudo kubectl exec ${POD} -- mkdir -p /app/resources/views/marketplace
    sudo kubectl cp /tmp/detail.blade.php default/${POD}:/app/resources/views/marketplace/detail.blade.php
    sudo kubectl cp /tmp/web.php default/${POD}:/app/routes/web.php
    sudo kubectl cp /tmp/HomeController.php default/${POD}:/app/app/Http/Controllers/HomeController.php
    sudo kubectl cp /tmp/PostToSocialMediaJob.php default/${POD}:/app/app/Jobs/PostToSocialMediaJob.php
    
    # Config
    sudo kubectl cp /tmp/services.php default/${POD}:/app/config/services.php
    
    # Nouvelles vues et contrôleurs Admin (APK Settings & Social Settings)
    sudo kubectl exec ${POD} -- mkdir -p /app/resources/views/admin/settings
    if [ -f /tmp/apks.blade.php ]; then
        sudo kubectl cp /tmp/apks.blade.php default/${POD}:/app/resources/views/admin/settings/apks.blade.php
    fi
    if [ -f /tmp/ApkSettingsController.php ]; then
        sudo kubectl cp /tmp/ApkSettingsController.php default/${POD}:/app/app/Http/Controllers/Admin/ApkSettingsController.php
    fi
    if [ -f /tmp/social.blade.php ]; then
        sudo kubectl cp /tmp/social.blade.php default/${POD}:/app/resources/views/admin/settings/social.blade.php
    fi
    if [ -f /tmp/SocialSettingsController.php ]; then
        sudo kubectl cp /tmp/SocialSettingsController.php default/${POD}:/app/app/Http/Controllers/Admin/SocialSettingsController.php
    fi
    
    if [ -f /tmp/SetEvolutionWebhookCommand.php ]; then
        sudo kubectl cp /tmp/SetEvolutionWebhookCommand.php default/${POD}:/app/app/Console/Commands/SetEvolutionWebhookCommand.php
    fi
    
    # APKs par défaut (s'ils ont été transférés)
    if [ -f /tmp/user_apk_default.apk ]; then
        sudo kubectl cp /tmp/user_apk_default.apk default/${POD}:/app/storage/app/public/user_apk_default.apk
    fi
    if [ -f /tmp/driver_apk_default.apk ]; then
        sudo kubectl cp /tmp/driver_apk_default.apk default/${POD}:/app/storage/app/public/driver_apk_default.apk
    fi
    
    # Migrations
    sudo kubectl cp /tmp/2026_06_30_081241_create_whatsapp_groups_table.php default/${POD}:/app/database/migrations/2026_06_30_081241_create_whatsapp_groups_table.php
    sudo kubectl cp /tmp/2026_06_30_081552_add_batch_processed_to_whatsapp_messages_table.php default/${POD}:/app/database/migrations/2026_06_30_081552_add_batch_processed_to_whatsapp_messages_table.php
    sudo kubectl cp /tmp/2026_06_30_233000_add_sub_category_to_marketplace_listings_table.php default/${POD}:/app/database/migrations/2026_06_30_233000_add_sub_category_to_marketplace_listings_table.php
    sudo kubectl cp /tmp/2026_07_19_151040_add_message_id_to_whatsapp_messages_table.php default/${POD}:/app/database/migrations/2026_07_19_151040_add_message_id_to_whatsapp_messages_table.php
    
    echo "=== Exécution des migrations sur $POD ==="
    sudo kubectl exec ${POD} -- php artisan migrate --force
    
    echo "=== Nettoyage du cache pour charger les nouvelles commandes ==="
    sudo kubectl exec ${POD} -- php artisan optimize:clear
    
    echo "=== Configuration du Webhook WhatsApp ==="
    sudo kubectl exec ${POD} -- php artisan evolution:set-webhook
done

# Sélection d'un pod principal pour les commandes artisan
MAIN_POD=$(echo "$LARAVEL_PODS" | head -n 1)

if [ -n "$MAIN_POD" ]; then
    echo "=== Exécution des migrations sur $MAIN_POD ==="
    sudo kubectl exec $MAIN_POD -- php /app/artisan migrate --force
    
    echo "=== Nettoyage des caches sur $MAIN_POD ==="
    sudo kubectl exec $MAIN_POD -- php /app/artisan route:clear
    sudo kubectl exec $MAIN_POD -- php /app/artisan optimize:clear
    
    # Redémarrage de la file d'attente sur les workers
    for WPOD in $WORKER_PODS; do
        echo "=== Redémarrage queue sur $WPOD ==="
        sudo kubectl exec $WPOD -- php /app/artisan queue:restart
    done
    
    echo "✅ Déploiement terminé avec succès !"
else
    echo "❌ Aucun pod Laravel trouvé pour exécuter artisan."
fi
