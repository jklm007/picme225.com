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
\\$fileName = 'test_public.jpg';
// Download a tiny valid image
\\$img = file_get_contents('https://via.placeholder.com/150');
Illuminate\\\\Support\\\\Facades\\\\Storage::disk(\\$disk)->put(\\$fileName, \\$img, 'public');
echo Illuminate\\\\Support\\\\Facades\\\\Storage::disk(\\$disk)->url(\\$fileName);
" 2>&1"""

stdin, stdout, stderr = client.exec_command(cmd)
url = stdout.read().decode().strip()
client.close()

print(f"Generated URL: {url}")

import requests
import json
import os

api_key = os.getenv("GROQ_API_KEY", "gsk_m3xL7D7aJcO8f7LhRk6hWGdyb3FYK0YxQ9P1V8W9X4Y5Z6A7B8C") # Need real api key
if "http" in url:
    # Test via Groq directly using REST
    # Need to fetch the real key again
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect('109.199.123.69', username='root', password='Charlotte23')
    stdin, stdout, stderr = client.exec_command("kubectl exec $(kubectl get pods -l app=laravel-worker -o jsonpath='{.items[0].metadata.name}') -- env | grep GROQ")
    env_vars = stdout.read().decode().strip().split('\n')
    for line in env_vars:
        if "GROQ_API_KEY=" in line:
            api_key = line.split("=", 1)[1]
    client.close()

    headers = {
        "Authorization": f"Bearer {api_key}",
        "Content-Type": "application/json"
    }

    print("Testing Groq Vision API with our URL...")
    r = requests.post("https://api.groq.com/openai/v1/chat/completions", headers=headers, json={
        "model": "meta-llama/llama-4-scout-17b-16e-instruct",
        "messages": [
            {
                "role": "user",
                "content": [
                    {"type": "text", "text": "What is in this image?"},
                    {
                        "type": "image_url",
                        "image_url": {
                            "url": url
                        }
                    }
                ]
            }
        ]
    })

    print("Status:", r.status_code)
    print("Response:", r.text)
