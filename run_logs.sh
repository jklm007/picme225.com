#!/bin/bash
POD=$(sudo kubectl get pods -l app=laravel -o jsonpath='{.items[?(@.status.phase=="Running")].metadata.name}' | head -n 1 | awk '{print $1}')
sudo kubectl logs $POD --tail=100
WORKER=$(sudo kubectl get pods -l app=laravel-worker -o jsonpath='{.items[?(@.status.phase=="Running")].metadata.name}' | head -n 1 | awk '{print $1}')
echo "--- WORKER LOGS ---"
sudo kubectl logs $WORKER --tail=100
