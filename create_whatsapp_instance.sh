#!/bin/bash
# Run from inside the Laravel pod to reach evolution-api-service via ClusterIP
LARAVEL_POD=$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o jsonpath='{.items[0].metadata.name}')
API_URL="http://evolution-api-service:8080"
API_KEY="picme225-evolution-secret-key"
INSTANCE="picme_whatsapp"

echo "=== Using Laravel pod: $LARAVEL_POD ==="

echo ""
echo "=== Étape 1: Vérification des instances existantes ==="
sudo k3s kubectl exec "$LARAVEL_POD" -- curl -s -X GET "$API_URL/instance/fetchInstances" \
  -H "apikey: $API_KEY" | python3 -m json.tool 2>/dev/null || echo "(no instances yet)"

echo ""
echo "=== Étape 2: Création de l'instance picme_whatsapp ==="
CREATE_RESULT=$(sudo k3s kubectl exec "$LARAVEL_POD" -- curl -s -X POST "$API_URL/instance/create" \
  -H "apikey: $API_KEY" \
  -H "Content-Type: application/json" \
  -d "{
    \"instanceName\": \"$INSTANCE\",
    \"integration\": \"WHATSAPP-BAILEYS\",
    \"token\": \"picme_wa_token_2025\",
    \"webhook\": \"https://www.picme225.site/api/whatsapp/webhook\",
    \"webhook_by_events\": false,
    \"events\": [\"MESSAGES_UPSERT\", \"CONNECTION_UPDATE\"],
    \"reject_call\": false,
    \"groups_ignore\": false
  }")
echo "$CREATE_RESULT" | python3 -m json.tool 2>/dev/null || echo "$CREATE_RESULT"

echo ""
echo "=== Étape 3: Connexion et récupération du QR Code ==="
sleep 3
QR_RESULT=$(sudo k3s kubectl exec "$LARAVEL_POD" -- curl -s -X GET "$API_URL/instance/connect/$INSTANCE" \
  -H "apikey: $API_KEY")
echo "$QR_RESULT" | python3 -c "
import json,sys
data=json.load(sys.stdin)
if 'base64' in data:
    b64=data['base64']
    print('QR_BASE64_START')
    print(b64)
    print('QR_BASE64_END')
    print('STATUS: QR Code disponible!')
elif 'instance' in data and data.get('instance',{}).get('state') == 'open':
    print('STATUS: Déjà connecté!')
else:
    print('STATUS: Réponse inattendue:')
    print(json.dumps(data, indent=2))
" 2>/dev/null || echo "$QR_RESULT"

echo ""
echo "=== DONE ==="
