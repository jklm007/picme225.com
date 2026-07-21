#!/bin/bash
set -e
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Pod: $POD"
echo "=== Nginx Logs Directory ==="
sudo k3s kubectl exec "$POD" -- ls -la /var/lib/nginx/logs || true
echo "=== syslog-ng Configuration ==="
sudo k3s kubectl exec "$POD" -- cat /etc/syslog-ng/syslog-ng.conf 2>/dev/null || echo "No syslog-ng.conf"
echo "=== Recently Modified Files in Pod (last 30 mins) ==="
sudo k3s kubectl exec "$POD" -- find /var/log /app/storage/logs -mmin -30 -type f 2>/dev/null || true
