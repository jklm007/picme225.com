import paramiko

host = '109.199.123.69'
user = 'root'
password = 'Charlotte23'

commands = """
LARAVEL_PODS=$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
MAIN_POD=$(echo "$LARAVEL_PODS" | head -n 1)

kubectl logs $MAIN_POD --tail=500 | grep -B 30 "ShareErrorsFromSession" | tail -n 40
"""

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, username=user, password=password)

    stdin, stdout, stderr = ssh.exec_command(commands)
    
    print(stdout.read().decode())
    
    ssh.close()

except Exception as e:
    print(f"Erreur : {str(e)}")
