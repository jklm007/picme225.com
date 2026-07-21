#!/bin/bash
POD=$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
echo "Pod: $POD"

sudo k3s kubectl cp /tmp/diag_marketplace.php default/${POD}:/tmp/diag_marketplace.php -c laravel
sudo k3s kubectl exec ${POD} -c laravel -- php /tmp/diag_marketplace.php
