$ZONE = "europe-west9-a"
$NODE = "k3s-master-gcp"

Write-Host "Uploading user APK to remote server..."
gcloud compute scp public\apk\picme-user.apk ${NODE}:/tmp/frontend_v2/picme-user.apk --zone=$ZONE --quiet

Write-Host "Syncing all APKs to all pods..."
$cmd = @"
PODS=`$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=':metadata.name' --no-headers)
for POD in `$PODS; do
  echo "Deploying APKs to `$POD"
  sudo k3s kubectl exec `$POD -c laravel -- mkdir -p /app/public/apk
  sudo k3s kubectl cp /tmp/frontend_v2/picme-user.apk default/`$POD:/app/public/apk/picme-user.apk -c laravel
  sudo k3s kubectl cp /tmp/p4/picme-driver.apk default/`$POD:/app/public/apk/picme-driver.apk -c laravel
done
echo 'All APKs deployed successfully!'
"@

gcloud compute ssh $NODE --zone=$ZONE --command $cmd

Write-Host "DONE"
