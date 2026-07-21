#!/bin/bash
set -e
POD1="laravel-deployment-8c59cf9c4-8rmr7"
POD2="laravel-deployment-8c59cf9c4-bbs49"

for POD in $POD1 $POD2; do
  echo ">>> Deploiement sur: $POD"
  sudo k3s kubectl cp /tmp/app.blade.php $POD:/app/resources/views/user/layout/app.blade.php
  sudo k3s kubectl cp /tmp/home.blade.php $POD:/app/resources/views/home.blade.php
  sudo k3s kubectl exec $POD -- php artisan view:clear
done
echo "=== COMPLETE ==="
