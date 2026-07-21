#!/bin/bash
set -e
LARAVEL_POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "Deploying to $LARAVEL_POD..."

sudo k3s kubectl cp /tmp/airport.blade.php $LARAVEL_POD:/app/resources/views/marketing/airport.blade.php

echo "Deployment successful."
