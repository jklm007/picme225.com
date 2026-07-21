#!/bin/bash
sed -i 's/"socket.io-redis": "^4.0.1"/"socket.io-redis": "^4.0.1", "debug": "^4.3.0", "ioredis": "^5.3.0"/g' package.json
sed -i 's/replicas: 1/replicas: 0/g' k8s/osrm.yaml k8s/photon.yaml
sudo docker build -t picme225-node:latest -f Dockerfile.node .
sudo docker save picme225-node:latest > node.tar
sudo k3s ctr -n k8s.io images import node.tar
sudo k3s kubectl apply -f k8s/
sudo k3s kubectl delete pod -l app=nodejs
sudo k3s kubectl delete pod -l app=osrm
sudo k3s kubectl delete pod -l app=photon
sleep 5
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
sudo k3s kubectl exec -i $POD -- php artisan migrate --force --seed
