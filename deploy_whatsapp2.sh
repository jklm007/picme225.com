#!/bin/bash
# Deploy updated WhatsApp files to new worker pod + both Laravel pods
WORKER="laravel-worker-59bb9bcf79-4bdwg"
LARAVEL1="laravel-deployment-7779b9fb85-jfnrs"
LARAVEL2="laravel-deployment-7779b9fb85-lswws"

echo "=== Deploying to new worker pod: $WORKER ==="
kubectl cp /tmp/WhatsAppWebhookController.php default/${WORKER}:/app/app/Http/Controllers/Api/WhatsAppWebhookController.php
kubectl cp /tmp/WhatsappMessage.php default/${WORKER}:/app/app/Models/WhatsappMessage.php
kubectl cp /tmp/WhatsappUser.php default/${WORKER}:/app/app/Models/WhatsappUser.php
kubectl cp /tmp/ProcessWhatsappMessageJob.php default/${WORKER}:/app/app/Jobs/ProcessWhatsappMessageJob.php
kubectl cp /tmp/2026_06_29_095400_update_marketplace_listings_status_check.php default/${WORKER}:/app/database/migrations/2026_06_29_095400_update_marketplace_listings_status_check.php
echo "Done $WORKER"

echo ""
echo "=== Redeploying updated Job to Laravel API pods ==="
kubectl cp /tmp/ProcessWhatsappMessageJob.php default/${LARAVEL1}:/app/app/Jobs/ProcessWhatsappMessageJob.php
kubectl cp /tmp/ProcessWhatsappMessageJob.php default/${LARAVEL2}:/app/app/Jobs/ProcessWhatsappMessageJob.php

echo ""
echo "=== Final test: Confirm webhook route is registered ==="
kubectl exec ${LARAVEL1} -- php /app/artisan route:list 2>&1 | grep -i whatsapp || echo "Route not found - checking api.php"
kubectl exec ${LARAVEL1} -- php /app/artisan route:clear
kubectl exec ${LARAVEL1} -- php /app/artisan optimize:clear
echo "Done"
