import paramiko
import os
import sys

host = '109.199.123.69'
user = 'root'
password = 'Charlotte23'

files_to_deploy = [
    ('config/services.php', '/tmp/services.php'),
    ('app/Services/AiFailoverService.php', '/tmp/AiFailoverService.php'),
    ('app/Jobs/ProcessWhatsappBatchJob.php', '/tmp/ProcessWhatsappBatchJob.php'),
    ('app/Http/Controllers/Api/WhatsAppWebhookController.php', '/tmp/WhatsAppWebhookController.php'),
    ('database/migrations/2026_07_09_082324_add_performance_indexes_to_critical_tables.php', '/tmp/2026_07_09_082324_add_performance_indexes_to_critical_tables.php'),
    ('database/migrations/2026_07_14_123337_add_status_to_services_table.php', '/tmp/2026_07_14_123337_add_status_to_services_table.php'),
    ('database/migrations/2026_07_19_151040_add_message_id_to_whatsapp_messages_table.php', '/tmp/2026_07_19_151040_add_message_id_to_whatsapp_messages_table.php'),
    ('resources/views/user/home.blade.php', '/tmp/home.blade.php'),
    ('resources/views/user/dashboard.blade.php', '/tmp/dashboard.blade.php'),
    ('resources/views/user/account/profile.blade.php', '/tmp/profile.blade.php'),
    ('resources/views/user/account/edit_profile.blade.php', '/tmp/edit_profile.blade.php'),
    ('resources/views/user/layout/base.blade.php', '/tmp/base.blade.php'),
    ('resources/views/user/layout/app.blade.php', '/tmp/app.blade.php'),
    ('resources/views/user/layout/user_dashboard.blade.php', '/tmp/user_dashboard.blade.php'),
    ('app/Http/Controllers/UserDashboardController.php', '/tmp/UserDashboardController.php'),
    ('app/Http/Controllers/UserMarketplaceController.php', '/tmp/UserMarketplaceController.php'),
    ('app/Helper/ViewHelper.php', '/tmp/ViewHelper.php'),
    ('app/Models/MarketplaceListing.php', '/tmp/MarketplaceListing.php'),
    ('routes/web.php', '/tmp/web.php'),
    ('resources/views/user/marketplace/detail.blade.php', '/tmp/detail.blade.php'),
    ('resources/views/user/marketplace/create.blade.php', '/tmp/create.blade.php'),
    ('resources/views/user/marketplace/explore.blade.php', '/tmp/explore.blade.php'),
    ('resources/views/user/marketplace/my_listings.blade.php', '/tmp/my_listings.blade.php'),
    ('resources/views/marketplace/index.blade.php', '/tmp/index.blade.php'),
    ('resources/views/home.blade.php', '/tmp/public_home.blade.php'),

    ('resources/views/marketing/airport.blade.php', '/tmp/airport.blade.php'),
    ('resources/views/drive.blade.php', '/tmp/drive.blade.php'),
    ('resources/lang/fr/home.php', '/tmp/fr_home.php'),
    ('resources/lang/en/home.php', '/tmp/en_home.php')
]


