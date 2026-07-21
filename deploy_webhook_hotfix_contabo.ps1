$server = "root@109.199.123.69"
$sshOptions = "-o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null"

Write-Host "Transfert de WhatsAppWebhookController.php vers $server..."
# Using cmd /c to ensure options are passed correctly if ssh/scp is native windows
cmd /c "scp -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null app\Http\Controllers\Api\WhatsAppWebhookController.php ${server}:/tmp/WhatsAppWebhookController.php"

Write-Host "Déploiement dans les pods Kubernetes..."
$sshCommand = @"
LARAVEL_PODS=`$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
WORKER_PODS=`$(kubectl get pods -l app=laravel-worker --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)

echo "Copie dans les pods web..."
for POD in `$LARAVEL_PODS; do
    kubectl cp /tmp/WhatsAppWebhookController.php default/`$POD:/app/app/Http/Controllers/Api/WhatsAppWebhookController.php
done

echo "Copie dans les pods worker..."
for POD in `$WORKER_PODS; do
    kubectl cp /tmp/WhatsAppWebhookController.php default/`$POD:/app/app/Http/Controllers/Api/WhatsAppWebhookController.php
    kubectl exec `$POD -- php artisan queue:restart
done
echo "Terminé !"
"@

# Write sshCommand to a temp file to avoid quoting issues
$sshCommand | Out-File -FilePath .\temp_ssh_cmd.sh -Encoding UTF8
cmd /c "scp -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null .\temp_ssh_cmd.sh ${server}:/tmp/temp_ssh_cmd.sh"
cmd /c "ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null $server bash /tmp/temp_ssh_cmd.sh"
Remove-Item .\temp_ssh_cmd.sh

Write-Host "✅ Mise à jour appliquée avec succès sur Contabo !"
