#!/bin/bash
set -e
echo "=== DEPLOIEMENT NEW DRIVER APK (OPTIMIZED) ==="

KCL="sudo k3s kubectl"

function deploy_driver {
  POD=$1
  CONTAINER=$2
  
  if [ -z "$POD" ]; then
    return
  fi
  
  echo ">>> Deploiement sur: $POD (Container: $CONTAINER)"
  
  $KCL exec ${POD} -c $CONTAINER -- mkdir -p /app/public/apk
  $KCL cp /tmp/p4/picme-driver.apk default/${POD}:/app/public/apk/picme-driver.apk -c $CONTAINER
  
  echo "Done: $POD"
}

PICME_POD=$($KCL get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
deploy_driver "$PICME_POD" "laravel"

JKLM_POD=$($KCL get pods -l app=jklm-web --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
deploy_driver "$JKLM_POD" "jklm-web"

EPDD_POD=$($KCL get pods -l app=epdd --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
deploy_driver "$EPDD_POD" "epdd"

echo "=== ALL DONE ==="
