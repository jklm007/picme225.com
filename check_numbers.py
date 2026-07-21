import paramiko
import sys
import json

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    client.connect(hostname, username=username, password=password)
    
    payload = json.dumps({"numbers": ["22577436121", "225077436121", "2250714950219", "225714950219"]})
    curl_cmd = f"curl -s -X POST -H 'apikey: 6BE2FB65-6676-48FB-BE16-162621590D6A' -H 'Content-Type: application/json' -d '{payload}' http://10.43.20.10:8080/chat/whatsappNumbers/picme_whatsapp"
    # Wait, the internal IP is not known, but I can just exec into the laravel pod and run curl!
    cmd = f"kubectl exec deploy/laravel-worker -- curl -s -X POST -H 'apikey: 6BE2FB65-6676-48FB-BE16-162621590D6A' -H 'Content-Type: application/json' -d '{payload}' http://evolution-api-service:8080/chat/whatsappNumbers/picme_whatsapp"
    
    stdin, stdout, stderr = client.exec_command(cmd)
    
    out = stdout.read().decode('utf-8')
    err = stderr.read().decode('utf-8')
    
    print("OUTPUT:", out)
    if err:
        print("ERROR:", err)
    
except Exception as e:
    print(f"Connection failed: {e}")
finally:
    client.close()
