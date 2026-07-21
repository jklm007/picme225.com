#!/bin/bash
# Sync all modified files to all running Laravel pods

PODS_WEB="laravel-deployment-787568f8f4-hxdsz laravel-deployment-787568f8f4-rmrkd"
POD_WORKER="laravel-worker-59bb9bcf79-hpt65"
ALL_PODS="$PODS_WEB $POD_WORKER"

FILES=(
  "/tmp/ProcessWhatsappBatchJob.php:/app/app/Jobs/ProcessWhatsappBatchJob.php:all"
  "/tmp/edit.blade.php:/app/resources/views/admin/marketplace/listings/edit.blade.php:web"
  "/tmp/AdminMarketplaceListingController.php:/app/app/Http/Controllers/Admin/MarketplaceListingController.php:web"
)

echo "=== Syncing files to pods ==="
for entry in "${FILES[@]}"; do
  SRC="${entry%%:*}"
  REST="${entry#*:}"
  DEST="${REST%%:*}"
  TARGET="${REST##*:}"
  
  if [ "$TARGET" = "all" ]; then
    TARGETS="$ALL_PODS"
  else
    TARGETS="$PODS_WEB"
  fi
  
  for POD in $TARGETS; do
    echo "  -> $POD : $DEST"
    sudo k3s kubectl cp "$SRC" "default/$POD:$DEST" && echo "     OK" || echo "     FAILED"
  done
done

# Clear view cache on web pods
for POD in $PODS_WEB; do
  echo "=== Clearing cache on $POD ==="
  sudo k3s kubectl exec $POD -- php artisan view:clear
  sudo k3s kubectl exec $POD -- php artisan config:clear
  sudo k3s kubectl exec $POD -- php artisan cache:clear
done

# Restart queue worker
echo "=== Restarting queue worker ==="
sudo k3s kubectl exec $POD_WORKER -- php artisan queue:restart

echo "=== All pods synced! ==="
