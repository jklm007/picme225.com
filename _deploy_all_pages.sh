#!/bin/bash
set -e
for POD in laravel-deployment-8c59cf9c4-8rmr7 laravel-deployment-8c59cf9c4-bbs49; do
  echo ">>> Deploying to: $POD"
  sudo k3s kubectl cp /tmp/airport.blade.php        $POD:/app/resources/views/marketing/airport.blade.php
  sudo k3s kubectl cp /tmp/location.blade.php       $POD:/app/resources/views/location.blade.php
  sudo k3s kubectl cp /tmp/marketplace.blade.php    $POD:/app/resources/views/marketplace/index.blade.php
  sudo k3s kubectl cp /tmp/home.blade.php           $POD:/app/resources/views/home.blade.php
  sudo k3s kubectl exec $POD -- php artisan view:clear
  echo "--- Done: $POD ---"
done
echo "=== ALL COMPLETE ==="
