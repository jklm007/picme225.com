#!/bin/bash
set -e

echo "Building Laravel image..."
sudo docker build -t picme225-laravel:latest -f Dockerfile.laravel .

echo "Exporting Laravel image to tar..."
sudo docker save picme225-laravel:latest -o laravel.tar

echo "Removing Docker image to save space..."
sudo docker rmi picme225-laravel:latest
sudo docker system prune -f

echo "Importing Laravel into K3s..."
sudo k3s ctr -n k8s.io images import laravel.tar
rm laravel.tar

echo "Building Node image..."
sudo docker build -t picme225-node:latest -f Dockerfile.node .

echo "Exporting Node image to tar..."
sudo docker save picme225-node:latest -o node.tar

echo "Removing Docker image to save space..."
sudo docker rmi picme225-node:latest
sudo docker system prune -f

echo "Importing Node into K3s..."
sudo k3s ctr -n k8s.io images import node.tar
rm node.tar

echo "Deploying Kubernetes resources..."
sudo k3s kubectl apply -f k8s/

echo "Done! Images are ready in K3s and resources applied."
