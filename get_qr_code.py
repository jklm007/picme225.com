import paramiko, json, time

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Step 1: Logout instance
print("Logging out instance...")
cmd_logout = """kubectl exec deploy/laravel-deployment -- curl -s -X DELETE http://evolution-api-service:8080/instance/logout/picme_whatsapp -H "apikey: picme225-evolution-secret-key" """
stdin_out, stdout_out, stderr_out = client.exec_command(cmd_logout)
print("Logout Response:", stdout_out.read().decode('utf-8', errors='replace'))

# Wait a moment for logout to complete
time.sleep(3)

# Step 2: Get connection status / QR code
print("Connecting instance to fetch QR code...")
cmd_connect = """kubectl exec deploy/laravel-deployment -- curl -s -X GET http://evolution-api-service:8080/instance/connect/picme_whatsapp -H "apikey: picme225-evolution-secret-key" """
stdin_conn, stdout_conn, stderr_conn = client.exec_command(cmd_connect)
res = stdout_conn.read().decode('utf-8', errors='replace')

try:
    data = json.loads(res)
    # Save the base64 QR code if present
    if "base64" in data:
        with open("qrcode_base64.txt", "w") as f:
            f.write(data["base64"])
        print("QR Code Base64 saved successfully.")
    else:
        print("Response does not contain base64 QR code:", res)
except Exception as e:
    print("Error parsing response:", e)
    print("Raw Response:", res)

client.close()
