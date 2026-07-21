import os
import requests
import json
import paramiko
from PIL import Image
import io
import base64

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

# Create a simple red 100x100 image
img = Image.new('RGB', (100, 100), color = 'red')
img_byte_arr = io.BytesIO()
img.save(img_byte_arr, format='JPEG')
img_bytes = img_byte_arr.getvalue()
b64_string = base64.b64encode(img_bytes).decode('utf-8')

data_uri = f"data:image/jpeg;base64,{b64_string}"

headers = {
    "Authorization": f"Bearer {api_key}",
    "Content-Type": "application/json"
}

print("Testing Groq Vision API with BASE64 (100x100)...")
r = requests.post("https://api.groq.com/openai/v1/chat/completions", headers=headers, json={
    "model": "meta-llama/llama-4-scout-17b-16e-instruct",
    "messages": [
        {
            "role": "user",
            "content": [
                {"type": "text", "text": "What color is this image?"},
                {
                    "type": "image_url",
                    "image_url": {
                        "url": data_uri
                    }
                }
            ]
        }
    ]
})

print("Status:", r.status_code)
print("Response:", r.text)
