#!/bin/bash
set -e
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "=== Pod: $POD ==="

sudo k3s kubectl cp /tmp/marketing_layout.blade.php $POD:/app/resources/views/marketing/layout.blade.php
sudo k3s kubectl cp /tmp/app.blade.php $POD:/app/resources/views/user/layout/app.blade.php

echo "=== Clearing caches ==="
sudo k3s kubectl exec $POD -- php artisan view:clear
sudo k3s kubectl exec $POD -- php artisan route:clear
sudo k3s kubectl exec $POD -- php artisan cache:clear

echo "=== DEPLOYMENT COMPLETE ==="
