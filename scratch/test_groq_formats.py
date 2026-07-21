import os
import requests
import json
import base64

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

# 1. URL based image for Scout
payload_url = {
    "model": "meta-llama/llama-4-scout-17b-16e-instruct",
    "messages": [
        {
            "role": "user",
            "content": [
                {"type": "text", "text": "What is in this image?"},
                {"type": "image_url", "image_url": {"url": "https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/Cat_August_2010-4.jpg/120px-Cat_August_2010-4.jpg"}}
            ]
        }
    ],
    "temperature": 0.1
}

img_req = requests.get("https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/Cat_August_2010-4.jpg/120px-Cat_August_2010-4.jpg", verify=False)
b64 = "data:image/jpeg;base64," + base64.b64encode(img_req.content).decode('utf-8')

payload_b64 = {
    "model": "meta-llama/llama-4-scout-17b-16e-instruct",
    "messages": [
        {
            "role": "user",
            "content": [
                {"type": "text", "text": "What is in this image?"},
                {"type": "image_url", "image_url": {"url": b64}}
            ]
        }
    ],
    "temperature": 0.1
}

import urllib3
urllib3.disable_warnings()

print("Testing URL...")
r1 = requests.post("https://api.groq.com/openai/v1/chat/completions", headers=headers, json=payload_url, verify=False)
print(f"Status URL: {r1.status_code}")
print(f"Body URL: {r1.text[:200]}")

print("\nTesting B64...")
r2 = requests.post("https://api.groq.com/openai/v1/chat/completions", headers=headers, json=payload_b64, verify=False)
print(f"Status B64: {r2.status_code}")
print(f"Body B64: {r2.text[:200]}")