commands = """
LARAVEL_PODS=$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
WORKER_PODS=$(kubectl get pods -l app=laravel-worker --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
ALL_PODS="$LARAVEL_PODS $WORKER_PODS"

for POD in $ALL_PODS; do
    echo "Copie dans $POD..."
    kubectl cp /tmp/services.php default/$POD:/app/config/services.php
    kubectl cp /tmp/AiFailoverService.php default/$POD:/app/app/Services/AiFailoverService.php
    kubectl cp /tmp/ProcessWhatsappBatchJob.php default/$POD:/app/app/Jobs/ProcessWhatsappBatchJob.php
    kubectl cp /tmp/WhatsAppWebhookController.php default/$POD:/app/app/Http/Controllers/Api/WhatsAppWebhookController.php
    kubectl cp /tmp/2026_07_09_082324_add_performance_indexes_to_critical_tables.php default/$POD:/app/database/migrations/2026_07_09_082324_add_performance_indexes_to_critical_tables.php
    kubectl cp /tmp/2026_07_14_123337_add_status_to_services_table.php default/$POD:/app/database/migrations/2026_07_14_123337_add_status_to_services_table.php
    kubectl cp /tmp/2026_07_19_151040_add_message_id_to_whatsapp_messages_table.php default/$POD:/app/database/migrations/2026_07_19_151040_add_message_id_to_whatsapp_messages_table.php
    kubectl cp /tmp/home.blade.php default/$POD:/app/resources/views/user/home.blade.php
    kubectl cp /tmp/dashboard.blade.php default/$POD:/app/resources/views/user/dashboard.blade.php
    kubectl cp /tmp/profile.blade.php default/$POD:/app/resources/views/user/account/profile.blade.php
    kubectl cp /tmp/edit_profile.blade.php default/$POD:/app/resources/views/user/account/edit_profile.blade.php
    kubectl cp /tmp/base.blade.php default/$POD:/app/resources/views/user/layout/base.blade.php
    kubectl cp /tmp/app.blade.php default/$POD:/app/resources/views/user/layout/app.blade.php
    kubectl cp /tmp/user_dashboard.blade.php default/$POD:/app/resources/views/user/layout/user_dashboard.blade.php
    kubectl cp /tmp/UserDashboardController.php default/$POD:/app/app/Http/Controllers/UserDashboardController.php
    kubectl cp /tmp/UserMarketplaceController.php default/$POD:/app/app/Http/Controllers/UserMarketplaceController.php
    kubectl cp /tmp/ViewHelper.php default/$POD:/app/app/Helper/ViewHelper.php
    kubectl cp /tmp/MarketplaceListing.php default/$POD:/app/app/Models/MarketplaceListing.php
    kubectl cp /tmp/web.php default/$POD:/app/routes/web.php
    kubectl cp /tmp/detail.blade.php default/$POD:/app/resources/views/user/marketplace/detail.blade.php
    kubectl cp /tmp/create.blade.php default/$POD:/app/resources/views/user/marketplace/create.blade.php
    kubectl cp /tmp/explore.blade.php default/$POD:/app/resources/views/user/marketplace/explore.blade.php
    kubectl cp /tmp/my_listings.blade.php default/$POD:/app/resources/views/user/marketplace/my_listings.blade.php
    kubectl cp /tmp/index.blade.php default/$POD:/app/resources/views/marketplace/index.blade.php
    kubectl cp /tmp/public_home.blade.php default/$POD:/app/resources/views/home.blade.php

    kubectl cp /tmp/airport.blade.php default/$POD:/app/resources/views/marketing/airport.blade.php
    kubectl cp /tmp/drive.blade.php default/$POD:/app/resources/views/drive.blade.php
    kubectl cp /tmp/fr_home.php default/$POD:/app/resources/lang/fr/home.php
    kubectl cp /tmp/en_home.php default/$POD:/app/resources/lang/en/home.php

    echo "Optimisation et cache sur $POD"
    kubectl exec $POD -- php artisan optimize
done

MAIN_POD=$(echo "$LARAVEL_PODS" | head -n 1)
if [ -n "$MAIN_POD" ]; then
    echo "Exécution de la migration sur $MAIN_POD..."
    kubectl exec $MAIN_POD -- php artisan migrate --force
fi

echo "Redémarrage des workers..."
for POD in $WORKER_PODS; do
    kubectl exec $POD -- php artisan queue:restart
done

echo "DEPLOIEMENT_TERMINE"
"""

print("Connexion au serveur Contabo via SSH...")
try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(host, username=user, password=password, timeout=10)
    
    print("Transfert des fichiers (SFTP)...")
    sftp = client.open_sftp()
    
    for local_path, remote_path in files_to_deploy:
        local_full_path = os.path.join(os.getcwd(), local_path.replace('/', '\\'))
        print(f" - Upload de {local_full_path} vers {remote_path}")
        sftp.put(local_full_path, remote_path)
        
    sftp.close()
    
    print("Exécution des commandes de déploiement sur le cluster K8s...")
    stdin, stdout, stderr = client.exec_command(commands)
    
    # Read output
    for line in iter(stdout.readline, ""):
        print(line, end="")
        
    for line in iter(stderr.readline, ""):
        print(line, end="", file=sys.stderr)
        
    client.close()
    print("Deploiement reussi !")

except Exception as e:
    print(f"Erreur lors du deploiement : {e}")
