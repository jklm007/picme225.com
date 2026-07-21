$ErrorActionPreference = "Stop"
$ZONE = "europe-west9-a"
$NODE = "k3s-master-gcp"

Write-Host "Copying dashboard.blade.php to server..."
gcloud compute scp "resources\views\user\dashboard.blade.php" "${NODE}:/tmp/dashboard.blade.php" --zone=$ZONE --quiet

Write-Host "Copying into Kubernetes pod..."
gcloud compute ssh $NODE --zone=$ZONE --command='
export POD=$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
echo "Deploying to pod: $POD"
sudo k3s kubectl cp /tmp/dashboard.blade.php default/${POD}:/app/resources/views/user/dashboard.blade.php
sudo k3s kubectl exec $POD -- php /app/artisan view:clear
'
Write-Host "Done deploying dashboard!"
