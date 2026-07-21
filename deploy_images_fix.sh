#!/bin/bash
PODS=$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=':metadata.name' --no-headers)
WORKERS=$(sudo k3s kubectl get pods -l app=laravel-worker --field-selector=status.phase=Running -o custom-columns=':metadata.name' --no-headers)

for POD in $PODS $WORKERS; do
    if [ ! -z "$POD" ]; then
        echo "Applying to $POD..."
        sudo k3s kubectl cp /tmp/ProcessWhatsappBatchJob.php default/${POD}:/app/app/Jobs/ProcessWhatsappBatchJob.php
        sudo k3s kubectl cp /tmp/MarketplaceListing.php default/${POD}:/app/app/Models/MarketplaceListing.php
        
        sudo k3s kubectl exec ${POD} -- mkdir -p /app/resources/views/admin/whatsapp
        sudo k3s kubectl cp /tmp/whatsapp_index.blade.php default/${POD}:/app/resources/views/admin/whatsapp/index.blade.php
        
        sudo k3s kubectl exec ${POD} -- mkdir -p /app/resources/views/admin/marketplace/listings
        sudo k3s kubectl cp /tmp/marketplace_index.blade.php default/${POD}:/app/resources/views/admin/marketplace/listings/index.blade.php
        
        sudo k3s kubectl exec ${POD} -- php artisan optimize:clear
        sudo k3s kubectl exec ${POD} -- php artisan view:clear
        sudo k3s kubectl exec ${POD} -- php artisan queue:restart
    fi
done
echo "Done updating pods."
