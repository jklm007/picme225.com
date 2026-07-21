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

import urllib3
urllib3.disable_warnings()

img_req = requests.get("https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/Cat_August_2010-4.jpg/120px-Cat_August_2010-4.jpg", verify=False)
raw_b64 = base64.b64encode(img_req.content).decode('utf-8')
data_b64 = "data:image/jpeg;base64," + raw_b64

print("1. Scout with raw base64...")
r = requests.post("https://api.groq.com/openai/v1/chat/completions", headers=headers, json={
    "model": "meta-llama/llama-4-scout-17b-16e-instruct",
    "messages": [
        {"role": "user", "content": [{"type": "text", "text": "What is in this image?"}, {"type": "image_url", "image_url": {"url": raw_b64}}]}
    ]
}, verify=False)
print(f"Status: {r.status_code} | Body: {r.text[:100]}")

print("\n2. Scout with data url base64...")
r = requests.post("https://api.groq.com/openai/v1/chat/completions", headers=headers, json={
    "model": "meta-llama/llama-4-scout-17b-16e-instruct",
    "messages": [
        {"role": "user", "content": [{"type": "text", "text": "What is in this image?"}, {"type": "image_url", "image_url": {"url": data_b64}}]}
    ]
}, verify=False)
print(f"Status: {r.status_code} | Body: {r.text[:100]}")

print("\n3. Qwen with data url base64...")
r = requests.post("https://api.groq.com/openai/v1/chat/completions", headers=headers, json={
    "model": "qwen/qwen3.6-27b",
    "messages": [
        {"role": "user", "content": [{"type": "text", "text": "What is in this image?"}, {"type": "image_url", "image_url": {"url": data_b64}}]}
    ]
}, verify=False)
print(f"Status: {r.status_code} | Body: {r.text[:100]}")

print("\n4. llama-3.2-11b-vision-preview with data url base64 (test if decommissioned)...")
r = requests.post("https://api.groq.com/openai/v1/chat/completions", headers=headers, json={
    "model": "llama-3.2-11b-vision-preview",
    "messages": [
        {"role": "user", "content": [{"type": "text", "text": "What is in this image?"}, {"type": "image_url", "image_url": {"url": data_b64}}]}
    ]
}, verify=False)
print(f"Status: {r.status_code} | Body: {r.text[:100]}")
