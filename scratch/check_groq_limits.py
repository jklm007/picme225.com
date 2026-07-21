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

r = requests.post("https://api.groq.com/openai/v1/chat/completions", headers=headers, json={
    "model": "meta-llama/llama-4-scout-17b-16e-instruct",
    "messages": [{"role": "user", "content": "Hi"}]
})

print("Headers for Scout:")
for k, v in r.headers.items():
    if 'ratelimit' in k.lower():
        print(f"{k}: {v}")

r2 = requests.post("https://api.groq.com/openai/v1/chat/completions", headers=headers, json={
    "model": "llama-3.3-70b-versatile",
    "messages": [{"role": "user", "content": "Hi"}]
})
print("\nHeaders for Llama 3.3 70B:")
for k, v in r2.headers.items():
    if 'ratelimit' in k.lower():
        print(f"{k}: {v}")
