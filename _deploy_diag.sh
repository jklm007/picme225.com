#!/bin/bash
set -e
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"

# Copy updated AppServiceProvider.php and app.blade.php
sudo k3s kubectl cp /tmp/AppServiceProvider.php $POD:/app/app/Providers/AppServiceProvider.php
sudo k3s kubectl cp /tmp/app.blade.php          $POD:/app/resources/views/user/layout/app.blade.php

# Clear all Laravel caches
sudo k3s kubectl exec $POD -- php artisan optimize:clear
sudo k3s kubectl exec $POD -- php artisan view:clear

echo "=== DEPLOYED DIAGNOSTIC FIXES SUCCESSFULLY ==="
