#!/bin/bash
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"
sudo k3s kubectl cp /tmp/app.blade.php $POD:/app/resources/views/user/layout/app.blade.php
sudo k3s kubectl exec $POD -- php artisan view:clear
echo "=== DONE ==="
