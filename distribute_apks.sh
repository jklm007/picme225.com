#!/bin/bash
set -e
echo "Deploying APKs to all laravel pods..."

KCL="sudo k3s kubectl"

PODS_WEB=$($KCL get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)

for POD in $PODS_WEB; do
  if [ -z "$POD" ]; then continue; fi
  echo "Deploying to $POD"
  $KCL exec ${POD} -c laravel -- mkdir -p /app/public/apk
  
  if [ -f /tmp/frontend_v2/picme-user.apk ]; then
    echo "  -> Copying User APK..."
    $KCL cp /tmp/frontend_v2/picme-user.apk default/${POD}:/app/public/apk/picme-user.apk -c laravel
  fi
  
  if [ -f /tmp/p4/picme-driver.apk ]; then
    echo "  -> Copying Driver APK..."
    $KCL cp /tmp/p4/picme-driver.apk default/${POD}:/app/public/apk/picme-driver.apk -c laravel
  fi
done

echo "Done distributing APKs!"
