#!/bin/bash
# Check if PHP-FPM can see K8s env vars
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"
echo "--- Shell sees these DB vars ---"
sudo k3s kubectl exec "$POD" -- bash -c 'echo "DB_HOST=$DB_HOST DB_CONNECTION=$DB_CONNECTION DB_DATABASE=$DB_DATABASE"'
echo "--- PHP getenv sees ---"
sudo k3s kubectl exec "$POD" -- php -r 'echo "DB_HOST=" . getenv("DB_HOST") . " DB_CONNECTION=" . getenv("DB_CONNECTION") . "\n";'
