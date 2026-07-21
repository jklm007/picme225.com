#!/bin/bash
set -e

# Get all pod names matching app=laravel
PODS=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[*].metadata.name}')

for POD in $PODS; do
    echo "=== Copying migrations to Pod: $POD ==="
    # First, copy to a temp dir in the pod
    sudo k3s kubectl cp /tmp/migrations "$POD":/app/database/migrations_new
    
    # Overwrite the files inside the pod
    sudo k3s kubectl exec "$POD" -- bash -c "
        cp -rf /tmp/migrations/* /app/database/migrations/ 2>/dev/null || true
        cp -f /app/database/migrations_new/*.php /app/database/migrations/ 2>/dev/null || true
        rm -rf /app/database/migrations_new
        echo 'Copy to $POD done.'
    "
done

echo "=== Sync complete ==="
