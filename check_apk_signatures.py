import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Check the User APK signature
print("=== USER APK SIGNATURE ===")
cmd_user = """kubectl exec deploy/laravel-deployment -- bash -c "unzip -p public/apk/picme-user.apk META-INF/*.RSA 2>/dev/null | openssl pkcs7 -inform DER -print_certs -noout -text | grep -E 'Issuer:|Subject:|Not Before|Not After'" """
stdin, stdout, stderr = client.exec_command(cmd_user)
out_user = stdout.read().decode('utf-8', errors='replace').strip()
if not out_user:
    # try .DSA
    cmd_user_dsa = """kubectl exec deploy/laravel-deployment -- bash -c "unzip -p public/apk/picme-user.apk META-INF/*.DSA 2>/dev/null | openssl pkcs7 -inform DER -print_certs -noout -text | grep -E 'Issuer:|Subject:|Not Before|Not After'" """
    stdin, stdout, stderr = client.exec_command(cmd_user_dsa)
    out_user = stdout.read().decode('utf-8', errors='replace').strip()
print(out_user)
print("Errors:", stderr.read().decode('utf-8', errors='replace'))

# Check the Driver APK signature
print("\\n=== DRIVER APK SIGNATURE ===")
cmd_driver = """kubectl exec deploy/laravel-deployment -- bash -c "unzip -p public/apk/picme-driver.apk META-INF/*.RSA 2>/dev/null | openssl pkcs7 -inform DER -print_certs -noout -text | grep -E 'Issuer:|Subject:|Not Before|Not After'" """
stdin, stdout, stderr = client.exec_command(cmd_driver)
out_driver = stdout.read().decode('utf-8', errors='replace').strip()
if not out_driver:
    cmd_driver_dsa = """kubectl exec deploy/laravel-deployment -- bash -c "unzip -p public/apk/picme-driver.apk META-INF/*.DSA 2>/dev/null | openssl pkcs7 -inform DER -print_certs -noout -text | grep -E 'Issuer:|Subject:|Not Before|Not After'" """
    stdin, stdout, stderr = client.exec_command(cmd_driver_dsa)
    out_driver = stdout.read().decode('utf-8', errors='replace').strip()
print(out_driver)
print("Errors:", stderr.read().decode('utf-8', errors='replace'))

client.close()
