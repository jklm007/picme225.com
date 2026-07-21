import paramiko
import sys
import os

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

local_file = r'app\Http\Controllers\Api\WhatsAppWebhookController.php'
remote_file = '/tmp/WhatsAppWebhookController.php'

commands = """
LARAVEL_PODS=$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
WORKER_PODS=$(kubectl get pods -l app=laravel-worker --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)

echo "Copie dans les pods web..."
for POD in $LARAVEL_PODS; do
    kubectl cp /tmp/WhatsAppWebhookController.php default/$POD:/app/app/Http/Controllers/Api/WhatsAppWebhookController.php
    echo "  -> Copié dans $POD"
done

echo "Copie dans les pods worker..."
for POD in $WORKER_PODS; do
    kubectl cp /tmp/WhatsAppWebhookController.php default/$POD:/app/app/Http/Controllers/Api/WhatsAppWebhookController.php
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
    
    print("Transfert du fichier WhatsAppWebhookController.php...")
    sftp = client.open_sftp()
    sftp.put(local_file, remote_file)
    sftp.close()
    print("Fichier transféré avec succès dans /tmp.")
    
    print("Déploiement dans les pods Kubernetes...")
    stdin, stdout, stderr = client.exec_command(commands)
    
    # Read the output line by line
    for line in stdout:
        print(line.strip())
        
    error = stderr.read().decode().strip()
    if error:
        print("Erreurs rencontrées:")
        print(error)
        
    client.close()
    print("✅ Déploiement complètement terminé.")

except Exception as e:
    print(f"Une erreur s'est produite : {e}")
    sys.exit(1)
