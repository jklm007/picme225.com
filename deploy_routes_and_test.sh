#!/bin/bash
# Deploy updated routes/api.php to all pods and clear route cache
LARAVEL1="laravel-deployment-7779b9fb85-jfnrs"
LARAVEL2="laravel-deployment-7779b9fb85-lswws"
WORKER="laravel-worker-59bb9bcf79-4bdwg"

echo "=== Copying routes/api.php to all pods ==="
kubectl cp /tmp/api.php default/${LARAVEL1}:/app/routes/api.php
kubectl cp /tmp/api.php default/${LARAVEL2}:/app/routes/api.php
kubectl cp /tmp/api.php default/${WORKER}:/app/routes/api.php
echo "Done"

echo ""
echo "=== Clearing route/config cache ==="
kubectl exec ${LARAVEL1} -- php /app/artisan route:clear
kubectl exec ${LARAVEL1} -- php /app/artisan config:clear
kubectl exec ${LARAVEL1} -- php /app/artisan cache:clear

echo ""
echo "=== Verify whatsapp route is now registered ==="
kubectl exec ${LARAVEL1} -- php /app/artisan route:list 2>&1 | grep -i whatsapp || echo "STILL NOT FOUND"

echo ""
echo "=== Live test via public URL ==="
curl -s -w "\nHTTP_CODE:%{http_code}" -X POST \
  https://www.picme225.site/api/user/whatsapp/webhook \
  -H 'Content-Type: application/json' \
  -d '{
    "event":"messages.upsert",
    "instance":"picme_whatsapp",
    "data":{
      "key":{"remoteJid":"22500000099@s.whatsapp.net","fromMe":false,"id":"LIVE_TEST_002"},
      "pushName":"Test Live 2",
      "messageType":"conversation",
      "message":{"conversation":"Je vends un appartement F3 a Cocody Abidjan, 250000 FCFA mois, contact 0701000000"}
    },
    "destination":"https://www.picme225.site/api/user/whatsapp/webhook",
    "apikey":"7EC9EF15-0D92-46B9-B324-C46DB277E3FD"
  }'
