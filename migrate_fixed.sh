#!/bin/bash
set -e
cp /tmp/2026_06_24_170000_create_unified_partners_tables.php /tmp/migrations/2026_06_24_170000_create_unified_partners_tables.php
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"
sudo k3s kubectl cp /tmp/2026_06_24_170000_create_unified_partners_tables.php "$POD":/app/database/migrations/2026_06_24_170000_create_unified_partners_tables.php
echo "Copied migration to pod."
sudo k3s kubectl exec "$POD" -- php /app/artisan migrate --force
