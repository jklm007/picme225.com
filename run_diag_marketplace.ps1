$ZONE = "europe-west9-a"
$NODE = "k3s-master-gcp"

Write-Host "Uploading diagnostic script..."
gcloud compute scp "C:\Users\HP\.gemini\antigravity\brain\eef68649-a0e3-488d-a21b-ddd928155790\scratch\diag_marketplace.php" ${NODE}:/tmp/diag_marketplace.php --zone=$ZONE --quiet

Write-Host "Running diagnostic on pod..."
gcloud compute ssh $NODE --zone=$ZONE --command "sudo k3s kubectl exec `$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=':metadata.name' --no-headers | head -n 1) -c laravel -- php /tmp/diag_marketplace.php 2>&1 || (sudo k3s kubectl cp /tmp/diag_marketplace.php default/`$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=':metadata.name' --no-headers | head -n 1):/tmp/diag_marketplace.php -c laravel && sudo k3s kubectl exec `$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=':metadata.name' --no-headers | head -n 1) -c laravel -- php /tmp/diag_marketplace.php)"
