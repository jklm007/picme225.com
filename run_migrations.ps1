Write-Host "Creating migrations archive..."
tar -czf migrations_sync.tar.gz -C database/migrations .

Write-Host "Uploading to k3s-master-gcp..."
gcloud compute scp migrations_sync.tar.gz k3s-master-gcp:/tmp/migrations_sync.tar.gz --zone=europe-west9-a --quiet

Write-Host "Executing on pod..."
$remoteScript = "
POD=`sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o jsonpath='{.items[0].metadata.name}'`
echo `"Pod: `$POD`"
sudo k3s kubectl cp /tmp/migrations_sync.tar.gz `$POD:/tmp/migrations_sync.tar.gz
sudo k3s kubectl exec `$POD -- bash -c `"mkdir -p /tmp/migs && tar -xzf /tmp/migrations_sync.tar.gz -C /tmp/migs && cp -r /tmp/migs/* /app/database/migrations/ && rm -rf /tmp/migs`"
echo `"Running php artisan migrate --force...`"
sudo k3s kubectl exec `$POD -- php /app/artisan migrate --force
"
gcloud compute ssh k3s-master-gcp --zone=europe-west9-a --command $remoteScript
