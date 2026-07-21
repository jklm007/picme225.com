#!/bin/bash
set -e
for POD in laravel-deployment-8c59cf9c4-8rmr7 laravel-deployment-8c59cf9c4-bbs49; do
  echo ">>> Fixing storage symlink on: $POD"
  sudo k3s kubectl exec $POD -- bash -c '
    cd /app
    # Remove broken symlink if exists
    rm -f public/storage
    # Create symlink: public/storage -> storage/app/public
    ln -sf /app/storage/app/public /app/public/storage
    echo "Symlink created: $(ls -la public/storage)"
    # Set correct permissions
    chmod -R 775 storage/
    chmod -R 775 public/uploads/
    echo "Permissions fixed."
    # Re-run artisan storage:link safely  
    php artisan storage:link 2>&1 || true
  '
done
echo "=== DONE ==="
