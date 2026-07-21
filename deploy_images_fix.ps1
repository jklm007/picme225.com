$zone = "europe-west9-a"
$server = "k3s-master-gcp"

Write-Host "Uploading modified files..."
gcloud compute scp app\Jobs\ProcessWhatsappBatchJob.php ${server}:/tmp/ProcessWhatsappBatchJob.php --zone=$zone --quiet
gcloud compute scp app\Models\MarketplaceListing.php ${server}:/tmp/MarketplaceListing.php --zone=$zone --quiet
gcloud compute scp resources\views\admin\whatsapp\index.blade.php ${server}:/tmp/whatsapp_index.blade.php --zone=$zone --quiet
gcloud compute scp resources\views\admin\marketplace\listings\index.blade.php ${server}:/tmp/marketplace_index.blade.php --zone=$zone --quiet
gcloud compute scp deploy_images_fix.sh ${server}:/tmp/deploy_images_fix.sh --zone=$zone --quiet

Write-Host "Applying to k3s pods..."
gcloud compute ssh $server --zone=$zone --command "bash /tmp/deploy_images_fix.sh"
Write-Host "Deployment completed!"
