#!/bin/bash
POD=$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
echo "Pod: $POD"

sudo k3s kubectl cp /tmp/debug_mkt2.php default/${POD}:/tmp/debug_mkt2.php -c laravel
sudo k3s kubectl exec ${POD} -c laravel -- php /tmp/debug_mkt2.php 2>&1
