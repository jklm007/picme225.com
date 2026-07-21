#!/bin/bash
set -e
POD1="laravel-deployment-8c59cf9c4-8rmr7"
POD2="laravel-deployment-8c59cf9c4-bbs49"

for POD in $POD1 $POD2; do
  echo ">>> Deploiement des images sur: $POD"
  sudo k3s kubectl cp /tmp/logo.png $POD:/app/public/logo.png
  sudo k3s kubectl cp /tmp/asset_logo.png $POD:/app/public/asset/logo.png
  sudo k3s kubectl cp /tmp/favicon.ico $POD:/app/public/favicon.ico
  sudo k3s kubectl cp /tmp/favicon.png $POD:/app/public/favicon.png
done
echo "=== COMPLETE ==="
