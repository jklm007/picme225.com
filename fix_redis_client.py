import paramiko

def main():
    hostname = '109.199.123.69'
    username = 'root'
    password = 'Charlotte23'
    
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # Patch the env var REDIS_CLIENT=phpredis for both laravel-deployment and laravel-worker
    for deployment in ['laravel-deployment', 'laravel-worker']:
        cmd = f"kubectl set env deployment/{deployment} REDIS_CLIENT=phpredis"
        stdin, stdout, stderr = client.exec_command(cmd)
        out = stdout.read().decode('ascii', 'ignore')
        err = stderr.read().decode('ascii', 'ignore')
        print(f"{deployment}: {out.strip()} {err.strip()}")
    
    client.close()

if __name__ == '__main__':
    main()
