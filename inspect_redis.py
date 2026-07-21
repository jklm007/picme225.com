import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # Test Redis connection from within the container
    commands = """
    kubectl exec deployment/laravel-deployment -- php -r "
    \$redis = new Redis();
    try {
        \$redis->connect('redis-service', 6379);
        \$redis->set('test_key', 'hello');
        echo 'Redis OK: ' . \$redis->get('test_key') . PHP_EOL;
    } catch (Exception \$e) {
        echo 'Redis ERROR: ' . \$e->getMessage() . PHP_EOL;
    }
    "
    """
    
    stdin, stdout, stderr = client.exec_command(commands)
    print("Stdout:", stdout.read().decode('ascii', 'ignore'))
    print("Stderr:", stderr.read().decode('ascii', 'ignore'))
    client.close()

if __name__ == '__main__':
    main()
