#!/bin/bash
curl -s -X POST -H "apikey: picme225-evolution-secret-key" \
  -H "Content-Type: application/json" \
  -d '{"webhook":{"enabled":true,"url":"https://www.picme225.site/api/user/whatsapp/webhook","webhookByEvents":false,"webhookBase64":false,"events":["MESSAGES_UPSERT","MESSAGES_UPDATE"]}}' \
  http://evolution-api-service:8080/webhook/set/picme_whatsapp
