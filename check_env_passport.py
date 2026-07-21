import paramiko

hostname = '109.199.123.69'
username = 'root'
password = 'Charlotte23'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname, username=username, password=password)

# Check if .env file exists and print its keys related to PASSPORT
cmd = """kubectl exec deploy/laravel-deployment -- php -r "
\$env = file_get_contents('.env');
\$lines = explode(\\\"\\\\n\\\", \$env);
foreach(\$lines as \$line) {
    if (strpos(\$line, 'PASSPORT') !== false) {
        echo \$line . \\\"\\\\n\\\";
    }
}
"
"""
stdin, stdout, stderr = client.exec_command(cmd)
print("ENV PASSPORT:")
print(stdout.read().decode('utf-8', errors='replace'))
print("STDERR:", stderr.read().decode('utf-8', errors='replace'))

client.close()
