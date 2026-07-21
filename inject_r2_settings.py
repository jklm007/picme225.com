import paramiko, io

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Obtenir le nom du pod Laravel
stdin, stdout, stderr = client.exec_command("kubectl get pod -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")
print(f"Pod: {pod}")

# Script PHP qui injecte les vraies valeurs R2 dans la table settings
php = b"""<?php
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

$settings = [
    'r2_access_key' => 'ab9da087a81d703a47f95e34a0167a27',
    'r2_secret_key' => 'f3f4f9c1a0aee212d38db9d4cb412b35e776e622289f36b1bfc508f0607cf123',
    'r2_endpoint'   => 'https://45dae7ec0d11d6baef63481feb03aa7d.r2.cloudflarestorage.com',
    'r2_bucket'     => 'picme225-storage',
];

foreach ($settings as $key => $value) {
    Setting::set($key, $value);
    echo "SET $key => " . substr($value, 0, 30) . "..." . PHP_EOL;
}
Setting::save();
echo "Done! Settings R2 sauvegards en base." . PHP_EOL;
"""

# Copier le script dans le pod
sftp = client.open_sftp()
sftp.putfo(io.BytesIO(php), '/tmp/inject_r2_settings.php')
sftp.close()

stdin, stdout, stderr = client.exec_command(f"kubectl cp /tmp/inject_r2_settings.php {pod}:/tmp/inject_r2_settings.php")
stdout.read(); stderr.read()

# Exécuter
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php /tmp/inject_r2_settings.php 2>&1")
out = stdout.read().decode('utf-8', errors='ignore')
print("=== RÉSULTAT ===")
print(out)

client.close()
