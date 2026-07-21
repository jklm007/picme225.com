#!/bin/bash
POD=$(sudo kubectl get pods -l app=laravel -o jsonpath='{.items[?(@.status.phase=="Running")].metadata.name}' | awk '{print $1}')
sudo kubectl exec $POD -- curl -s -X POST http://evolution-api-service:8080/chat/getBase64FromMediaMessage/picme_whatsapp \
-H "apikey: 6BE2FB65-6676-48FB-BE16-162621590D6A" \
-H "Content-Type: application/json" \
-d '{"message": {}}'
