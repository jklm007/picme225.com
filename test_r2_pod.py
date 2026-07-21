import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Écrire le script via stdin pour éviter les problèmes de heredoc
php_script = b"""<?php
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

try {
    Illuminate\\Support\\Facades\\Storage::disk('s3')->put('test-r2.txt', 'Hello from PicMe225 pod!');
    $content = Illuminate\\Support\\Facades\\Storage::disk('s3')->get('test-r2.txt');
    Illuminate\\Support\\Facades\\Storage::disk('s3')->delete('test-r2.txt');
    echo "R2 OK! Contenu: " . $content . PHP_EOL;
} catch (Exception $e) {
    echo "ERREUR R2: " . $e->getMessage() . PHP_EOL;
}
"""

# Obtenir le nom du pod
stdin, stdout, stderr = client.exec_command("kubectl get pod -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod_name = stdout.read().decode().strip().strip("'")
print(f"Pod: {pod_name}")

# Copier le script dans le pod via kubectl cp
sftp = client.open_sftp()
sftp.putfo(__import__('io').BytesIO(php_script), '/tmp/test_r2.php')
sftp.close()

# kubectl cp depuis le noeud vers le pod
stdin, stdout, stderr = client.exec_command(f"kubectl cp /tmp/test_r2.php {pod_name}:/tmp/test_r2.php")
stdout.read(); stderr.read()

# Exécuter le script
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod_name} -- php /tmp/test_r2.php 2>&1")
out = stdout.read().decode('utf-8', errors='ignore')
print("=== RÉSULTAT ===")
print(out or "(vide)")

client.close()
