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
    # 1. Obtenir le JSON du ReplicaSet 56f54497f
    cmd_get_rs = "k3s kubectl get rs laravel-deployment-56f54497f -o json"
    out, err = run_ssh_command('109.199.123.69', 'root', 'Charlotte23', cmd_get_rs)
    if not out:
        print("Failed to get RS:", err)
        exit(1)
        
    rs_data = json.loads(out)
    pod_template = rs_data['spec']['template']
    
    # Remplacer les labels
    pod_template['metadata']['labels'] = {'app': 'laravel-test-46h'}
    
    # Créer un Deployment manifest
    deploy_manifest = {
        "apiVersion": "apps/v1",
        "kind": "Deployment",
        "metadata": {
            "name": "laravel-test-46h",
            "namespace": "default"
        },
        "spec": {
            "replicas": 1,
            "selector": {
                "matchLabels": {
                    "app": "laravel-test-46h"
                }
            },
            "template": pod_template
        }
    }
    
    deploy_json_str = json.dumps(deploy_manifest)
    
    # Appliquer le déploiement
    cmd_apply = f"cat << 'EOF' > /tmp/test-46h.json\n{deploy_json_str}\nEOF\nk3s kubectl apply -f /tmp/test-46h.json"
    out_apply, err_apply = run_ssh_command('109.199.123.69', 'root', 'Charlotte23', cmd_apply)
    print("APPLY:", out_apply, err_apply)
    
    # Attendre et vérifier le contenu
    cmd_wait = "k3s kubectl wait --for=condition=Available deployment/laravel-test-46h --timeout=60s && k3s kubectl exec deploy/laravel-test-46h -- ls -la /app/resources/views/user/marketplace/ 2>/dev/null || echo 'Not found'"
    out_wait, err_wait = run_ssh_command('109.199.123.69', 'root', 'Charlotte23', cmd_wait)
    print("WAIT AND EXEC:", out_wait, err_wait)
    
    # Check what image is running
    cmd_img = "k3s kubectl get deployment laravel-test-46h -o jsonpath='{.spec.template.spec.containers[0].image}'"
    out_img, _ = run_ssh_command('109.199.123.69', 'root', 'Charlotte23', cmd_img)
    print("\nIMAGE DANS LE DEPLOIEMENT:", out_img)

    # Check the digest of the running pod
    cmd_pod_img = "k3s kubectl get pods -l app=laravel-test-46h -o jsonpath='{.items[0].status.containerStatuses[0].imageID}'"
    out_pod_img, _ = run_ssh_command('109.199.123.69', 'root', 'Charlotte23', cmd_pod_img)
    print("IMAGE ID REEL DU POD:", out_pod_img)
