#!/bin/bash
POD=$(sudo kubectl get pods -l app=laravel -o jsonpath='{.items[?(@.status.phase=="Running")].metadata.name}' | awk '{print $1}')
echo "Running Pod is $POD"
sudo kubectl exec $POD -- php artisan optimize:clear
sudo kubectl exec $POD -- php artisan evolution:set-webhook
