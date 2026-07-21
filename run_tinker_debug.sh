#!/bin/bash
POD=$(sudo kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
sudo kubectl cp /tmp/tinker_debug.php default/${POD}:/app/tinker_debug.php
sudo kubectl exec ${POD} -- php /app/tinker_debug.php
