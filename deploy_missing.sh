#!/bin/bash
set -e
sudo tar -xzf all_missing_files.tar.gz

WEB_PODS=$(sudo k3s kubectl get pods -o jsonpath='{.items[*].metadata.name}' | tr ' ' '\n' | grep laravel-deployment)
WORKER_PODS=$(sudo k3s kubectl get pods -o jsonpath='{.items[*].metadata.name}' | tr ' ' '\n' | grep laravel-worker)

for POD in $WEB_PODS $WORKER_PODS; do
    echo "=== Syncing to Pod: $POD ==="

    # Marketing routes
    sudo k3s kubectl cp routes/marketing.php "$POD":/app/routes/marketing.php

    # Marketing controllers
    sudo k3s kubectl exec "$POD" -- mkdir -p /app/app/Http/Controllers/Marketing
    sudo k3s kubectl cp app/Http/Controllers/Marketing/MarketingController.php "$POD":/app/app/Http/Controllers/Marketing/MarketingController.php
    sudo k3s kubectl cp app/Http/Controllers/Marketing/BlogController.php "$POD":/app/app/Http/Controllers/Marketing/BlogController.php

    # WhatsAppQrController (manquant)
    sudo k3s kubectl cp app/Http/Controllers/WhatsAppQrController.php "$POD":/app/app/Http/Controllers/WhatsAppQrController.php

    # Marketplace controller + view
    sudo k3s kubectl cp app/Http/Controllers/MarketplaceController.php "$POD":/app/app/Http/Controllers/MarketplaceController.php
    sudo k3s kubectl exec "$POD" -- mkdir -p /app/resources/views/marketplace
    sudo k3s kubectl cp resources/views/marketplace/index.blade.php "$POD":/app/resources/views/marketplace/index.blade.php

    # Marketing views
    sudo k3s kubectl exec "$POD" -- mkdir -p /app/resources/views/marketing
    sudo k3s kubectl cp resources/views/marketing/airport.blade.php "$POD":/app/resources/views/marketing/airport.blade.php
    sudo k3s kubectl cp resources/views/marketing/layout.blade.php "$POD":/app/resources/views/marketing/layout.blade.php
    sudo k3s kubectl cp resources/views/marketing/sitemap.blade.php "$POD":/app/resources/views/marketing/sitemap.blade.php

    # Clear all caches
    sudo k3s kubectl exec "$POD" -- php /app/artisan view:clear || true
    sudo k3s kubectl exec "$POD" -- php /app/artisan route:clear || true
    sudo k3s kubectl exec "$POD" -- php /app/artisan config:clear || true
    sudo k3s kubectl exec "$POD" -- php /app/artisan cache:clear || true

    echo "=== Done: $POD ==="
done
echo "All pods synced successfully!"
