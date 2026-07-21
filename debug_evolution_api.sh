#!/bin/bash
LARAVEL_POD=$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o jsonpath='{.items[0].metadata.name}')
API_URL="http://evolution-api-service:8080"
API_KEY="picme225-evolution-secret-key"
INSTANCE="picme_whatsapp"

echo "=== Pod: $LARAVEL_POD ==="

echo ""
echo "=== Vérification version Evolution API ==="
sudo k3s kubectl exec "$LARAVEL_POD" -- curl -s "$API_URL/" | python3 -m json.tool 2>/dev/null || true

echo ""
echo "=== Test création instance (format minimal) ==="
sudo k3s kubectl exec "$LARAVEL_POD" -- curl -s -X POST "$API_URL/instance/create" \
  -H "apikey: $API_KEY" \
  -H "Content-Type: application/json" \
  -d "{\"instanceName\": \"$INSTANCE\"}" | python3 -m json.tool 2>/dev/null

echo ""
echo "=== Instances existantes ==="
sudo k3s kubectl exec "$LARAVEL_POD" -- curl -s "$API_URL/instance/fetchInstances" \
  -H "apikey: $API_KEY" | python3 -m json.tool 2>/dev/null
