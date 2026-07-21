#!/bin/bash
set -e
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"

# Copy files into pod
sudo k3s kubectl cp /tmp/app.blade.php $POD:/app/resources/views/user/layout/app.blade.php
sudo k3s kubectl cp /tmp/style.css     $POD:/app/public/asset/css/style.css

# Clear Laravel caches
sudo k3s kubectl exec $POD -- php artisan optimize:clear
sudo k3s kubectl exec $POD -- php artisan view:clear

echo "=== DEPLOYED STYLE.CSS AND APP.BLADE.PHP SUCCESSFULLY ==="
