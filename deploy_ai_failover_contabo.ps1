$server = "root@109.199.123.69"
$sshOptions = "-o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null"

Write-Host "Transfert des fichiers vers $server..."

$filesToDeploy = @(
    "config\services.php",
    "app\Services\AiFailoverService.php",
    "app\Jobs\ProcessWhatsappBatchJob.php",
    "app\Http\Controllers\Api\WhatsAppWebhookController.php",
    "database\migrations\2026_07_19_151040_add_message_id_to_whatsapp_messages_table.php"
)

foreach ($file in $filesToDeploy) {
    $filename = Split-Path $file -Leaf
    cmd /c "scp -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null $file ${server}:/tmp/$filename"
}

Write-Host "Déploiement dans les pods Kubernetes..."
$sshCommand = @"
LARAVEL_PODS=`$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
WORKER_PODS=`$(kubectl get pods -l app=laravel-worker --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
ALL_PODS="`$LARAVEL_PODS `$WORKER_PODS"

for POD in `$ALL_PODS; do
    echo "Copie dans `$POD..."
    kubectl cp /tmp/services.php default/`$POD:/app/config/services.php
    kubectl cp /tmp/AiFailoverService.php default/`$POD:/app/app/Services/AiFailoverService.php
    kubectl cp /tmp/ProcessWhatsappBatchJob.php default/`$POD:/app/app/Jobs/ProcessWhatsappBatchJob.php
    kubectl cp /tmp/WhatsAppWebhookController.php default/`$POD:/app/app/Http/Controllers/Api/WhatsAppWebhookController.php
    kubectl cp /tmp/2026_07_19_151040_add_message_id_to_whatsapp_messages_table.php default/`$POD:/app/database/migrations/2026_07_19_151040_add_message_id_to_whatsapp_messages_table.php

    echo "Clear cache sur `$POD"
    kubectl exec `$POD -- php artisan optimize:clear
done

# Migration sur le premier pod
MAIN_POD=`$(echo "`$LARAVEL_PODS" | head -n 1)
if [ -n "`$MAIN_POD" ]; then
    echo "Exécution de la migration sur `$MAIN_POD..."
    kubectl exec `$MAIN_POD -- php artisan migrate --force
fi

echo "Redémarrage des workers..."
for POD in `$WORKER_PODS; do
    kubectl exec `$POD -- php artisan queue:restart
done

echo "Terminé !"
"@

$sshCommand | Out-File -FilePath .\temp_ssh_cmd.sh -Encoding UTF8
cmd /c "scp -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null .\temp_ssh_cmd.sh ${server}:/tmp/temp_ssh_cmd.sh"
cmd /c "ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null $server bash /tmp/temp_ssh_cmd.sh"
Remove-Item .\temp_ssh_cmd.sh

Write-Host "✅ Mise à jour du Failover IA appliquée avec succès sur Contabo !"
