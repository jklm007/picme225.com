#!/bin/bash
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "=== Pod: $POD ==="

# Copy the fixed migration file to the pod
echo "--- Copying fixed migration to pod ---"
sudo k3s kubectl cp /tmp/2025_01_19_155210_update_service_types_calculator_enum.php "$POD":/app/database/migrations/2025_01_19_155210_update_service_types_calculator_enum.php

echo "--- Running migrations ---"
sudo k3s kubectl exec "$POD" -- php /app/artisan migrate --force

echo "=== DONE ==="
sudo k3s kubectl exec "$POD" -- php /app/artisan migrate:status | tail -30
