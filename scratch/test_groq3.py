import paramiko
import os
import json

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

# Get the GROQ_API_KEY from the pod env
stdin, stdout, stderr = client.exec_command("kubectl exec $(kubectl get pods -l app=laravel-worker -o jsonpath='{.items[0].metadata.name}') -- env | grep GROQ")
env_vars = stdout.read().decode().strip().split('\n')
api_key = ""
for line in env_vars:
    if "GROQ_API_KEY=" in line:
        api_key = line.split("=", 1)[1]

import requests

b64 = "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA="

headers = {
    "Authorization": f"Bearer {api_key}",
    "Content-Type": "application/json"
}

payload = {
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

print("Sending request to Groq...")
response = requests.post("https://api.groq.com/openai/v1/chat/completions", headers=headers, json=payload)
print(f"Status: {response.status_code}")
print(f"Body: {response.text}")
