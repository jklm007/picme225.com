import os
import requests
import json

api_key = os.getenv("GROQ_API_KEY", "")
if not api_key:
    import paramiko
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

import urllib3
urllib3.disable_warnings()

img_url = "https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png"

payload_url = {
    "model": "meta-llama/llama-4-scout-17b-16e-instruct",
    "messages": [
        {
            "role": "user",
            "content": [
                {"type": "text", "text": "What is in this image?"},
                {"type": "image_url", "image_url": {"url": img_url}}
            ]
        }
    ],
    "temperature": 0.1
}

print("Testing Google Logo URL...")
r1 = requests.post("https://api.groq.com/openai/v1/chat/completions", headers=headers, json=payload_url, verify=False)
print(f"Status URL: {r1.status_code}")
print(f"Body URL: {r1.text[:200]}")
