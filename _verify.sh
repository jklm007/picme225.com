#!/bin/bash
set -e
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"

# Verify files are deployed
echo "=== Checking app.blade.php on pod ==="
sudo k3s kubectl exec $POD -- head -15 /app/resources/views/user/layout/app.blade.php

# Clear all Laravel caches
echo "=== Clearing all caches ==="
sudo k3s kubectl exec $POD -- php artisan view:clear
sudo k3s kubectl exec $POD -- php artisan cache:clear  
sudo k3s kubectl exec $POD -- php artisan config:clear
sudo k3s kubectl exec $POD -- php artisan route:clear
sudo k3s kubectl exec $POD -- php artisan optimize:clear 2>/dev/null || true
echo "=== Done ==="
