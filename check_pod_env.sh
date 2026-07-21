#!/bin/bash
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"
echo "=== .env file in pod ==="
sudo k3s kubectl exec "$POD" -- head -n 15 /app/.env
echo "=== APP_KEY via artisan tinker ==="
sudo k3s kubectl exec "$POD" -- php /app/artisan tinker --execute="echo env('APP_KEY') . \"\n\"; echo config('app.key') . \"\n\";"
