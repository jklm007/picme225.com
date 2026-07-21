#!/bin/bash
set -e
echo "=== DEPLOIEMENT APK + QR CODES ==="

KCL="kubectl"
command -v kubectl > /dev/null 2>&1 || KCL="sudo k3s kubectl"

function deploy_all {
  POD=$1
  CONTAINER=$2
  
  if [ -z "$POD" ]; then
    return
  fi
  
  echo ">>> Deploiement sur: $POD (Container: $CONTAINER)"
  
  # Create directories
  $KCL exec ${POD} -c $CONTAINER -- mkdir -p /app/public/apk
  $KCL exec ${POD} -c $CONTAINER -- mkdir -p /app/public/asset/img
  
  # Copy APK files
  $KCL cp /tmp/p3/picme-user.apk default/${POD}:/app/public/apk/picme-user.apk -c $CONTAINER
  $KCL cp /tmp/p3/picme-driver.apk default/${POD}:/app/public/apk/picme-driver.apk -c $CONTAINER
  
  # Copy QR codes
  $KCL cp /tmp/p3/qr-user.png default/${POD}:/app/public/asset/img/qr-user.png -c $CONTAINER
  $KCL cp /tmp/p3/qr-driver.png default/${POD}:/app/public/asset/img/qr-driver.png -c $CONTAINER
  
  # Copy updated home view
  $KCL cp /tmp/p3/home.blade.php default/${POD}:/app/resources/views/home.blade.php -c $CONTAINER
  
  # Clear view cache
  $KCL exec ${POD} -c $CONTAINER -- php /app/artisan view:clear || true
  
  echo "Done: $POD"
}

PICME_POD=$($KCL get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
deploy_all "$PICME_POD" "laravel"

JKLM_POD=$($KCL get pods -l app=jklm-web --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
deploy_all "$JKLM_POD" "jklm-web"

EPDD_POD=$($KCL get pods -l app=epdd --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
deploy_all "$EPDD_POD" "epdd"

echo "=== ALL DONE ==="
