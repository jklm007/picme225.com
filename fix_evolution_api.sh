#!/bin/bash
set -e

echo "=== Étape 1: Correction du déploiement Evolution API ==="

# Patch imagePullPolicy to Always + correct webhook URL
sudo k3s kubectl patch deployment evolution-api-deployment --type=json -p='[
  {
    "op": "replace",
    "path": "/spec/template/spec/containers/0/imagePullPolicy",
    "value": "Always"
  },
  {
    "op": "replace",
    "path": "/spec/template/spec/containers/0/env",
    "value": [
      {"name": "AUTHENTICATION_TYPE", "value": "apikey"},
      {"name": "AUTHENTICATION_API_KEY", "value": "picme225-evolution-secret-key"},
      {"name": "AUTHENTICATION_EXPOSE_IN_FETCH_INSTANCES", "value": "true"},
      {"name": "SERVER_PORT", "value": "8080"},
      {"name": "SERVER_URL", "value": "https://evolution.picme225.site"},
      {"name": "WEBHOOK_GLOBAL_ENABLED", "value": "true"},
      {"name": "WEBHOOK_GLOBAL_URL", "value": "https://www.picme225.site/api/whatsapp/webhook"},
      {"name": "WEBHOOK_GLOBAL_WEBHOOK_BY_EVENTS", "value": "false"},
      {"name": "WEBHOOK_EVENTS_MESSAGES_UPSERT", "value": "true"},
      {"name": "WEBHOOK_EVENTS_CONNECTION_UPDATE", "value": "true"},
      {"name": "STORE_MESSAGES", "value": "true"},
      {"name": "STORE_MESSAGE_UP", "value": "true"},
      {"name": "STORE_CONTACTS", "value": "true"},
      {"name": "DATABASE_ENABLED", "value": "true"},
      {"name": "DATABASE_PROVIDER", "value": "postgresql"},
      {"name": "DATABASE_CONNECTION_URI", "value": "postgresql://picme_user:secret_password@postgres-service:5432/picme_db?schema=evolution&sslmode=disable"},
      {"name": "REDIS_ENABLED", "value": "true"},
      {"name": "REDIS_PREFIX_KEY", "value": "evolution"},
      {"name": "LOG_LEVEL", "value": "DEBUG"},
      {"name": "LOG_COLOR", "value": "false"},
      {"name": "REDIS_URI", "value": "redis://redis-service:6379"},
      {"name": "CACHE_REDIS_ENABLED", "value": "true"},
      {"name": "CACHE_REDIS_URI", "value": "redis://redis-service:6379"}
    ]
  }
]'

echo "=== Étape 2: Redémarrage forcé du pod ==="
sudo k3s kubectl rollout restart deployment/evolution-api-deployment

echo "=== Étape 3: Attente du démarrage (60s max) ==="
sudo k3s kubectl rollout status deployment/evolution-api-deployment --timeout=120s

echo "=== Étape 4: Vérification finale ==="
sudo k3s kubectl get pods -l app=evolution-api

echo "=== DONE ==="
