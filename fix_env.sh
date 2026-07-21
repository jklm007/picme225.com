#!/bin/bash
set -e

POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"

echo "--- Checking Laravel config ---"
sudo k3s kubectl exec "$POD" -- php artisan config:show database.default 2>/dev/null || true

echo "--- Checking if .env exists ---"
sudo k3s kubectl exec "$POD" -- ls -la /app/.env 2>&1 || echo "No .env file"

echo "--- Writing a temporary .env override to fix DB connection ---"
sudo k3s kubectl exec "$POD" -- bash -c 'cat > /app/.env << EOF
APP_NAME=PicMe225
APP_ENV=production
APP_KEY=base64:YOUR_KEY_PLACEHOLDER
APP_DEBUG=false
APP_URL=https://picme225.site

DB_CONNECTION=pgsql
DB_HOST=postgres-service
DB_PORT=5432
DB_DATABASE=picme_db
DB_USERNAME=picme_user
DB_PASSWORD=secret_password

REDIS_HOST=redis-service
REDIS_PORT=6379
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
EOF'

echo "--- Clearing config cache ---"
sudo k3s kubectl exec "$POD" -- php artisan config:clear

echo "--- Running migrations ---"
sudo k3s kubectl exec "$POD" -- php artisan migrate --force
