import requests

# Test a known S3 url from the logs or DB
url = "https://picme225-storage.s3.eu-west-3.amazonaws.com/ads/ad_669528f804561.jpg" # Example, I don't know an exact url
# Let me query a real url from ad_contents
import paramiko
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')
pod = "laravel-deployment-56f54497f-r8pmg"
cmd = f"""kubectl exec {pod} -- php -r "
\\$pdo = new PDO(getenv('DB_CONNECTION') . ':host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
\\$row = \\$pdo->query('SELECT image_url FROM ad_contents WHERE image_url LIKE \'%s3%\' ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
echo \\$row ? \\$row['image_url'] : 'NO_URL';
" 2>&1"""
stdin, stdout, stderr = client.exec_command(cmd)
real_url = stdout.read().decode().strip()
client.close()

print(f"Real URL: {real_url}")
if real_url and real_url != 'NO_URL':
    r = requests.get(real_url)
    print("S3 fetch status:", r.status_code)
