Write-Host "Uploading check_db.php..."
gcloud compute scp check_db.php k3s-master-gcp:/tmp/check_db.php --zone=europe-west9-a --quiet

Write-Host "Executing on pod..."
$remoteScript = "
POD=`sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o jsonpath='{.items[0].metadata.name}'`
echo `"Pod: `$POD`"
sudo k3s kubectl cp /tmp/check_db.php `$POD:/app/check_db.php
sudo k3s kubectl exec `$POD -- php /app/check_db.php
"
gcloud compute ssh k3s-master-gcp --zone=europe-west9-a --command $remoteScript
