import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

pod = "laravel-deployment-56f54497f-r8pmg"
cmd = f"""kubectl exec {pod} -- php -r "
require '/app/vendor/autoload.php';
\\$app = require '/app/bootstrap/app.php';
\\$app->make('Illuminate\\\\Contracts\\\\Console\\\\Kernel')->bootstrap();
\\$disk = env('FILESYSTEM_DISK', 's3');
\\$fileName = 'test_public.txt';
Illuminate\\\\Support\\\\Facades\\\\Storage::disk(\\$disk)->put(\\$fileName, 'hello world', 'public');
echo Illuminate\\\\Support\\\\Facades\\\\Storage::disk(\\$disk)->url(\\$fileName);
" 2>&1"""

stdin, stdout, stderr = client.exec_command(cmd)
url = stdout.read().decode().strip()
client.close()

print(f"Generated URL: {url}")
import requests
if "http" in url:
    r = requests.get(url)
    print(f"Status: {r.status_code}")
    print(f"Body: {r.text}")
