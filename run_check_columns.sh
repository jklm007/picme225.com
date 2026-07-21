#!/bin/bash
POD=$(sudo kubectl get pods -l app=laravel -o jsonpath='{.items[?(@.status.phase=="Running")].metadata.name}' | awk '{print $1}')
sudo kubectl cp /tmp/check_columns.php default/$POD:/app/check_columns.php
sudo kubectl exec $POD -- php /app/check_columns.php
