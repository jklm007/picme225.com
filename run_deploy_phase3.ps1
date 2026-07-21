# run_deploy_phase3.ps1 - Deploiement Phase 2+3 via gcloud compute scp
$zone   = "europe-west9-a"
$server = "k3s-master-gcp"

Write-Host "=== Upload des fichiers Phase 2+3 vers $server ===" -ForegroundColor Cyan

# 1. Script de deploy cote serveur
gcloud compute scp deploy_phase3.sh ${server}:/tmp/deploy_phase3.sh --zone=$zone --quiet

# 2. Controllers
gcloud compute scp app\Http\Controllers\Api\WhatsAppWebhookController.php  ${server}:/tmp/p3/WhatsAppWebhookController.php  --zone=$zone --quiet
gcloud compute scp app\Http\Controllers\Admin\WhatsappListingController.php ${server}:/tmp/p3/WhatsappListingController.php  --zone=$zone --quiet
gcloud compute scp app\Http\Controllers\HomeController.php                  ${server}:/tmp/p3/HomeController.php              --zone=$zone --quiet

# 3. Job
gcloud compute scp app\Jobs\ProcessWhatsappBatchJob.php                     ${server}:/tmp/p3/ProcessWhatsappBatchJob.php     --zone=$zone --quiet

# 4. Routes
gcloud compute scp routes\admin.php                                          ${server}:/tmp/p3/admin.php                      --zone=$zone --quiet

# 5. Vues Marketplace (Phase 2)
gcloud compute scp resources\views\marketplace\index.blade.php               ${server}:/tmp/p3/marketplace_index.blade.php    --zone=$zone --quiet
gcloud compute scp resources\views\marketplace\detail.blade.php              ${server}:/tmp/p3/marketplace_detail.blade.php   --zone=$zone --quiet

# 6. Vue Admin Whatsapp (Phase 3 - KPIs + Blacklist)
gcloud compute scp resources\views\admin\whatsapp\index.blade.php            ${server}:/tmp/p3/whatsapp_admin_index.blade.php --zone=$zone --quiet

# 7. Migration Blacklist (Phase 3)
gcloud compute scp "database\migrations\2026_07_03_112022_add_is_blacklisted_to_whatsapp_users_table.php" `
                   "${server}:/tmp/p3/2026_07_03_112022_add_is_blacklisted_to_whatsapp_users_table.php" `
                   --zone=$zone --quiet

Write-Host "Upload termine. Lancement du script de deploiement sur le serveur..." -ForegroundColor Yellow

# 8. Creation du dossier /tmp/p3 et execution du script
gcloud compute ssh $server --zone=$zone --command "mkdir -p /tmp/p3 && chmod +x /tmp/deploy_phase3.sh && bash /tmp/deploy_phase3.sh"

Write-Host ""
Write-Host "=== DEPLOIEMENT PHASE 2+3 TERMINE ===" -ForegroundColor Green
