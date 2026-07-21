#!/bin/bash
set -e

echo "=== Rebuilding Laravel image WITHOUT config cache ==="
sudo docker build -t picme225-laravel:latest -f Dockerfile.laravel .
echo "=== Saving and importing into K3s ==="
sudo docker save picme225-laravel:latest > laravel.tar
sudo docker rmi picme225-laravel:latest
sudo k3s ctr -n k8s.io images import laravel.tar
rm laravel.tar

echo "=== Restarting Laravel pods ==="
sudo k3s kubectl rollout restart deployment/laravel-deployment
sleep 15

echo "=== Running migrations ==="
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"
sudo k3s kubectl exec "$POD" -- php artisan migrate --force

echo "=== DONE ==="
sudo k3s kubectl get pods
