#!/bin/bash
set -e
echo "=== DEPLOIEMENT FOOTER ABIDJAN BANNER ==="

KCL="kubectl"
command -v kubectl > /dev/null 2>&1 || KCL="sudo k3s kubectl"

function deploy_footer {
  POD=$1
  CONTAINER=$2
  
  if [ -z "$POD" ]; then
    return
  fi
  
  echo ">>> Deploiement sur: $POD (Container: $CONTAINER)"
  $KCL cp /tmp/p3/drive.blade.php default/${POD}:/app/resources/views/drive.blade.php -c $CONTAINER
  $KCL cp /tmp/p3/ride.blade.php default/${POD}:/app/resources/views/ride.blade.php -c $CONTAINER
  $KCL exec ${POD} -c $CONTAINER -- php /app/artisan view:clear || true
}

PICME_POD=$($KCL get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
deploy_footer "$PICME_POD" "laravel"

JKLM_POD=$($KCL get pods -l app=jklm-web --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
deploy_footer "$JKLM_POD" "jklm-web"

EPDD_POD=$($KCL get pods -l app=epdd --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
deploy_footer "$EPDD_POD" "epdd"

echo "DONE"
