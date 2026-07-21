#!/bin/bash
LARAVEL_POD=$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o jsonpath='{.items[0].metadata.name}')
API_URL="http://evolution-api-service:8080"
API_KEY="picme225-evolution-secret-key"
INSTANCE="picme_whatsapp"

echo "=== Pod: $LARAVEL_POD ==="

echo ""
echo "=== Étape 1: Configuration du Webhook sur l'instance ==="
sudo k3s kubectl exec "$LARAVEL_POD" -- curl -s -X POST "$API_URL/webhook/set/$INSTANCE" \
  -H "apikey: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://www.picme225.site/api/whatsapp/webhook",
    "enabled": true,
    "webhookByEvents": false,
    "webhookBase64": false,
    "events": ["MESSAGES_UPSERT", "CONNECTION_UPDATE", "MESSAGES_UPDATE"]
  }' | python3 -m json.tool 2>/dev/null

echo ""
echo "=== Étape 2: Connexion et récupération du QR Code ==="
QR_RAW=$(sudo k3s kubectl exec "$LARAVEL_POD" -- curl -s -X GET "$API_URL/instance/connect/$INSTANCE" \
  -H "apikey: $API_KEY")

echo "$QR_RAW" | python3 -c "
import json, sys, base64, os

data = json.load(sys.stdin)

if 'base64' in data:
    b64 = data['base64']
    # Remove prefix like data:image/png;base64,
    if ',' in b64:
        raw = b64.split(',')[1]
    else:
        raw = b64
    with open('/tmp/qr_picme.png', 'wb') as f:
        f.write(base64.b64decode(raw))
    print('✅ QR Code sauvegardé dans /tmp/qr_picme.png')
    print('QR_PAIRINGCODE=' + str(data.get('pairingCode', 'N/A')))
elif data.get('instance', {}).get('state') == 'open':
    print('✅ Instance déjà connectée!')
else:
    print('Réponse:')
    print(json.dumps(data, indent=2))
" 2>/dev/null || echo "$QR_RAW"

echo ""
echo "=== Étape 3: Copie du QR Code vers volume accessible ==="
sudo k3s kubectl cp "$LARAVEL_POD:/tmp/does_not_matter" /tmp/placeholder 2>/dev/null || true

# Copy QR from pod to host
sudo k3s kubectl exec "$LARAVEL_POD" -- ls -la /tmp/qr_picme.png 2>/dev/null && echo "QR file exists in pod" || echo "QR not in pod, it was generated on host"

echo "=== DONE ==="
