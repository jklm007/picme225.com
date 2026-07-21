#!/usr/bin/env python3
"""
fix_seeders_remote.py
Fixes all seeder files in-place inside the pod via kubectl exec + sed
"""
import subprocess
import sys

POD = "laravel-deployment-7b87f5f49c-fhkgv"
MASTER = "k3s-master-gcp"
ZONE = "europe-west9-a"

def ssh(cmd):
    result = subprocess.run(
        ["gcloud", "compute", "ssh", MASTER, f"--zone={ZONE}", f"--command={cmd}"],
        capture_output=True, text=True
    )
    print("STDOUT:", result.stdout)
    print("STDERR:", result.stderr)
    return result.returncode

def kubectl_exec(cmd):
    return ssh(f"sudo k3s kubectl exec {POD} -- {cmd}")

print("=== Fixing FOREIGN_KEY_CHECKS in all seeders inside pod ===")

# Use sed to replace SET FOREIGN_KEY_CHECKS=0 with proper pgsql-safe version
# We'll use a Python heredoc approach inside the pod

fix_script = r"""python3 -c "
import os, re

SEED_DIR = '/app/database/seeders'
files = [f for f in os.listdir(SEED_DIR) if f.endswith('.php')]
fixed = 0
for fname in files:
    path = os.path.join(SEED_DIR, fname)
    with open(path, 'r') as f:
        content = f.read()
    
    # Replace SET FOREIGN_KEY_CHECKS=0 (MySQL only)
    if 'SET FOREIGN_KEY_CHECKS' in content:
        new_content = re.sub(
            r\"DB::statement\('SET FOREIGN_KEY_CHECKS=0;'\);\",
            \"if (DB::getDriverName() === 'pgsql') { DB::statement(\\\"SET session_replication_role = 'replica';\\\"); } else { DB::statement('SET FOREIGN_KEY_CHECKS=0;'); }\",
            content
        )
        new_content = re.sub(
            r\"DB::statement\('SET FOREIGN_KEY_CHECKS=1;'\);\",
            \"if (DB::getDriverName() === 'pgsql') { DB::statement(\\\"SET session_replication_role = 'origin';\\\"); } else { DB::statement('SET FOREIGN_KEY_CHECKS=1;'); }\",
            new_content
        )
        # Also handle \\DB:: variant
        new_content = re.sub(
            r\"\\\\\\\\DB::statement\('SET FOREIGN_KEY_CHECKS=0;'\);\",
            \"if (DB::getDriverName() === 'pgsql') { DB::statement(\\\"SET session_replication_role = 'replica';\\\"); } else { DB::statement('SET FOREIGN_KEY_CHECKS=0;'); }\",
            new_content
        )
        new_content = re.sub(
            r\"\\\\\\\\DB::statement\('SET FOREIGN_KEY_CHECKS=1;'\);\",
            \"if (DB::getDriverName() === 'pgsql') { DB::statement(\\\"SET session_replication_role = 'origin';\\\"); } else { DB::statement('SET FOREIGN_KEY_CHECKS=1;'); }\",
            new_content
        )
        if new_content != content:
            with open(path, 'w') as f:
                f.write(new_content)
            fixed += 1
            print('Fixed:', fname)

print(f'Total fixed: {fixed}/{len(files)} files')
"
"""

returncode = kubectl_exec(fix_script.strip())
if returncode == 0:
    print("\n=== All seeders patched! ===")
else:
    print("\n=== Some error occurred ===")
    sys.exit(1)
