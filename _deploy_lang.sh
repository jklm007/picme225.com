#!/bin/bash
set -e
POD1="laravel-deployment-8c59cf9c4-8rmr7"
POD2="laravel-deployment-8c59cf9c4-bbs49"

for POD in $POD1 $POD2; do
  echo ">>> Pod: $POD"
  sudo k3s kubectl cp /tmp/home_fr.php $POD:/app/resources/lang/fr/home.php
  sudo k3s kubectl cp /tmp/home_en.php $POD:/app/resources/lang/en/home.php

  # Vider le cache de config et de traductions
  sudo k3s kubectl exec $POD -- php artisan config:clear
  sudo k3s kubectl exec $POD -- php artisan cache:clear
  sudo k3s kubectl exec $POD -- php artisan view:clear

  # Verifier que les cles existent sur le pod
  echo "--- Verification cles hero sur $POD ---"
  sudo k3s kubectl exec $POD -- php -r "
    \$fr = include '/app/resources/lang/fr/home.php';
    echo 'hero_badge: ' . (\$fr['hero_badge'] ?? 'MISSING') . PHP_EOL;
    echo 'hero_title_part1: ' . (\$fr['hero_title_part1'] ?? 'MISSING') . PHP_EOL;
    echo 'hero_subtitle: ' . (\$fr['hero_subtitle'] ?? 'MISSING') . PHP_EOL;
  "
  echo ">>> $POD DONE"
done

echo "=== LANGUES DEPLOYEES SUR LES 2 PODS ==="
