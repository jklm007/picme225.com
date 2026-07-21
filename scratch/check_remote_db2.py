import paramiko
import base64

SERVER_IP = "109.199.123.69"
SERVER_USER = "root"
SERVER_PASS = "Charlotte23"
POD_PREFIX = "laravel-deployment-"
NAMESPACE = "default"

php_script = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

$listings = App\\Models\\MarketplaceListing::whereIn('id', [272, 268, 264, 263])->orderBy('id', 'desc')->get(['id', 'cover_image', 'images'])->toArray();
echo json_encode($listings, JSON_PRETTY_PRINT);
"""

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
    
    b64_php = base64.b64encode(php_script.encode()).decode()
    cmd = f"kubectl exec {pod} -n {NAMESPACE} -- sh -c 'echo {b64_php} | base64 -d > test_db.php && php test_db.php'"
    
    stdin, stdout, stderr = ssh.exec_command(cmd)
    
    print("OUTPUT:")
    print(stdout.read().decode('utf-8'))
    print("ERRORS:")
    print(stderr.read().decode('utf-8'))
    
    ssh.close()

if __name__ == "__main__":
    check()
