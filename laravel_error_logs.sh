#!/bin/bash
set -e
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"
sudo k3s kubectl exec "$POD" -- tail -n 50 /app/storage/logs/laravel.log 2>/dev/null || echo "No laravel.log found or empty."
