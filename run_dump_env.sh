#!/bin/bash
POD=$(sudo kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
sudo kubectl cp /tmp/dump_env.php default/${POD}:/app/dump_env.php
sudo kubectl exec ${POD} -- php /app/dump_env.php
