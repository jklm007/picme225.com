#!/bin/bash
POD=$(sudo kubectl get pods -l app=laravel -o jsonpath='{.items[?(@.status.phase=="Running")].metadata.name}' | awk '{print $1}')
sudo kubectl cp /tmp/check_db.php default/$POD:/app/check_db.php
sudo kubectl exec $POD -- php /app/check_db.php
