import paramiko
import os
import sys

host = '109.199.123.69'
user = 'root'
password = 'Charlotte23'

files_to_deploy = [
    ('resources/views/admin/marketplace/listings/create.blade.php', '/tmp/create.blade.php'),
    ('app/Http/Controllers/MarketplaceTicketController.php', '/tmp/MarketplaceTicketController.php'),
    ('app/Http/Controllers/Api/EventAgentController.php', '/tmp/EventAgentController.php'),
    ('app/Http/Controllers/SocialTicketController.php', '/tmp/SocialTicketController.php'),
]

commands = """
LARAVEL_PODS=$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
WORKER_PODS=$(kubectl get pods -l app=laravel-worker --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
ALL_PODS="$LARAVEL_PODS $WORKER_PODS"

for POD in $ALL_PODS; do
    echo "Copie dans $POD..."
    kubectl cp /tmp/create.blade.php default/$POD:/app/resources/views/admin/marketplace/listings/create.blade.php
    kubectl cp /tmp/MarketplaceTicketController.php default/$POD:/app/app/Http/Controllers/MarketplaceTicketController.php
    kubectl cp /tmp/EventAgentController.php default/$POD:/app/app/Http/Controllers/Api/EventAgentController.php
    kubectl cp /tmp/SocialTicketController.php default/$POD:/app/app/Http/Controllers/SocialTicketController.php

    echo "Optimisation et cache sur $POD"
    kubectl exec $POD -- php artisan view:clear
done

echo "DEPLOIEMENT TERMINE"
"""

print("Connexion au serveur Contabo via SSH...")
try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, username=user, password=password)

    sftp = ssh.open_sftp()
    print("Envoi des fichiers vers /tmp...")
    for local_path, remote_path in files_to_deploy:
        if os.path.exists(local_path):
            sftp.put(local_path, remote_path)
            print(f"Envoyé : {local_path}")
        else:
            print(f"ATTENTION : Fichier local manquant {local_path}")
    sftp.close()

    print("Exécution des commandes Kubernetes (mise à jour des pods)...")
    stdin, stdout, stderr = ssh.exec_command(commands)
    
    # Affichage en temps réel
    for line in stdout:
        sys.stdout.write(line)
        sys.stdout.flush()
        
    err = stderr.read().decode()
    if err:
        print("Erreurs SSH :", err)

    ssh.close()
    print("\nMise à jour terminée avec succès !")

except Exception as e:
    print(f"Erreur de connexion/déploiement : {str(e)}")
