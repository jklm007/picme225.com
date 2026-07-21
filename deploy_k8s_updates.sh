#!/bin/bash
set -e
echo "=== Applying updated laravel.yaml deployment to cluster ==="
sudo k3s kubectl apply -f /tmp/laravel.yaml
echo "=== Deployment applied successfully ==="
