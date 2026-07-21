Write-Host "Syncing updated files to server..."
gcloud compute scp "resources/views/whatsapp/qr_page.blade.php" k3s-master-gcp:/tmp/qr_page.blade.php --zone=europe-west9-a --quiet
gcloud compute scp "resources/views/admin/include/nav.blade.php" k3s-master-gcp:/tmp/nav.blade.php --zone=europe-west9-a --quiet
gcloud compute scp "routes/admin.php" k3s-master-gcp:/tmp/admin.php --zone=europe-west9-a --quiet
gcloud compute scp "routes/web.php" k3s-master-gcp:/tmp/web.php --zone=europe-west9-a --quiet

$script = @"
set -e
POD=`$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o jsonpath='{.items[0].metadata.name}')
echo "=== Deploying to pod: `$POD ==="

sudo k3s kubectl cp /tmp/qr_page.blade.php "`$POD:/app/resources/views/whatsapp/qr_page.blade.php"
sudo k3s kubectl cp /tmp/nav.blade.php     "`$POD:/app/resources/views/admin/include/nav.blade.php"
sudo k3s kubectl cp /tmp/admin.php         "`$POD:/app/routes/admin.php"
sudo k3s kubectl cp /tmp/web.php           "`$POD:/app/routes/web.php"

echo "=== Clearing caches ==="
sudo k3s kubectl exec "`$POD" -- php /app/artisan route:clear
sudo k3s kubectl exec "`$POD" -- php /app/artisan view:clear
sudo k3s kubectl exec "`$POD" -- php /app/artisan config:clear

echo "=== Verifying new routes ==="
sudo k3s kubectl exec "`$POD" -- php /app/artisan route:list --path=admin/whatsapp

echo "=== DONE ==="
"@

$script | gcloud compute ssh k3s-master-gcp --zone=europe-west9-a --command "bash -s"
