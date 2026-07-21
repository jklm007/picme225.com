#!/bin/bash
# deploy_phase3.sh - Phase 2+3 : Marketplace + Moderation
set -e
echo "=== DEPLOIEMENT PHASE 2+3 ==="

# Auto-detect kubectl
KCL="kubectl"
command -v kubectl > /dev/null 2>&1 || KCL="sudo k3s kubectl"

LARAVEL_PODS=$($KCL get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
WORKER_PODS=$($KCL get pods -l app=laravel-worker --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers 2>/dev/null || echo "")
ALL_PODS="$LARAVEL_PODS $WORKER_PODS"
echo "Pods: $ALL_PODS"

for POD in $ALL_PODS; do
  [ -z "$POD" ] && continue
  echo ">>> Pod: $POD"
  
  $KCL cp /tmp/p3/WhatsAppWebhookController.php  default/${POD}:/app/app/Http/Controllers/Api/WhatsAppWebhookController.php
  $KCL cp /tmp/p3/WhatsappListingController.php   default/${POD}:/app/app/Http/Controllers/Admin/WhatsappListingController.php
  $KCL cp /tmp/p3/HomeController.php              default/${POD}:/app/app/Http/Controllers/HomeController.php
  $KCL cp /tmp/p3/ProcessWhatsappBatchJob.php     default/${POD}:/app/app/Jobs/ProcessWhatsappBatchJob.php
  $KCL cp /tmp/p3/admin.php                       default/${POD}:/app/routes/admin.php
  $KCL cp /tmp/p3/marketplace_index.blade.php     default/${POD}:/app/resources/views/marketplace/index.blade.php
  $KCL cp /tmp/p3/marketplace_detail.blade.php    default/${POD}:/app/resources/views/marketplace/detail.blade.php
  $KCL cp /tmp/p3/whatsapp_admin_index.blade.php  default/${POD}:/app/resources/views/admin/whatsapp/index.blade.php
  $KCL cp /tmp/p3/2026_07_03_112022_add_is_blacklisted_to_whatsapp_users_table.php \
          default/${POD}:/app/database/migrations/2026_07_03_112022_add_is_blacklisted_to_whatsapp_users_table.php

  $KCL exec ${POD} -- php /app/artisan optimize:clear
  $KCL exec ${POD} -- php /app/artisan view:clear
  echo ">>> $POD OK"
done

FIRST_POD=$(echo $LARAVEL_PODS | awk "{print $1}")
echo ">>> Migration sur: $FIRST_POD"
$KCL exec ${FIRST_POD} -- php /app/artisan migrate --force
echo ">>> Migration OK"
echo "=== DONE ==="
