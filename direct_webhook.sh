#!/bin/bash
sudo kubectl run -i --rm --restart=Never curl-client --image=curlimages/curl -- curl -X POST http://evolution-api-service:8080/webhook/set/picme_whatsapp \
  -H "apikey: picme225-evolution-secret-key" \
  -H "Content-Type: application/json" \
  -d '{
    "webhook": {
      "enabled": true,
      "url": "https://picme225.site/api/whatsapp/webhook",
      "byEvents": false,
      "base64": true,
      "events": [
        "MESSAGES_UPSERT",
        "MESSAGES_UPDATE",
        "MESSAGES_DELETE",
        "SEND_MESSAGE",
        "CONNECTION_UPDATE"
      ]
    }
  }'
