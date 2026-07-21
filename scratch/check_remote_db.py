import paramiko

SERVER_IP = "109.199.123.69"
SERVER_USER = "root"
SERVER_PASS = "Charlotte23"
POD_PREFIX = "laravel-deployment-"
NAMESPACE = "default"

def check():
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(SERVER_IP, username=SERVER_USER, password=SERVER_PASS)
    
    stdin, stdout, stderr = ssh.exec_command(f"kubectl get pods -n {NAMESPACE} | grep {POD_PREFIX} | grep Running | awk '{{print $1}}'")
    pods = stdout.read().decode('utf-8').strip().split('\n')
    
    if not pods or not pods[0]:
        print("No running Laravel pod found!")
        return
        
    pod = pods[0]
    
    cmd = f"kubectl exec {pod} -n {NAMESPACE} -- php artisan tinker --execute=\"echo json_encode(App\\Models\\MarketplaceListing::whereIn('id', [272, 268, 264, 263])->get(['id', 'cover_image', 'images'])->toArray());\""
    stdin, stdout, stderr = ssh.exec_command(cmd)
    
    print("OUTPUT:")
    print(stdout.read().decode('utf-8'))
    print("ERRORS:")
    print(stderr.read().decode('utf-8'))
    
    ssh.close()

if __name__ == "__main__":
    check()
