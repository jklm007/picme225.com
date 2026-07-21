import paramiko
import urllib.request
import urllib.error
import sys

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23', timeout=15)

images = [
    'service/taxi.png',
    'service/eco_partage.png',
    'service/livraison.png',
    'service/location.png',
    'service/urgence.png',
    'service/voyage.png',
    'service/taxi_vtc.webp',
    'service/van.webp',
    'service/woro-woro.webp',
    'service/ambulance.webp',
    'service/moto.webp',
]

base_url = 'https://media.picme225.site'
print("=== Testing R2 CDN URLs ===")
for img in images:
    url = f'{base_url}/{img}'
    try:
        req = urllib.request.Request(url, method='HEAD')
        with urllib.request.urlopen(req, timeout=10) as resp:
            print(f"  OK {url} -> {resp.status}")
    except urllib.error.HTTPError as e:
        print(f"  FAIL {url} -> HTTP {e.code}")
    except Exception as e:
        print(f"  ERROR {url} -> {str(e)[:80]}")

# Check local files on pod
print("\n=== Local files in pod ===")
local_paths = ['/app/public/service', '/app/storage/app/public/service', '/app/public/images']
for path in local_paths:
    stdin, stdout, stderr = client.exec_command(f'kubectl exec laravel-deployment-56f54497f-r8pmg -- ls {path} 2>/dev/null || echo MISSING')
    out = stdout.read().decode().strip()
    print(f"  {path}: {out[:300]}")

stdin3, stdout3, _ = client.exec_command('kubectl exec laravel-deployment-56f54497f-r8pmg -- printenv AWS_ENDPOINT')
endpoint = stdout3.read().decode().strip()
print(f"\nAWS_ENDPOINT: {endpoint}")

stdin4, stdout4, _ = client.exec_command('kubectl exec laravel-deployment-56f54497f-r8pmg -- printenv AWS_BUCKET')
bucket = stdout4.read().decode().strip()
print(f"AWS_BUCKET: {bucket}")

# Test direct S3 endpoint
r2_test = f"https://{bucket}.45dae7ec0d11d6baef63481feb03aa7d.r2.cloudflarestorage.com/service/taxi.png"
print(f"\nDirect R2: {r2_test}")

client.close()
