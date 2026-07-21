#!/bin/bash
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "=== Pod: $POD ==="

# Copy fixed migration
sudo k3s kubectl cp /tmp/2025_01_19_155210_update_service_types_calculator_enum.php "$POD":/app/database/migrations/2025_01_19_155210_update_service_types_calculator_enum.php

# Rollback the last 2 failed migrations to clean state
echo "--- Rolling back the failed migration ---"
sudo k3s kubectl exec "$POD" -- php /app/artisan migrate:rollback --step=1 2>&1 || true

echo "--- Running migrations again ---"
sudo k3s kubectl exec "$POD" -- php /app/artisan migrate --force

echo "=== DONE ==="
