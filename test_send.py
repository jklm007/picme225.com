import paramiko
import sys
import json
import time

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    client.connect(hostname, username=username, password=password)
    
    # 1. Test sending to the user WITHOUT options (to JID)
    payload1 = json.dumps({"number": "22577436121@s.whatsapp.net", "text": "Test notification from PicMe225 (No Options)"})
    cmd1 = f"kubectl exec deploy/laravel-worker -- curl -s -X POST -H 'apikey: 6BE2FB65-6676-48FB-BE16-162621590D6A' -H 'Content-Type: application/json' -d '{payload1}' http://evolution-api-service:8080/message/sendText/picme_whatsapp"
    stdin, stdout, stderr = client.exec_command(cmd1)
    print("Test 1 (JID No Options):", stdout.read().decode('utf-8'))
    
    time.sleep(2)
    
    # 2. Test sending to the user WITH options (to JID)
    payload2 = json.dumps({"number": "22577436121@s.whatsapp.net", "text": "Test notification from PicMe225 (With Options)", "options": {"delay": 1200, "presence": "composing"}})
    cmd2 = f"kubectl exec deploy/laravel-worker -- curl -s -X POST -H 'apikey: 6BE2FB65-6676-48FB-BE16-162621590D6A' -H 'Content-Type: application/json' -d '{payload2}' http://evolution-api-service:8080/message/sendText/picme_whatsapp"
    stdin, stdout, stderr = client.exec_command(cmd2)
    print("Test 2 (JID With Options):", stdout.read().decode('utf-8'))

    time.sleep(2)
    
    # 3. Test sending to the LID No Options
    payload3 = json.dumps({"number": "195923657408723@lid", "text": "Test notification from PicMe225 (LID No Options)"})
    cmd3 = f"kubectl exec deploy/laravel-worker -- curl -s -X POST -H 'apikey: 6BE2FB65-6676-48FB-BE16-162621590D6A' -H 'Content-Type: application/json' -d '{payload3}' http://evolution-api-service:8080/message/sendText/picme_whatsapp"
    stdin, stdout, stderr = client.exec_command(cmd3)
    print("Test 3 (LID No Options):", stdout.read().decode('utf-8'))
    
except Exception as e:
    print(f"Connection failed: {e}")
finally:
    client.close()
