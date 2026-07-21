$ZONE = "europe-west9-a"
$NODE = "k3s-master-gcp"

Write-Host "Uploading app.blade.php..."
gcloud compute scp resources\views\user\layout\app.blade.php ${NODE}:/tmp/frontend_v2/app.blade.php --zone=$ZONE --quiet

Write-Host "Syncing app.blade.php to all pods..."
$cmd = @"
PODS=`$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=':metadata.name' --no-headers)
for POD in `$PODS; do
  echo "Deploying app.blade.php to `$POD"
  sudo k3s kubectl cp /tmp/frontend_v2/app.blade.php default/`$POD:/app/resources/views/user/layout/app.blade.php -c laravel
  sudo k3s kubectl exec `$POD -c laravel -- php artisan view:clear
done
echo 'app.blade.php deployed successfully!'
"@

gcloud compute ssh $NODE --zone=$ZONE --command $cmd

Write-Host "DONE"
