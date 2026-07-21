import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Lire les valeurs R2 actuelles en base
stdin, stdout, stderr = client.exec_command(
    "kubectl exec deployment/laravel-deployment -- php artisan "
    "--no-interaction eval \"$keys=['r2_access_key','r2_secret_key','r2_endpoint','r2_bucket'];"
    "foreach($keys as $k){echo $k.': '.Setting::get($k,'(vide)').PHP_EOL;}\" 2>&1"
)

# On passe par une requête psql directe
stdin, stdout, stderr = client.exec_command(
    "kubectl exec deployment/postgres-0 -- psql -U picme_user -d picme_db "
    "-c \"SELECT key, value FROM settings WHERE key LIKE 'r2%' ORDER BY key;\" 2>&1"
)
out = stdout.read().decode('utf-8', errors='ignore')
err = stderr.read().decode('utf-8', errors='ignore')
print("=== Settings R2 actuels en base ===")
print(out or "(aucun résultat)")
if err and 'no such object' not in err:
    print("ERR:", err[:300])

client.close()
