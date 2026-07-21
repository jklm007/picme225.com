#!/usr/bin/env python3
"""
Server Analysis Script - READ-ONLY
Connects via SSH and runs diagnostic commands, saving output to a report file.
"""

import paramiko
import datetime
import os

# Connection details
HOST = "109.199.123.69"
PORT = 22
USERNAME = "root"
PASSWORD = "Charlotte23"

# Output report path
REPORT_FILE = os.path.join(os.path.dirname(os.path.abspath(__file__)), "server_report.txt")

# Commands organized by section
COMMANDS = [
    # Section 1: Find recently modified files in the project
    ("Section 1: Recently modified files in /var", "find /var -type f -newermt '2026-07-15 00:00' -ls 2>/dev/null | head -200"),
    ("Section 1: Recently modified files in /opt", "find /opt -type f -newermt '2026-07-15 00:00' -ls 2>/dev/null | head -200"),
    ("Section 1: Recently modified files in /home", "find /home -type f -newermt '2026-07-15 00:00' -ls 2>/dev/null | head -200"),
    ("Section 1: Recently modified files in /root", "find /root -type f -newermt '2026-07-15 00:00' -ls 2>/dev/null | head -200"),
    ("Section 1: Recently modified files in /srv", "find /srv -type f -newermt '2026-07-15 00:00' -ls 2>/dev/null | head -200"),

    # Section 2: Find Laravel project location
    ("Section 2: Find artisan in laravel path", "find / -name 'artisan' -path '*/laravel/*' 2>/dev/null"),
    ("Section 2: Find .env in laravel path", "find / -name '.env' -path '*/laravel/*' 2>/dev/null"),
    ("Section 2: Find artisan anywhere (maxdepth 5)", "find / -maxdepth 5 -name 'artisan' 2>/dev/null"),

    # Section 3: Kubernetes analysis
    ("Section 3: Pods (all namespaces)", "kubectl get pods -A -o wide 2>/dev/null || k3s kubectl get pods -A -o wide 2>/dev/null"),
    ("Section 3: ReplicaSets (all namespaces)", "kubectl get rs -A -o wide 2>/dev/null || k3s kubectl get rs -A -o wide 2>/dev/null"),
    ("Section 3: Deployments (all namespaces)", "kubectl get deployments -A -o wide 2>/dev/null || k3s kubectl get deployments -A -o wide 2>/dev/null"),
    ("Section 3: PVCs (all namespaces)", "kubectl get pvc -A 2>/dev/null || k3s kubectl get pvc -A 2>/dev/null"),
    ("Section 3: PVs (all namespaces)", "kubectl get pv -A 2>/dev/null || k3s kubectl get pv -A 2>/dev/null"),
    ("Section 3: ConfigMaps (all namespaces)", "kubectl get configmap -A 2>/dev/null || k3s kubectl get configmap -A 2>/dev/null"),
    ("Section 3: Secrets (all namespaces)", "kubectl get secrets -A 2>/dev/null || k3s kubectl get secrets -A 2>/dev/null"),
    ("Section 3: Ingress (all namespaces)", "kubectl get ingress -A 2>/dev/null || k3s kubectl get ingress -A 2>/dev/null"),

    # Section 4: Rollout history for all deployments
    ("Section 4: Rollout history for all deployments",
     "for dep in $(kubectl get deployments -A -o jsonpath='{range .items[*]}{.metadata.namespace}/{.metadata.name} {end}' 2>/dev/null || k3s kubectl get deployments -A -o jsonpath='{range .items[*]}{.metadata.namespace}/{.metadata.name} {end}' 2>/dev/null); do ns=$(echo $dep | cut -d/ -f1); name=$(echo $dep | cut -d/ -f2); echo \"=== Rollout History: $ns/$name ===\"; kubectl rollout history deployment/$name -n $ns 2>/dev/null || k3s kubectl rollout history deployment/$name -n $ns 2>/dev/null; done"),

    # Section 5: Container images
    ("Section 5: Docker images", "docker images 2>/dev/null"),
    ("Section 5: crictl images", "crictl images 2>/dev/null"),
    ("Section 5: containerd images", "ctr -n k8s.io images list 2>/dev/null | head -50"),

    # Section 6: Backup files search
    ("Section 6: Backup files", "find / -maxdepth 4 -type f \\( -name '*.bak' -o -name '*.backup' -o -name '*.old' -o -name '*~' \\) 2>/dev/null | head -50"),
    ("Section 6: Backup directories", "find / -maxdepth 4 -type d \\( -name 'backup' -o -name 'backups' -o -name 'archive' -o -name 'old' -o -name 'release' -o -name 'releases' \\) 2>/dev/null | head -30"),

    # Section 7: Recent deployment logs and history
    ("Section 7: Bash history", "cat ~/.bash_history 2>/dev/null | tail -100"),
    ("Section 7: Journal logs since 2026-07-15", "journalctl --since '2026-07-15' --no-pager 2>/dev/null | tail -200"),

    # Section 8: PVC details
    ("Section 8: PVC details (describe)",
     "for pvc in $(kubectl get pvc -A -o jsonpath='{range .items[*]}{.metadata.namespace}/{.metadata.name} {end}' 2>/dev/null || k3s kubectl get pvc -A -o jsonpath='{range .items[*]}{.metadata.namespace}/{.metadata.name} {end}' 2>/dev/null); do ns=$(echo $pvc | cut -d/ -f1); name=$(echo $pvc | cut -d/ -f2); echo \"=== PVC: $ns/$name ===\"; kubectl describe pvc $name -n $ns 2>/dev/null || k3s kubectl describe pvc $name -n $ns 2>/dev/null; done"),

    # Section 9: Check for git repos in common locations
    ("Section 9: Git repositories", "find / -maxdepth 5 -name '.git' -type d 2>/dev/null | head -20"),

    # Section 10: Check deployment scripts / CI/CD
    ("Section 10: Root scripts", "ls -la /root/*.sh /root/*.py /root/*.yaml /root/*.yml 2>/dev/null"),
    ("Section 10: Deploy scripts in /root", "find /root -maxdepth 2 -name 'deploy*' 2>/dev/null"),
    ("Section 10: YAML/YML files in /root", "find /root -maxdepth 2 -name '*.yaml' -o -name '*.yml' 2>/dev/null | head -20"),

    # Section 11: ReplicaSets with details (JSON)
    ("Section 11: ReplicaSets full JSON", "kubectl get rs -A -o json 2>/dev/null || k3s kubectl get rs -A -o json 2>/dev/null"),

    # Section 12: Describe specific deployments
    ("Section 12: Deployment details (describe all)",
     "for dep in $(kubectl get deployments -A -o jsonpath='{range .items[*]}{.metadata.namespace}/{.metadata.name} {end}' 2>/dev/null || k3s kubectl get deployments -A -o jsonpath='{range .items[*]}{.metadata.namespace}/{.metadata.name} {end}' 2>/dev/null); do ns=$(echo $dep | cut -d/ -f1); name=$(echo $dep | cut -d/ -f2); echo \"=== Deployment Details: $ns/$name ===\"; kubectl describe deployment $name -n $ns 2>/dev/null || k3s kubectl describe deployment $name -n $ns 2>/dev/null; done"),
]


