#!/bin/bash
set -e

POD1="laravel-deployment-8c59cf9c4-8rmr7"
POD2="laravel-deployment-8c59cf9c4-bbs49"

echo "=========================================="
echo " DEPLOIEMENT SUR LES 2 PODS"
echo "=========================================="

for POD in $POD1 $POD2; do
  echo ""
  echo ">>> Deploiement sur: $POD"

  sudo k3s kubectl cp /tmp/app.blade.php              $POD:/app/resources/views/user/layout/app.blade.php
  sudo k3s kubectl cp /tmp/marketing_layout.blade.php $POD:/app/resources/views/marketing/layout.blade.php
  sudo k3s kubectl cp /tmp/marketing_airport.blade.php $POD:/app/resources/views/marketing/airport.blade.php
  sudo k3s kubectl cp /tmp/marketplace_index.blade.php $POD:/app/resources/views/marketplace/index.blade.php
  sudo k3s kubectl cp /tmp/location.blade.php         $POD:/app/resources/views/location.blade.php
  sudo k3s kubectl cp /tmp/drive.blade.php            $POD:/app/resources/views/drive.blade.php
  sudo k3s kubectl cp /tmp/home.blade.php             $POD:/app/resources/views/home.blade.php
  sudo k3s kubectl cp /tmp/web.php                    $POD:/app/routes/web.php
  sudo k3s kubectl cp /tmp/marketing.php              $POD:/app/routes/marketing.php
  sudo k3s kubectl cp /tmp/style.css                  $POD:/app/public/asset/css/style.css
  sudo k3s kubectl cp /tmp/HomeController.php         $POD:/app/app/Http/Controllers/HomeController.php

  # Supprimer les anciennes pages legacy
  sudo k3s kubectl exec $POD -- rm -f /app/resources/views/airport.blade.php
  sudo k3s kubectl exec $POD -- rm -f /app/resources/views/index.blade.php

  # Vider tous les caches
  sudo k3s kubectl exec $POD -- php artisan optimize:clear
  sudo k3s kubectl exec $POD -- php artisan view:clear

  echo ">>> $POD : DONE ?"
done

echo ""
echo "=========================================="
echo " TOUS LES PODS MIS A JOUR !"
echo "=========================================="
