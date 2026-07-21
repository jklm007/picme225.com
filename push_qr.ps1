$ZONE = "europe-west9-a"
$NODE = "k3s-master-gcp"

Write-Host "Uploading QR Codes..."
gcloud compute scp public\asset\img\qr-user.png ${NODE}:/tmp/frontend_v2/qr-user.png --zone=$ZONE --quiet
gcloud compute scp public\asset\img\qr-driver.png ${NODE}:/tmp/frontend_v2/qr-driver.png --zone=$ZONE --quiet

Write-Host "Deploying..."
$cmd = @"
POD=`$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=':metadata.name' --no-headers | head -n 1)
sudo k3s kubectl cp /tmp/frontend_v2/qr-user.png default/`$POD:/app/public/asset/img/qr-user.png -c laravel
sudo k3s kubectl cp /tmp/frontend_v2/qr-driver.png default/`$POD:/app/public/asset/img/qr-driver.png -c laravel
echo 'QR Codes uploaded'
"@

gcloud compute ssh $NODE --zone=$ZONE --command $cmd

Write-Host "DONE"
