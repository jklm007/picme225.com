#!/bin/bash
POD=$(sudo kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
sudo kubectl cp /tmp/check_perms_v2.php default/${POD}:/app/check_perms_v2.php
sudo kubectl exec ${POD} -- php /app/check_perms_v2.php
