#!/bin/bash
set -e
echo "Deploying app.blade.php to all laravel pods..."

KCL="sudo k3s kubectl"

PODS_WEB=$($KCL get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)

for POD in $PODS_WEB; do
  if [ -z "$POD" ]; then continue; fi
  echo "Deploying to $POD"
  $KCL cp /tmp/frontend_v2/app.blade.php default/${POD}:/app/resources/views/user/layout/app.blade.php -c laravel
  $KCL exec ${POD} -c laravel -- php artisan view:clear
done

echo "Done distributing app.blade.php!"
