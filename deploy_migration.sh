#!/bin/bash
PODS="laravel-deployment-7779b9fb85-jfnrs laravel-deployment-7779b9fb85-lswws laravel-worker-59669d4dc5-z6gtn"

for POD in $PODS; do
  echo "=== Copying migration to $POD ==="
  kubectl cp /tmp/2026_06_29_095400_update_marketplace_listings_status_check.php default/${POD}:/app/database/migrations/2026_06_29_095400_update_marketplace_listings_status_check.php
  echo "Done $POD"
done

echo ""
echo "=== Running Migrations ==="
kubectl exec laravel-deployment-7779b9fb85-jfnrs -- php /app/artisan migrate --force