def run_command(ssh_client, command, timeout=120):
    """Run a command over SSH and return stdout + stderr."""
    try:
        stdin, stdout, stderr = ssh_client.exec_command(command, timeout=timeout)
        out = stdout.read().decode("utf-8", errors="replace")
        err = stderr.read().decode("utf-8", errors="replace")
        return out, err
    except Exception as e:
        return "", f"ERROR executing command: {e}"


def main():
    print(f"[*] Connecting to {HOST}:{PORT} as {USERNAME}...")
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    
    try:
        ssh.connect(HOST, port=PORT, username=USERNAME, password=PASSWORD, timeout=30)
        print("[+] Connected successfully!")
    except Exception as e:
        print(f"[-] Connection failed: {e}")
        return
    
    report_lines = []
    report_lines.append("=" * 80)
    report_lines.append(f"SERVER ANALYSIS REPORT - {HOST}")
    report_lines.append(f"Generated: {datetime.datetime.now().isoformat()}")
    report_lines.append("=" * 80)
    report_lines.append("")
    
    total = len(COMMANDS)
    for i, (section, cmd) in enumerate(COMMANDS, 1):
        print(f"[{i}/{total}] Running: {section}...")
        
        report_lines.append("=" * 80)
        report_lines.append(f"### {section}")
        report_lines.append(f"Command: {cmd[:200]}{'...' if len(cmd) > 200 else ''}")
        report_lines.append("-" * 80)
        
        out, err = run_command(ssh, cmd, timeout=120)
        
        if out.strip():
            report_lines.append(out.rstrip())
        else:
            report_lines.append("(no output)")
        
        if err.strip():
            report_lines.append(f"\n[STDERR]: {err.rstrip()}")
        
        report_lines.append("")
    
    ssh.close()
    print("[+] SSH connection closed.")
    
    # Write report
    report_content = "\n".join(report_lines)
    with open(REPORT_FILE, "w", encoding="utf-8") as f:
        f.write(report_content)
    
    print(f"[+] Report saved to: {REPORT_FILE}")
    print(f"[+] Report size: {len(report_content)} characters")
    
    # Also print to stdout
    print("\n" + report_content)


if __name__ == "__main__":
    main()
