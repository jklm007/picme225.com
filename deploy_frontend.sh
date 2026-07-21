#!/bin/bash
set -e
echo "=== DEPLOIEMENT FRONTEND MODERNISATION 2026 ==="

KCL="kubectl"
command -v kubectl > /dev/null 2>&1 || KCL="sudo k3s kubectl"

function deploy_frontend {
  POD=$1
  CONTAINER=$2
  
  if [ -z "$POD" ]; then
    return
  fi
  
  echo ">>> Deploiement sur: $POD (Container: $CONTAINER)"
  $KCL exec ${POD} -c $CONTAINER -- mkdir -p /app/resources/views/user/layout
  $KCL exec ${POD} -c $CONTAINER -- mkdir -p /app/resources/views/marketplace
  $KCL cp /tmp/p3/app.blade.php default/${POD}:/app/resources/views/user/layout/app.blade.php -c $CONTAINER
  $KCL cp /tmp/p3/marketplace_index.blade.php default/${POD}:/app/resources/views/marketplace/index.blade.php -c $CONTAINER
  $KCL exec ${POD} -c $CONTAINER -- php /app/artisan view:clear || true
}

PICME_POD=$($KCL get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
deploy_frontend "$PICME_POD" "laravel"

JKLM_POD=$($KCL get pods -l app=jklm-web --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
deploy_frontend "$JKLM_POD" "jklm-web"

EPDD_POD=$($KCL get pods -l app=epdd --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
deploy_frontend "$EPDD_POD" "epdd"

echo "DONE"
