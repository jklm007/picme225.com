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

# Check ad_contents for campaign 1
cmd = f"""kubectl exec {pod} -- php artisan tinker --execute="\\$c = \\App\\\\Models\\\\AdContent::where('ad_campaign_id', 1)->first(); if(\\$c) {{ echo 'IMAGE:' . \\$c->image_url . ' | VIDEO:' . \\$c->video_url; }} else {{ echo 'NO CONTENT FOUND'; }}" 2>&1"""
stdin, stdout, stderr = client.exec_command(cmd)
print("Content for campaign 1:")
print(stdout.read().decode())
print(stderr.read().decode())

# Check storage env
cmd2 = f"kubectl exec {pod} -- sh -c \"env | grep -E 'FILESYSTEM|AWS_BUCKET|S3_BUCKET|APP_URL'\""
stdin, stdout, stderr = client.exec_command(cmd2)
print("\nEnv vars:")
print(stdout.read().decode())

client.close()
