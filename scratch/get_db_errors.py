import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

cmd = """kubectl exec $(kubectl get pods -l app=laravel-worker -o jsonpath='{.items[0].metadata.name}') -- php -r "
\\$pdo = new PDO(getenv('DB_CONNECTION') . ':host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
\\$stmt = \\$pdo->query('SELECT id, error_log FROM whatsapp_messages WHERE status = \\'failed\\' ORDER BY id DESC LIMIT 5');
while (\\$row = \\$stmt->fetch(PDO::FETCH_ASSOC)) {
    echo 'MSG ' . \\$row['id'] . ': ' . \\$row['error_log'] . \\"\n\\";
}
" 2>&1"""
stdin, stdout, stderr = client.exec_command(cmd)
print(stdout.read().decode())
client.close()
