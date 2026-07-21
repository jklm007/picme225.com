import paramiko
import sys
import os

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

files_to_upload = {
    r'app\Http\Controllers\Api\WhatsAppWebhookController.php': '/tmp/WhatsAppWebhookController.php',
    r'app\Http\Controllers\Admin\MarketplaceListingController.php': '/tmp/MarketplaceListingController.php',
    r'app\Http\Controllers\Admin\WhatsappListingController.php': '/tmp/WhatsappListingController.php',
    r'app\Jobs\ProcessWhatsappBatchJob.php': '/tmp/ProcessWhatsappBatchJob.php',
    r'config\passport.php': '/tmp/passport.php',
    r'config\filesystems.php': '/tmp/filesystems.php',
    r'app\Http\Controllers\Admin\IntegrationSettingsController.php': '/tmp/IntegrationSettingsController.php',
    r'resources\views\admin\settings\integrations.blade.php': '/tmp/integrations.blade.php',
    r'resources\views\admin\include\nav.blade.php': '/tmp/nav.blade.php',
    r'routes\admin.php': '/tmp/admin.php',
    r'app\Providers\AppServiceProvider.php': '/tmp/AppServiceProvider.php'
}

commands = """
LARAVEL_PODS=$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
WORKER_PODS=$(kubectl get pods -l app=laravel-worker --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)

echo "Copie dans les pods web..."
for POD in $LARAVEL_PODS; do
    kubectl cp /tmp/WhatsAppWebhookController.php default/$POD:/app/app/Http/Controllers/Api/WhatsAppWebhookController.php
    kubectl cp /tmp/MarketplaceListingController.php default/$POD:/app/app/Http/Controllers/Admin/MarketplaceListingController.php
    kubectl cp /tmp/WhatsappListingController.php default/$POD:/app/app/Http/Controllers/Admin/WhatsappListingController.php
    kubectl cp /tmp/ProcessWhatsappBatchJob.php default/$POD:/app/app/Jobs/ProcessWhatsappBatchJob.php
    kubectl cp /tmp/passport.php default/$POD:/app/config/passport.php
    kubectl cp /tmp/filesystems.php default/$POD:/app/config/filesystems.php
    kubectl cp /tmp/IntegrationSettingsController.php default/$POD:/app/app/Http/Controllers/Admin/IntegrationSettingsController.php
    kubectl cp /tmp/integrations.blade.php default/$POD:/app/resources/views/admin/settings/integrations.blade.php
    kubectl cp /tmp/nav.blade.php default/$POD:/app/resources/views/admin/include/nav.blade.php
    kubectl cp /tmp/admin.php default/$POD:/app/routes/admin.php
    kubectl cp /tmp/AppServiceProvider.php default/$POD:/app/app/Providers/AppServiceProvider.php
    echo "  -> Copié dans $POD"
done

echo "Copie dans les pods worker..."
for POD in $WORKER_PODS; do
    kubectl cp /tmp/WhatsAppWebhookController.php default/$POD:/app/app/Http/Controllers/Api/WhatsAppWebhookController.php
    kubectl cp /tmp/MarketplaceListingController.php default/$POD:/app/app/Http/Controllers/Admin/MarketplaceListingController.php
    kubectl cp /tmp/WhatsappListingController.php default/$POD:/app/app/Http/Controllers/Admin/WhatsappListingController.php
    kubectl cp /tmp/ProcessWhatsappBatchJob.php default/$POD:/app/app/Jobs/ProcessWhatsappBatchJob.php
    kubectl cp /tmp/passport.php default/$POD:/app/config/passport.php
    kubectl cp /tmp/filesystems.php default/$POD:/app/config/filesystems.php
    kubectl cp /tmp/IntegrationSettingsController.php default/$POD:/app/app/Http/Controllers/Admin/IntegrationSettingsController.php
    kubectl cp /tmp/integrations.blade.php default/$POD:/app/resources/views/admin/settings/integrations.blade.php
    kubectl cp /tmp/nav.blade.php default/$POD:/app/resources/views/admin/include/nav.blade.php
    kubectl cp /tmp/admin.php default/$POD:/app/routes/admin.php
    kubectl cp /tmp/AppServiceProvider.php default/$POD:/app/app/Providers/AppServiceProvider.php
    kubectl exec $POD -- php artisan queue:restart
    echo "  -> Copié et queue redémarrée dans $POD"
done
echo "Terminé !"
"""

try:
    print(f"Connexion à {hostname}...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, port=22, username=username, password=password, timeout=10)
    
    sftp = client.open_sftp()
    for local_f, remote_f in files_to_upload.items():
        print(f"Transfert de {local_f}...")
        sftp.put(local_f, remote_f)
    sftp.close()
    
    print("Déploiement dans les pods Kubernetes...")
    stdin, stdout, stderr = client.exec_command(commands)
    
    for line in stdout:
        print(line.strip())
        
    error = stderr.read().decode().strip()
    if error:
        print("Erreurs rencontrées:")
        print(error)
        
    client.close()
    print("Déploiement terminé.")

except Exception as e:
    print(f"Une erreur s'est produite : {e}")
    sys.exit(1)
