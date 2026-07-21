#!/usr/bin/env python3
"""
Server Analysis Script Part 2 - /var/www/picme focus - READ-ONLY
Appends output to the existing report file.
"""

import paramiko
import datetime
import os

HOST = "109.199.123.69"
PORT = 22
USERNAME = "root"
PASSWORD = "Charlotte23"

REPORT_FILE = os.path.join(os.path.dirname(os.path.abspath(__file__)), "server_report.txt")

COMMANDS = [
    # 1. Recently modified files in /var/www/picme
    ("Section 13: Recently modified files in /var/www/picme (since Jul 15)",
     "find /var/www/picme -type f -newermt '2026-07-15 00:00' -ls 2>/dev/null"),

    # 2. Files modified in last 3 days
    ("Section 13: Files modified in last 3 days in /var/www/picme",
     "find /var/www/picme -type f -mtime -3 -ls 2>/dev/null | head -200"),

    # 3. Project structure
    ("Section 14: /var/www/picme/ listing",
     "ls -la /var/www/picme/"),
    ("Section 14: /var/www/picme/resources/views/",
     "ls -la /var/www/picme/resources/views/ 2>/dev/null"),
    ("Section 14: /var/www/picme/resources/views/user/",
     "ls -la /var/www/picme/resources/views/user/ 2>/dev/null"),
    ("Section 14: /var/www/picme/public/",
     "ls -la /var/www/picme/public/ 2>/dev/null"),
    ("Section 14: /var/www/picme/public/images/",
     "ls -la /var/www/picme/public/images/ 2>/dev/null"),

    # 4. .env R2/Cloudflare settings
    ("Section 15: .env R2/Cloudflare/S3/Filesystem settings",
     "grep -i 'R2\\|cloudflare\\|AWS_\\|S3_\\|FILESYSTEM' /var/www/picme/.env 2>/dev/null"),

    # 5. Git status
    ("Section 16: Git log (last 20 commits)",
     "cd /var/www/picme && git log --oneline -20 2>/dev/null"),
    ("Section 16: Git status",
     "cd /var/www/picme && git status 2>/dev/null"),
    ("Section 16: Git reflog (last 20)",
     "cd /var/www/picme && git reflog -20 2>/dev/null"),
    ("Section 16: Git stash list",
     "cd /var/www/picme && git stash list 2>/dev/null"),

    # 6. Backups in /root/backups
    ("Section 17: /root/backups/ listing",
     "ls -la /root/backups/ 2>/dev/null"),
    ("Section 17: /root/backups/ recursive listing",
     "ls -laR /root/backups/ 2>/dev/null"),

    # 7. Deploy script contents
    ("Section 18: deploy_production.sh contents",
     "cat /root/deploy_production.sh 2>/dev/null"),

    # 8. Homepage/marketplace/ad related blade files
    ("Section 19: Homepage/marketplace/ad blade files",
     "find /var/www/picme -type f -name '*.blade.php' | grep -i 'home\\|index\\|marketplace\\|ad-campaign\\|banner\\|pub' 2>/dev/null"),
    ("Section 19: Recently modified blade files (since Jul 14)",
     "find /var/www/picme -type f -name '*.blade.php' -newermt '2026-07-14 00:00' -ls 2>/dev/null"),

    # 9. /tmp/picme225-build staging area
    ("Section 20: /tmp/picme225-build/ listing",
     "ls -la /tmp/picme225-build/ 2>/dev/null"),
    ("Section 20: /tmp/picme225-build/resources/views/",
     "ls -la /tmp/picme225-build/resources/views/ 2>/dev/null"),

    # 10. Containerd snapshots for old app versions
    ("Section 21: Containerd snapshots listing",
     "ls -la /var/lib/containerd/io.containerd.snapshotter.v1.overlayfs/snapshots/ 2>/dev/null | head -30"),
    ("Section 21: home.blade.php in snapshots",
     "find /var/lib/containerd/io.containerd.snapshotter.v1.overlayfs/snapshots/ -maxdepth 3 -name 'home.blade.php' 2>/dev/null"),
    ("Section 21: All blade files in snapshot views",
     "find /var/lib/containerd/io.containerd.snapshotter.v1.overlayfs/snapshots/ -maxdepth 4 -path '*/views/*' -name '*.blade.php' 2>/dev/null | head -50"),

    # 11. Inside the running Laravel pod
    ("Section 22: Laravel pod /app/resources/views/ listing",
     "kubectl exec $(kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}') -- ls -la /app/resources/views/ 2>/dev/null || k3s kubectl exec $(k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}') -- ls -la /app/resources/views/ 2>/dev/null"),
    ("Section 22: Laravel pod /app/public/images/ listing",
     "kubectl exec $(kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}') -- ls -la /app/public/images/ 2>/dev/null || k3s kubectl exec $(k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}') -- ls -la /app/public/images/ 2>/dev/null"),
    ("Section 22: Laravel pod .env R2/Cloudflare/S3 settings",
     "kubectl exec $(kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}') -- cat /app/.env 2>/dev/null | grep -i 'R2\\|cloudflare\\|AWS_\\|S3_\\|FILESYSTEM' || k3s kubectl exec $(k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}') -- cat /app/.env 2>/dev/null | grep -i 'R2\\|cloudflare\\|AWS_\\|S3_\\|FILESYSTEM'"),
]


def run_command(ssh_client, command, timeout=120):
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
    report_lines.append("")
    report_lines.append("")
    report_lines.append("=" * 80)
    report_lines.append(f"ADDITIONAL SERVER ANALYSIS - /var/www/picme FOCUS")
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

    # Append to existing report
    report_content = "\n".join(report_lines)
    with open(REPORT_FILE, "a", encoding="utf-8") as f:
        f.write(report_content)

    print(f"[+] Appended to report: {REPORT_FILE}")
    print(f"[+] Appended size: {len(report_content)} characters")

    # Also print to stdout
    print("\n" + report_content)


if __name__ == "__main__":
    main()
