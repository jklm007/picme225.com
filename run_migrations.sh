#!/bin/bash
POD=$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"
sudo k3s kubectl cp /tmp/migrations_sync.tar.gz $POD:/tmp/migrations_sync.tar.gz
sudo k3s kubectl exec $POD -- bash -c "mkdir -p /tmp/migs && tar -xzf /tmp/migrations_sync.tar.gz -C /tmp/migs && cp -rf /tmp/migs/* /app/database/migrations/ && rm -rf /tmp/migs"
echo "Running php artisan migrate --force..."
sudo k3s kubectl exec $POD -- php /app/artisan migrate --force
