#!/bin/bash
tar -xzf /tmp/webhook_files.tar.gz -C /tmp/

echo "Deploying to laravel pods..."
for pod in $(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[*].metadata.name}'); do
    echo "Uploading to $pod..."
    sudo k3s kubectl cp /tmp/app default/$pod:/app/app
    sudo k3s kubectl cp /tmp/routes/api.php default/$pod:/app/routes/api.php
    sudo k3s kubectl exec $pod -- php artisan config:clear
    sudo k3s kubectl exec $pod -- php artisan route:clear
done

echo "Deploying to laravel-worker pods..."
for pod in $(sudo k3s kubectl get pods -l app=laravel-worker -o jsonpath='{.items[*].metadata.name}'); do
    echo "Uploading to $pod..."
    sudo k3s kubectl cp /tmp/app default/$pod:/app/app
    sudo k3s kubectl exec $pod -- php artisan queue:restart
done

echo "Running migrations just in case..."
# Run migration on the first laravel pod
first_pod=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
sudo k3s kubectl exec $first_pod -- php artisan migrate --force

echo "Done!"
