#!/bin/bash
set -e
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"

# Deploy the fixed layout
sudo k3s kubectl cp /tmp/app.blade.php $POD:/app/resources/views/user/layout/app.blade.php
sudo k3s kubectl cp /tmp/index.blade.php $POD:/app/resources/views/index.blade.php

# Full cache clear
sudo k3s kubectl exec $POD -- php artisan optimize:clear
sudo k3s kubectl exec $POD -- php artisan view:clear

# Verify DOCTYPE is now correct
echo "=== Verifying DOCTYPE fix ==="
sudo k3s kubectl exec $POD -- head -3 /app/resources/views/user/layout/app.blade.php

echo "=== DEPLOYED + CACHE CLEARED ==="
