#!/bin/bash
POD=$(sudo kubectl get pods -l app=laravel -o jsonpath='{.items[?(@.status.phase=="Running")].metadata.name}' | awk '{print $1}')
sudo kubectl cp /tmp/test_base64.php default/$POD:/app/test_base64.php
sudo kubectl exec $POD -- php /app/test_base64.php
