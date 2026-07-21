#!/bin/bash
# Live end-to-end test: POST to public webhook URL (like Evolution API would)
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X POST \
  https://www.picme225.site/api/user/whatsapp/webhook \
  -H 'Content-Type: application/json' \
  -d '{
    "event": "messages.upsert",
    "instance": "picme_whatsapp",
    "data": {
      "key": {
        "remoteJid": "22500000002@s.whatsapp.net",
        "fromMe": false,
        "id": "LIVE_TEST_001"
      },
      "pushName": "Test WhatsApp Live",
      "messageType": "conversation",
      "message": {
        "conversation": "Je vends une Toyota Corolla 2019 en bon etat, climatisee, 5500000 FCFA, contact: 0759000000, Yopougon Abidjan"
      }
    },
    "destination": "https://www.picme225.site/api/user/whatsapp/webhook",
    "apikey": "7EC9EF15-0D92-46B9-B324-C46DB277E3FD"
  }')

echo "=== Live Webhook Test ==="
echo "$RESPONSE"

# Wait a few seconds for the queue job to run, then check worker logs
echo ""
echo "=== Waiting 10s for queue worker to process job ==="
sleep 10
echo ""
echo "=== Worker logs (last 30 lines) ==="
kubectl logs laravel-worker-59bb9bcf79-4bdwg --tail=30 2>&1

echo ""
echo "=== Recent whatsapp_messages in DB ==="
kubectl exec laravel-deployment-7779b9fb85-jfnrs -- php /app/artisan tinker --execute="use App\Models\WhatsappMessage; \$msgs = WhatsappMessage::latest()->take(3)->get(['id','phone','content','status','error_log']); echo json_encode(\$msgs->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);"
