#!/bin/bash
set -e

POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "=== Pod: $POD ==="

# Verify .env was written
echo "--- .env file content ---"
sudo k3s kubectl exec "$POD" -- cat /app/.env

echo "--- bootstrap/cache/ contents ---"
sudo k3s kubectl exec "$POD" -- ls -la /app/bootstrap/cache/ || true

echo "--- Force delete ALL cache ---"
sudo k3s kubectl exec "$POD" -- bash -c "rm -f /app/bootstrap/cache/*.php"

echo "--- Run migrations with env vars inline (bypasses Laravel env() entirely) ---"
sudo k3s kubectl exec "$POD" -- bash -c 'export DB_CONNECTION=pgsql; export DB_HOST=postgres-service; export DB_PORT=5432; export DB_DATABASE=picme_db; export DB_USERNAME=picme_user; export DB_PASSWORD=secret_password; export APP_KEY=base64:YWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXoxMjM0NTY=; export APP_ENV=production; unset DB_URL; php /app/artisan migrate --force'

echo "=== DONE ==="
