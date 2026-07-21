#!/bin/bash
set -e

echo "=== Building epdd-backend ==="
cd ~/epdd_backend
sudo docker build -t epdd-backend:latest -f Dockerfile .
sudo docker save -o epdd-backend.tar epdd-backend:latest
sudo k3s ctr -n k8s.io images import epdd-backend.tar
sudo docker rmi epdd-backend:latest
rm epdd-backend.tar

echo "=== Building jklm-web ==="
cd ~/jklm_backend
sudo docker build -t jklm-web:latest -f Dockerfile.web .
sudo docker save -o jklm-web.tar jklm-web:latest
sudo k3s ctr -n k8s.io images import jklm-web.tar
sudo docker rmi jklm-web:latest
rm jklm-web.tar

echo "=== Building jklm-python ==="
cd ~/jklm_backend/ai-service
sudo docker build -t jklm-python:latest -f Dockerfile .
sudo docker save -o jklm-python.tar jklm-python:latest
sudo k3s ctr -n k8s.io images import jklm-python.tar
sudo docker rmi jklm-python:latest
rm jklm-python.tar

echo "=== Restarting Deployments ==="
sudo k3s kubectl rollout restart deployment/epdd-deployment
sudo k3s kubectl rollout restart deployment/jklm-web-deployment
sudo k3s kubectl rollout restart deployment/jklm-python-deployment

echo "=== Waiting for rollouts ==="
sudo k3s kubectl rollout status deployment/epdd-deployment --timeout=300s
sudo k3s kubectl rollout status deployment/jklm-web-deployment --timeout=300s
sudo k3s kubectl rollout status deployment/jklm-python-deployment --timeout=300s

echo "=== Running Migrations for EPDD ==="
EPDD_POD=$(sudo k3s kubectl get pods -l app=epdd --field-selector=status.phase=Running -o jsonpath='{.items[0].metadata.name}')
sudo k3s kubectl exec "$EPDD_POD" -- php artisan migrate --force

echo "=== Running Migrations for JKLM ==="
JKLM_POD=$(sudo k3s kubectl get pods -l app=jklm-web --field-selector=status.phase=Running -o jsonpath='{.items[0].metadata.name}')
sudo k3s kubectl exec "$JKLM_POD" -- php artisan migrate --force

echo "=== ALL DONE ==="
sudo k3s kubectl get pods
