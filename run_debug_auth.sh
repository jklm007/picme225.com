#!/bin/bash
POD=$(sudo kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
sudo kubectl cp /tmp/debug_500_auth.php default/${POD}:/app/debug_500_auth.php
sudo kubectl exec ${POD} -- php /app/debug_500_auth.php
