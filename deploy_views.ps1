$ErrorActionPreference = "Stop"

Write-Host "Copying index.blade.php..."
gcloud compute scp "resources/views/marketplace/index.blade.php" k3s-master-gcp:/tmp/marketplace_index.blade.php --zone=europe-west9-a --quiet

Write-Host "Copying admin whatsapp index..."
gcloud compute scp "resources/views/admin/whatsapp/index.blade.php" k3s-master-gcp:/tmp/wa_index.blade.php --zone=europe-west9-a --quiet

Write-Host "Copying admin marketplace listings index..."
gcloud compute scp "resources/views/admin/marketplace/listings/index.blade.php" k3s-master-gcp:/tmp/market_listings_index.blade.php --zone=europe-west9-a --quiet

Write-Host "Copying into pod..."
gcloud compute ssh k3s-master-gcp --zone=europe-west9-a --command="
sudo k3s kubectl cp /tmp/marketplace_index.blade.php default/laravel-deployment-787568f8f4-hxdsz:/app/resources/views/marketplace/index.blade.php &&
sudo k3s kubectl cp /tmp/wa_index.blade.php default/laravel-deployment-787568f8f4-hxdsz:/app/resources/views/admin/whatsapp/index.blade.php &&
sudo k3s kubectl cp /tmp/market_listings_index.blade.php default/laravel-deployment-787568f8f4-hxdsz:/app/resources/views/admin/marketplace/listings/index.blade.php &&
sudo k3s kubectl exec laravel-deployment-787568f8f4-hxdsz -- php artisan view:clear
"

Write-Host "Done!"
