#!/bin/bash
set -e

PODS=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[*].metadata.name}')

for POD in $PODS; do
    echo "=== Syncing all fixes to Pod: $POD ==="
    
    # Views
    sudo k3s kubectl cp /tmp/app.blade.php "$POD":/app/resources/views/user/layout/app.blade.php
    sudo k3s kubectl cp /tmp/home.blade.php "$POD":/app/resources/views/home.blade.php
    
    # Routes
    sudo k3s kubectl cp /tmp/web.php "$POD":/app/routes/web.php
    
    # Language files
    sudo k3s kubectl cp /tmp/en_home.php "$POD":/app/resources/lang/en/home.php
    sudo k3s kubectl cp /tmp/fr_home.php "$POD":/app/resources/lang/fr/home.php
    
    # Middleware
    sudo k3s kubectl cp /tmp/LandingLanguageMiddleware.php "$POD":/app/app/Http/Middleware/LandingLanguageMiddleware.php
    
    # Controllers
    sudo k3s kubectl cp /tmp/RegisterController.php "$POD":/app/app/Http/Controllers/Auth/RegisterController.php
    sudo k3s kubectl cp /tmp/HomeController.php "$POD":/app/app/Http/Controllers/HomeController.php
    
    # Logo (full version with transport icons)
    sudo k3s kubectl exec "$POD" -- cp /app/public/logo.png /app/public/asset/logo.png 2>/dev/null || true
    
    echo "Copy to $POD done."
    
    sudo k3s kubectl exec "$POD" -- php /app/artisan view:clear || true
    sudo k3s kubectl exec "$POD" -- php /app/artisan config:clear || true
    sudo k3s kubectl exec "$POD" -- php /app/artisan route:clear || true
    echo "Cache cleared on $POD."
done

echo "=== All fixes sync complete ==="
