#!/bin/bash
set -e
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"
echo "=== Last 200 lines of container logs ==="
sudo k3s kubectl logs "$POD" --tail=200
