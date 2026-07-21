#!/bin/bash
set -e
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"
echo "=== Files in /var/log/ inside pod ==="
sudo k3s kubectl exec "$POD" -- ls -la /var/log/ || true
echo "=== checking /var/log/nginx/ ==="
sudo k3s kubectl exec "$POD" -- ls -la /var/log/nginx/ || true
echo "=== tail /var/log/syslog ==="
sudo k3s kubectl exec "$POD" -- tail -n 50 /var/log/syslog || echo "No /var/log/syslog"
echo "=== tail /var/log/messages ==="
sudo k3s kubectl exec "$POD" -- tail -n 50 /var/log/messages || echo "No /var/log/messages"
