#!/bin/bash
curl -s -X POST -H "apikey: picme225-evolution-secret-key" \
  -H "Content-Type: application/json" \
  -d '{"instanceName":"picme_whatsapp","qrcode":true,"integration":"WHATSAPP-BAILEYS"}' \
  http://evolution-api-service:8080/instance/create
