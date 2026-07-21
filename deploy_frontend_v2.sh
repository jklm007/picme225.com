#!/bin/bash
set -e
echo "=== DEPLOIEMENT FRONTEND V2 (DARK VIP 2026) ==="

KCL="sudo k3s kubectl"

PODS_WEB=$($KCL get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
PODS_JKLM=$($KCL get pods -l app=jklm-web --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)

ALL_WEB_PODS="$PODS_WEB $PODS_JKLM"

# Fichiers à synchroniser
FILES=(
  "/tmp/frontend_v2/app.blade.php:/app/resources/views/user/layout/app.blade.php"
  "/tmp/frontend_v2/home.blade.php:/app/resources/views/home.blade.php"
  "/tmp/frontend_v2/drive.blade.php:/app/resources/views/drive.blade.php"
  "/tmp/frontend_v2/ride.blade.php:/app/resources/views/ride.blade.php"
  "/tmp/frontend_v2/style.css:/app/public/asset/css/style.css"
  "/tmp/frontend_v2/abidjan_plateau_sunset.png:/app/public/asset/img/abidjan_plateau_sunset.png"
  "/tmp/frontend_v2/abidjan_plateau_night.png:/app/public/asset/img/abidjan_plateau_night.png"
  "/tmp/frontend_v2/vip_chauffeur_abidjan.png:/app/public/asset/img/vip_chauffeur_abidjan.png"
)

for POD in $ALL_WEB_PODS; do
  if [ -z "$POD" ]; then continue; fi
  echo ">>> Deploiement sur: $POD"
  
  # Determine container name
  CONTAINER="laravel"
  if [[ "$POD" == *"jklm-web"* ]]; then CONTAINER="jklm-web"; fi
  
  $KCL exec ${POD} -c $CONTAINER -- mkdir -p /app/public/asset/img
  
  for entry in "${FILES[@]}"; do
    SRC="${entry%%:*}"
    DEST="${entry##*:}"
    echo "  Copie $SRC -> $DEST"
    $KCL cp "$SRC" default/${POD}:$DEST -c $CONTAINER
  done
  
  echo "  Vidage du cache des vues..."
  $KCL exec ${POD} -c $CONTAINER -- php /app/artisan view:clear || true
done

echo "=== FRONTEND DEPLOYE AVEC SUCCES ==="
