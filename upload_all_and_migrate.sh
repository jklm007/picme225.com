#!/bin/bash
set -e
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "=== Copying ALL fixed migrations to Pod: $POD ==="

# Copy all migration files at once via tar
sudo k3s kubectl exec "$POD" -- bash -c "rm -rf /tmp/migrations_backup && mkdir -p /tmp/migrations_backup && cp -r /app/database/migrations/* /tmp/migrations_backup/"

echo "--- Running migrations ---"
sudo k3s kubectl exec "$POD" -- php /app/artisan migrate --force

echo "=== Migration Result ==="
