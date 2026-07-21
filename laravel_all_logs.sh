#!/bin/bash
set -e
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"
echo "=== Listing /app/storage/logs ==="
sudo k3s kubectl exec "$POD" -- ls -la /app/storage/logs/ || true
echo "=== Nginx error log ==="
sudo k3s kubectl exec "$POD" -- tail -n 30 /var/log/nginx/error.log || echo "No nginx error log"
echo "=== Nginx access log ==="
sudo k3s kubectl exec "$POD" -- tail -n 10 /var/log/nginx/access.log || echo "No nginx access log"
