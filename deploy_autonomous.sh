#!/bin/bash
set -e
echo "Creating k8s secret for env vars..."
sudo k3s kubectl delete secret laravel-env --ignore-not-found
sudo k3s kubectl create secret generic laravel-env --from-env-file=.env.production

echo "Installing Docker..."
sudo apt-get update
sudo apt-get install docker.io -y
sudo systemctl enable docker
sudo systemctl start docker

echo "Building Laravel image..."
sudo docker build -t picme225-laravel:latest -f Dockerfile.laravel .

echo "Building Node image..."
sudo docker build -t picme225-node:latest -f Dockerfile.node .

echo "Exporting images to tar..."
sudo docker save picme225-laravel:latest -o laravel.tar
sudo docker save picme225-node:latest -o node.tar

echo "Importing images into K3s..."
sudo k3s ctr images import laravel.tar
sudo k3s ctr images import node.tar

echo "Done! Images are ready in K3s."
