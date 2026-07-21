#!/bin/bash
set -e
for POD in laravel-deployment-8c59cf9c4-8rmr7 laravel-deployment-8c59cf9c4-bbs49; do
  echo ">>> Deploying application.blade.php to: $POD"
  sudo k3s kubectl cp /tmp/application.blade.php $POD:/app/resources/views/admin/settings/application.blade.php
  sudo k3s kubectl exec $POD -- php artisan view:clear
done
echo "=== DONE ==="
