#!/bin/bash
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "=== Pod: $POD ==="

echo "--- Copying all fixed migrations to pod ---"
sudo k3s kubectl cp /tmp/migrations "$POD":/app/database/migrations_new

echo "--- Replacing migrations directory ---"
sudo k3s kubectl exec "$POD" -- bash -c "
cp -rf /tmp/migrations/* /app/database/migrations/ 2>/dev/null || true
# Also try from the pod's /tmp if kubectl cp put them there
ls /app/database/migrations_new/ | head -5
cp -f /app/database/migrations_new/*.php /app/database/migrations/ 2>/dev/null || true
rm -rf /app/database/migrations_new
echo 'Copy done'
"

echo "--- Running migrations ---"
sudo k3s kubectl exec "$POD" -- php /app/artisan migrate --force 2>&1

echo "=== DONE ==="
