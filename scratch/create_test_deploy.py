import paramiko
import json

def run_ssh_command(host, user, password, command):
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        client.connect(host, username=user, password=password, timeout=10)
        stdin, stdout, stderr = client.exec_command(command)
        out = stdout.read().decode()
        err = stderr.read().decode()
        return out, err
    except Exception as e:
        return str(e), ""
    finally:
        client.close()

if __name__ == "__main__":
    # Get the pod template from the ReplicaSet
    cmd_get_rs = "k3s kubectl get rs laravel-deployment-ff9cc75cd -o json"
    out, err = run_ssh_command('109.199.123.69', 'root', 'Charlotte23', cmd_get_rs)
    if not out:
        print("Failed to get RS:", err)
        exit(1)
        
    rs_data = json.loads(out)
    pod_template = rs_data['spec']['template']
    
    # Remove old labels
    pod_template['metadata']['labels'] = {'app': 'laravel-test'}
    
    # Create a new deployment manifest
    deploy_manifest = {
        "apiVersion": "apps/v1",
        "kind": "Deployment",
        "metadata": {
            "name": "laravel-test-deployment",
            "namespace": "default"
        },
        "spec": {
            "replicas": 1,
            "selector": {
                "matchLabels": {
                    "app": "laravel-test"
                }
            },
            "template": pod_template
        }
    }
    
    deploy_json_str = json.dumps(deploy_manifest)
    
    # Apply the deployment
    cmd_apply = f"cat << 'EOF' > /tmp/test-deploy.json\n{deploy_json_str}\nEOF\nk3s kubectl apply -f /tmp/test-deploy.json"
    out_apply, err_apply = run_ssh_command('109.199.123.69', 'root', 'Charlotte23', cmd_apply)
    print("APPLY:", out_apply, err_apply)
    
    # Wait and check
    cmd_wait = "k3s kubectl wait --for=condition=Available deployment/laravel-test-deployment --timeout=60s && k3s kubectl exec deploy/laravel-test-deployment -- ls -la /app/resources/views/user/marketplace/ 2>/dev/null || echo 'Not found'"
    out_wait, err_wait = run_ssh_command('109.199.123.69', 'root', 'Charlotte23', cmd_wait)
    print("WAIT AND EXEC:", out_wait, err_wait)
