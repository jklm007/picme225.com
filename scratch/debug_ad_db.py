import paramiko
import time

for attempt in range(3):
    try:
        client = paramiko.SSHClient()
        client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        client.connect('109.199.123.69', username='root', password='Charlotte23', timeout=15)
        break
    except Exception as e:
        print(f"Attempt {attempt+1} failed: {e}")
        time.sleep(5)

pod = "laravel-deployment-56f54497f-r8pmg"

# Use mysql to check directly
cmd = f"""kubectl exec {pod} -- php -r "
\\$pdo = new PDO(getenv('DB_CONNECTION') . ':host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
\\$rows = \\$pdo->query('SELECT id, content_type, image_url, video_url FROM ad_contents WHERE ad_campaign_id = 1 LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
foreach(\\$rows as \\$r) print_r(\\$r);
" 2>&1"""
stdin, stdout, stderr = client.exec_command(cmd)
print(stdout.read().decode())

client.close()
