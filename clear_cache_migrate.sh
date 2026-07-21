#!/bin/bash
set -e

POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "=== Using Pod: $POD ==="

echo "--- Removing compiled config cache from bootstrap/cache ---"
sudo k3s kubectl exec "$POD" -- bash -c "rm -f /app/bootstrap/cache/config.php /app/bootstrap/cache/routes-v7.php"

echo "--- Removing any stale .env (if present) ---"
sudo k3s kubectl exec "$POD" -- bash -c "rm -f /app/.env"

echo "--- Verifying DB env vars ---"
sudo k3s kubectl exec "$POD" -- bash -c "echo DB_CONNECTION=\$DB_CONNECTION; echo DB_HOST=\$DB_HOST; echo DB_PORT=\$DB_PORT; echo DB_DATABASE=\$DB_DATABASE"

echo "--- Verifying resolved config (after cache cleared) ---"
sudo k3s kubectl exec "$POD" -- php artisan config:show database.default

echo "--- Running migrations ---"
sudo k3s kubectl exec "$POD" -- php artisan migrate --force
