#!/bin/bash
set -e
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "==> Pod: $POD"

sudo k3s kubectl cp /tmp/app.blade.php              $POD:/app/resources/views/user/layout/app.blade.php
sudo k3s kubectl cp /tmp/index.blade.php            $POD:/app/resources/views/index.blade.php
sudo k3s kubectl cp /tmp/airport.blade.php          $POD:/app/resources/views/marketing/airport.blade.php
sudo k3s kubectl cp /tmp/marketplace_index.blade.php $POD:/app/resources/views/marketplace/index.blade.php
sudo k3s kubectl cp /tmp/location.blade.php         $POD:/app/resources/views/location.blade.php
sudo k3s kubectl cp /tmp/drive.blade.php            $POD:/app/resources/views/drive.blade.php

echo "==> Clearing view cache..."
sudo k3s kubectl exec $POD -- php artisan view:clear 2>/dev/null || true
sudo k3s kubectl exec $POD -- php artisan cache:clear 2>/dev/null || true
echo "==> DONE! All pages deployed with PicMe225 Design System."
