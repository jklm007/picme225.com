import os
import requests
import json
import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

stdin, stdout, stderr = client.exec_command("kubectl exec $(kubectl get pods -l app=laravel-worker -o jsonpath='{.items[0].metadata.name}') -- env | grep GROQ")
env_vars = stdout.read().decode().strip().split('\n')
api_key = ""
for line in env_vars:
    if "GROQ_API_KEY=" in line:
        api_key = line.split("=", 1)[1]
client.close()

headers = {
    "Authorization": f"Bearer {api_key}",
    "Content-Type": "application/json"
}

r = requests.get("https://api.groq.com/openai/v1/models", headers=headers)
models = r.json().get('data', [])
for m in models:
    print(m['id'])
