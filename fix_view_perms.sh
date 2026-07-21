#!/bin/bash
echo "=== Start Fixing Directory Permissions on All Pods ==="

LARAVEL_PODS=$(sudo kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
WORKER_PODS=$(sudo kubectl get pods -l app=laravel-worker --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
ALL_PODS="$LARAVEL_PODS $WORKER_PODS"

for POD in $ALL_PODS; do
    echo "Fixing permissions inside pod: $POD"
    
    # Recursively set directory permissions to 755
    sudo kubectl exec ${POD} -- find /app/resources/views -type d -exec chmod 755 {} +
    
    # Recursively set file permissions to 644/664 just in case
    sudo kubectl exec ${POD} -- find /app/resources/views -type f -exec chmod 644 {} +

    # Clear view cache
    sudo kubectl exec ${POD} -- php artisan view:clear
done

echo "=== Permission Fix Finished successfully! ==="
