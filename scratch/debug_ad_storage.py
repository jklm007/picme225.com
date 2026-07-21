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

# Check if the file exists in local storage in the container
cmd = f"kubectl exec {pod} -- sh -c 'ls /app/storage/app/public/ads/ 2>/dev/null || echo NO_FILES'"
stdin, stdout, stderr = client.exec_command(cmd)
print("Local storage files:", stdout.read().decode())

# Also check the S3 bucket for any ads/ files
cmd2 = f"""kubectl exec {pod} -- php -r "
require '/app/vendor/autoload.php';
\\$app = require '/app/bootstrap/app.php';
\\$app->make('Illuminate\\\\Contracts\\\\Console\\\\Kernel')->bootstrap();
\\$files = Illuminate\\\\Support\\\\Facades\\\\Storage::disk('s3')->files('ads');
echo 'S3 ads files: ' . count(\\$files) . PHP_EOL;
foreach(array_slice(\\$files, 0, 5) as \\$f) echo ' - ' . \\$f . PHP_EOL;
" 2>&1"""
stdin, stdout, stderr = client.exec_command(cmd2)
print("S3 check:", stdout.read().decode())

client.close()
