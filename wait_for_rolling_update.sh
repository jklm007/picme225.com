#!/bin/bash
set -e
echo "=== Waiting for rollout status ==="
sudo k3s kubectl rollout status deployment/laravel-deployment --timeout=120s
echo ""
echo "=== K3s Pods ==="
sudo k3s kubectl get pods -l app=laravel
