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

# Get the pod name
stdin, stdout, stderr = client.exec_command(
    "kubectl get pods -l app=laravel-app -o jsonpath='{.items[0].metadata.name}'"
)
pod = stdout.read().decode().strip().strip("'")
print(f"Pod: {pod}")

# Check what's in ad_contents for campaign 1
cmd = f"""kubectl exec {pod} -- php artisan tinker --execute="
\\$contents = \\App\\\\Models\\\\AdContent::where('ad_campaign_id', 1)->get(['id','content_type','image_url','video_url']);
foreach(\\$contents as \\$c) {{
    echo 'ID:'  . \\$c->id . ' | TYPE:' . \\$c->content_type . ' | IMAGE:' . \\$c->image_url . ' | VIDEO:' . \\$c->video_url . PHP_EOL;
}}
" 2>&1"""
stdin, stdout, stderr = client.exec_command(cmd)
print("ad_contents for campaign 1:")
print(stdout.read().decode())

# Check FILESYSTEM_DISK and S3 config
cmd2 = f"kubectl exec {pod} -- env | grep -E 'FILESYSTEM|AWS_|S3_'"
stdin, stdout, stderr = client.exec_command(cmd2)
print("\nStorage config:")
print(stdout.read().decode())

client.close()
