#!/bin/bash
LARAVEL_POD=$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o jsonpath='{.items[0].metadata.name}')
API_URL="http://evolution-api-service:8080"
API_KEY="picme225-evolution-secret-key"
INSTANCE="picme_whatsapp"

echo "=== Pod: $LARAVEL_POD ==="

echo ""
echo "=== Création instance avec integration WHATSAPP-BAILEYS ==="
CREATE=$(sudo k3s kubectl exec "$LARAVEL_POD" -- curl -s -X POST "$API_URL/instance/create" \
  -H "apikey: $API_KEY" \
  -H "Content-Type: application/json" \
  -d "{
    \"instanceName\": \"$INSTANCE\",
    \"integration\": \"WHATSAPP-BAILEYS\"
  }")
echo "$CREATE" | python3 -m json.tool 2>/dev/null || echo "$CREATE"

# Check if instance was created successfully
STATUS=$(echo "$CREATE" | python3 -c "import json,sys; d=json.load(sys.stdin); print(d.get('instance',{}).get('state','error'))" 2>/dev/null || echo "error")
echo "Instance state: $STATUS"

if [[ "$STATUS" != "error" ]]; then
  echo ""
  echo "=== Récupération du QR Code ==="
  sleep 3
  QR=$(sudo k3s kubectl exec "$LARAVEL_POD" -- curl -s -X GET "$API_URL/instance/connect/$INSTANCE" \
    -H "apikey: $API_KEY")
  
  # Extract and save QR code base64
  echo "$QR" | python3 -c "
import json, sys, base64
data = json.load(sys.stdin)
print('--- Réponse complète ---')
state = data.get('instance', {}).get('state', '')
if state == 'open':
    print('STATUS: Deja connecte!')
elif 'base64' in data:
    b64 = data['base64']
    # Remove data:image/png;base64, prefix if present
    if ',' in b64:
        b64 = b64.split(',')[1]
    with open('/tmp/qr_code.png', 'wb') as f:
        f.write(base64.b64decode(b64))
    print('QR CODE SAVED to /tmp/qr_code.png')
    print('QR_BASE64=' + data['base64'][:50] + '...')
else:
    print(json.dumps(data, indent=2))
" 2>/dev/null || echo "$QR"
fi

echo ""
echo "=== Vérification instances ==="
sudo k3s kubectl exec "$LARAVEL_POD" -- curl -s "$API_URL/instance/fetchInstances" \
  -H "apikey: $API_KEY" | python3 -m json.tool 2>/dev/null

echo "=== DONE ==="
