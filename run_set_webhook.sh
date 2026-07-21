#!/bin/bash
POD=$(sudo kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
sudo kubectl cp /tmp/set_webhook.php default/${POD}:/tmp/set_webhook.php
sudo kubectl exec ${POD} -- php /tmp/set_webhook.php
