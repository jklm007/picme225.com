#!/bin/bash
POD=$(sudo kubectl get pods -l app=laravel -o jsonpath='{.items[?(@.status.phase=="Running")].metadata.name}' | awk '{print $1}')
sudo kubectl logs $POD --tail=500 > /tmp/logs.txt
cat /tmp/logs.txt | grep -A 20 -B 2 "WEBHOOK RECEIVED" | tail -n 100 > /tmp/recent_webhooks.txt
cat /tmp/recent_webhooks.txt
