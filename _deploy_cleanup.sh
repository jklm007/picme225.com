#!/bin/bash
set -e
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"

# Copy updated routes, views, and styles
sudo k3s kubectl cp /tmp/web.php   $POD:/app/routes/web.php
sudo k3s kubectl cp /tmp/app.blade.php $POD:/app/resources/views/user/layout/app.blade.php
sudo k3s kubectl cp /tmp/style.css $POD:/app/public/asset/css/style.css

# Delete legacy views inside the pod completely
echo "=== Deleting legacy view files from pod ==="
sudo k3s kubectl exec $POD -- rm -f /app/resources/views/index.blade.php
sudo k3s kubectl exec $POD -- rm -f /app/resources/views/airport.blade.php

# Clear all Laravel caches
echo "=== Clearing all caches ==="
sudo k3s kubectl exec $POD -- php artisan optimize:clear
sudo k3s kubectl exec $POD -- php artisan view:clear

echo "=== DEPLOYMENT AND CLEANUP COMPLETE ==="
