#!/bin/bash
PODS="laravel-deployment-7779b9fb85-jfnrs laravel-deployment-7779b9fb85-lswws laravel-worker-59669d4dc5-z6gtn"

for POD in $PODS; do
  echo "=== Copying to $POD ==="
  kubectl cp /tmp/WhatsAppWebhookController.php default/${POD}:/app/app/Http/Controllers/Api/WhatsAppWebhookController.php
  kubectl cp /tmp/WhatsappMessage.php default/${POD}:/app/app/Models/WhatsappMessage.php
  kubectl cp /tmp/WhatsappUser.php default/${POD}:/app/app/Models/WhatsappUser.php
  kubectl cp /tmp/ProcessWhatsappMessageJob.php default/${POD}:/app/app/Jobs/ProcessWhatsappMessageJob.php
  echo "Done $POD"
done

echo ""
echo "=== Clearing route cache on laravel-deployment-7779b9fb85-jfnrs ==="
kubectl exec laravel-deployment-7779b9fb85-jfnrs -- php /app/artisan route:clear
kubectl exec laravel-deployment-7779b9fb85-jfnrs -- php /app/artisan optimize:clear

echo ""
echo "=== Verifying controller exists ==="
kubectl exec laravel-deployment-7779b9fb85-jfnrs -- ls /app/app/Http/Controllers/Api/ | grep Whats
