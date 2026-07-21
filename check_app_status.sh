#!/bin/bash
set -e
echo "=== K3s Pods ==="
sudo k3s kubectl get pods -n default
echo ""
echo "=== Laravel Pod Logs ==="
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"
sudo k3s kubectl logs "$POD" --tail=30
