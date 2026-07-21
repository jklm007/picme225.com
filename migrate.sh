#!/bin/bash
set -e

echo "=== POD STATUS ==="
sudo k3s kubectl get pods

echo ""
echo "=== DB ENV IN LARAVEL POD ==="
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"
sudo k3s kubectl exec "$POD" -- env | grep -E "^DB_|^APP_"

echo ""
echo "=== RUNNING MIGRATIONS ==="
sudo k3s kubectl exec "$POD" -- php artisan migrate --force
