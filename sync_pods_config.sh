#!/bin/bash
set -e

PODS=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[*].metadata.name}')

for POD in $PODS; do
    echo "=== Copying config/database.php to Pod: $POD ==="
    sudo k3s kubectl cp /tmp/database.php "$POD":/app/config/database.php
    echo "Copy to $POD done."
    
    sudo k3s kubectl exec "$POD" -- php /app/artisan config:clear || true
    echo "Config cache cleared on $POD."
done

echo "=== Config sync complete ==="
