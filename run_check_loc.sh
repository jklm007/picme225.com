#!/bin/bash
POD=$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
sudo k3s kubectl cp /tmp/check_loc.php default/${POD}:/tmp/check_loc.php -c laravel
sudo k3s kubectl exec ${POD} -c laravel -- php /tmp/check_loc.php
