#!/bin/bash
echo "=== Start Deploying WA Listings Fix to All Pods ==="

LARAVEL_PODS=$(sudo kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
WORKER_PODS=$(sudo kubectl get pods -l app=laravel-worker --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
ALL_PODS="$LARAVEL_PODS $WORKER_PODS"

for POD in $ALL_PODS; do
    echo "Syncing files to pod: $POD"
    
    # Create directories if not exists
    sudo kubectl exec ${POD} -- mkdir -p /app/app/Http/Controllers/Admin
    sudo kubectl exec ${POD} -- mkdir -p /app/app/Models
    sudo kubectl exec ${POD} -- mkdir -p /app/resources/views/admin/whatsapp
    sudo kubectl exec ${POD} -- mkdir -p /app/resources/views/admin/marketplace/listings

    # Copy files
    sudo kubectl cp /tmp/WhatsappListingController.php default/${POD}:/app/app/Http/Controllers/Admin/WhatsappListingController.php
    sudo kubectl cp /tmp/MarketplaceListing.php default/${POD}:/app/app/Models/MarketplaceListing.php
    sudo kubectl cp /tmp/whatsapp_index.blade.php default/${POD}:/app/resources/views/admin/whatsapp/index.blade.php
    sudo kubectl cp /tmp/marketplace_index.blade.php default/${POD}:/app/resources/views/admin/marketplace/listings/index.blade.php

    # Clear cache inside each pod
    echo "Clearing cache inside $POD..."
    sudo kubectl exec ${POD} -- php artisan view:clear
    sudo kubectl exec ${POD} -- php artisan route:clear
    sudo kubectl exec ${POD} -- php artisan cache:clear
done

echo "=== Deployment Finished successfully! ==="
