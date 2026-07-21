#!/bin/bash
set -e

POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "=== Pod: $POD ==="

# Verify PHP can see env vars
echo "--- PHP getenv DB_HOST ---"
sudo k3s kubectl exec "$POD" -- php -r 'echo getenv("DB_HOST") . "\n";'

# Write a minimal .env with the correct values
echo "--- Writing .env file ---"
sudo k3s kubectl exec "$POD" -- bash -c 'printf "APP_NAME=PicMe225\nAPP_ENV=production\nAPP_KEY=base64:YWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXoxMjM0NTY=\nAPP_DEBUG=false\nAPP_URL=https://picme225.site\n\nDB_CONNECTION=pgsql\nDB_HOST=postgres-service\nDB_PORT=5432\nDB_DATABASE=picme_db\nDB_USERNAME=picme_user\nDB_PASSWORD=secret_password\n\nREDIS_HOST=redis-service\nREDIS_PORT=6379\nCACHE_DRIVER=redis\nQUEUE_CONNECTION=redis\nSESSION_DRIVER=redis\n" > /app/.env'

# Clear all caches
echo "--- Clearing caches ---"
sudo k3s kubectl exec "$POD" -- bash -c 'rm -f /app/bootstrap/cache/config.php /app/bootstrap/cache/routes-v7.php /app/bootstrap/cache/services.php'
sudo k3s kubectl exec "$POD" -- php artisan config:clear
sudo k3s kubectl exec "$POD" -- php artisan cache:clear

# Verify .env is being read
echo "--- Verifying DB_HOST from Laravel env() ---"
sudo k3s kubectl exec "$POD" -- php artisan tinker --execute='echo env("DB_HOST");'

# Migrate
echo "--- Running migrations ---"
sudo k3s kubectl exec "$POD" -- php artisan migrate --force

echo "=== DONE ==="
