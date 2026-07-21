Write-Host "Téléchargement des fichiers modifiés vers k3s-master-gcp:/tmp..."

$zone = "europe-west9-a"
$server = "k3s-master-gcp"

# Scripts et Controllers
gcloud compute scp deploy_wa_market.sh ${server}:/tmp/deploy_wa_market.sh --zone=$zone --quiet
gcloud compute scp app\Http\Controllers\Api\WhatsAppWebhookController.php ${server}:/tmp/WhatsAppWebhookController.php --zone=$zone --quiet
gcloud compute scp app\Http\Controllers\Admin\WhatsappGroupController.php ${server}:/tmp/WhatsappGroupController.php --zone=$zone --quiet

# Models et Jobs
gcloud compute scp app\Models\WhatsappMessage.php ${server}:/tmp/WhatsappMessage.php --zone=$zone --quiet
gcloud compute scp app\Models\WhatsappGroup.php ${server}:/tmp/WhatsappGroup.php --zone=$zone --quiet
gcloud compute scp app\Models\MarketplaceListing.php ${server}:/tmp/MarketplaceListing.php --zone=$zone --quiet
gcloud compute scp app\Jobs\ProcessWhatsappBatchJob.php ${server}:/tmp/ProcessWhatsappBatchJob.php --zone=$zone --quiet
gcloud compute scp app\Jobs\PostToSocialMediaJob.php ${server}:/tmp/PostToSocialMediaJob.php --zone=$zone --quiet
gcloud compute scp app\Http\Controllers\HomeController.php ${server}:/tmp/HomeController.php --zone=$zone --quiet
gcloud compute scp app\Services\AiFailoverService.php ${server}:/tmp/AiFailoverService.php --zone=$zone --quiet

# Routes, Vues et Config
gcloud compute scp routes\admin.php ${server}:/tmp/admin.php --zone=$zone --quiet
gcloud compute scp routes\web.php ${server}:/tmp/web.php --zone=$zone --quiet
gcloud compute scp config\services.php ${server}:/tmp/services.php --zone=$zone --quiet
gcloud compute scp resources\views\admin\whatsapp_groups\index.blade.php ${server}:/tmp/index.blade.php --zone=$zone --quiet
gcloud compute scp resources\views\admin\whatsapp_groups\create.blade.php ${server}:/tmp/create.blade.php --zone=$zone --quiet
gcloud compute scp resources\views\admin\whatsapp_groups\edit.blade.php ${server}:/tmp/edit.blade.php --zone=$zone --quiet
gcloud compute scp resources\views\admin\include\nav.blade.php ${server}:/tmp/nav.blade.php --zone=$zone --quiet
gcloud compute scp resources\views\marketplace\detail.blade.php ${server}:/tmp/detail.blade.php --zone=$zone --quiet

# Vues Admin Settings
gcloud compute scp app\Http\Controllers\Admin\ApkSettingsController.php ${server}:/tmp/ApkSettingsController.php --zone=$zone --quiet
gcloud compute scp resources\views\admin\settings\apks.blade.php ${server}:/tmp/apks.blade.php --zone=$zone --quiet
gcloud compute scp app\Http\Controllers\Admin\SocialSettingsController.php ${server}:/tmp/SocialSettingsController.php --zone=$zone --quiet
gcloud compute scp resources\views\admin\settings\social.blade.php ${server}:/tmp/social.blade.php --zone=$zone --quiet
gcloud compute scp app\Console\Commands\SetEvolutionWebhookCommand.php ${server}:/tmp/SetEvolutionWebhookCommand.php --zone=$zone --quiet

# APKs par défaut générés par lancer_pickme.py
$userApkPath = "..\PickeMe.PRO_andoid\app\build\outputs\apk\debug\app-debug.apk"
$driverApkPath = "..\picmeDriver_androix\app\build\outputs\apk\debug\app-debug.apk"

# Demander s'il faut uploader les APKs
$uploadApks = "N"

if ($uploadApks -match '^[oOyY]') {
    if (Test-Path $userApkPath) {
        Write-Host "Transfert de l'APK Utilisateur par défaut..."
        gcloud compute scp $userApkPath ${server}:/tmp/user_apk_default.apk --zone=$zone --quiet
    }
    if (Test-Path $driverApkPath) {
        Write-Host "Transfert de l'APK Chauffeur par défaut..."
        gcloud compute scp $driverApkPath ${server}:/tmp/driver_apk_default.apk --zone=$zone --quiet
    }
} else {
    Write-Host "Transfert des APKs ignoré pour accélérer le déploiement."
}

# Migrations
gcloud compute scp database\migrations\2026_06_30_081241_create_whatsapp_groups_table.php ${server}:/tmp/2026_06_30_081241_create_whatsapp_groups_table.php --zone=$zone --quiet
gcloud compute scp database\migrations\2026_06_30_081552_add_batch_processed_to_whatsapp_messages_table.php ${server}:/tmp/2026_06_30_081552_add_batch_processed_to_whatsapp_messages_table.php --zone=$zone --quiet
gcloud compute scp database\migrations\2026_06_30_233000_add_sub_category_to_marketplace_listings_table.php ${server}:/tmp/2026_06_30_233000_add_sub_category_to_marketplace_listings_table.php --zone=$zone --quiet
gcloud compute scp database\migrations\2026_07_19_151040_add_message_id_to_whatsapp_messages_table.php ${server}:/tmp/2026_07_19_151040_add_message_id_to_whatsapp_messages_table.php --zone=$zone --quiet

Write-Host "Fichiers transférés. Lancement du déploiement sur le serveur K3S..."
gcloud compute ssh $server --zone=$zone --command "bash /tmp/deploy_wa_market.sh"
Write-Host "Déploiement terminé localement !"
