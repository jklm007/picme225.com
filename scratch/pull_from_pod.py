import paramiko
import os

SERVER_IP = "109.199.123.69"
SERVER_USER = "root"
SERVER_PASS = "Charlotte23"
POD_APP_DIR = "/app"
LOCAL_BASE = r"c:\Users\HP\Documents\Jews-world Backend\picme225.com_backend"

# Liste des fichiers à rapatrier depuis le pod
FILES_TO_PULL = [
    "resources/views/user/dashboard.blade.php",
    "resources/views/user/home.blade.php",
    "resources/views/user/account/profile.blade.php",
    "resources/views/user/ride/trips.blade.php",
    "resources/views/user/ride/upcoming.blade.php",
    "resources/views/user/ride/confirm_ride.blade.php",
    "resources/views/user/marketplace/detail.blade.php",
    "resources/views/user/include/header.blade.php",
    "resources/views/user/include/nav.blade.php",
    "resources/views/user/include/footer.blade.php",
    "resources/views/user/layout/app.blade.php",
    "resources/views/user/layout/base.blade.php",
    "resources/views/user/layout/pwa.blade.php",
    "resources/views/user/auth/login.blade.php",
    "resources/views/user/wallet/index.blade.php",
    "resources/views/provider/index.blade.php",
    "resources/views/provider/layout/app.blade.php",
    "resources/views/provider/layout/partials/header.blade.php",
    "resources/views/provider/layout/partials/nav.blade.php",
    "resources/views/provider/location/index.blade.php",
    "resources/views/provider/payment/earnings.blade.php",
    "resources/views/provider/payment/upcoming.blade.php",
    "resources/views/provider/profile/index.blade.php",
    "resources/views/provider/trip/index.blade.php",
    "resources/views/provider/wallet/index.blade.php",
    "resources/views/provider/document/index.blade.php",
    "resources/views/provider/governance/index.blade.php",
    "resources/views/home.blade.php",
    "resources/views/drive.blade.php",
    "resources/views/offline.blade.php",
    "resources/views/marketplace/detail.blade.php",
    "routes/web.php",
    "routes/admin.php",
    "routes/provider.php",
    "app/Http/Controllers/UserDashboardController.php",
    "app/Http/Controllers/RideController.php",
    "app/Http/Controllers/ProviderController.php",
    "app/Models/Service.php",
    "app/Models/ServiceType.php",
]

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(SERVER_IP, username=SERVER_USER, password=SERVER_PASS)
sftp = ssh.open_sftp()

ok = []
errors = []

for rel_path in FILES_TO_PULL:
    pod_path = f"{POD_APP_DIR}/{rel_path}"
    local_path = os.path.join(LOCAL_BASE, rel_path.replace("/", os.sep))
    tmp_path = f"/tmp/pull_{rel_path.replace('/', '_')}"

    try:
        # Copier depuis le pod vers /tmp sur le serveur
        cmd = f"kubectl exec deploy/laravel-deployment -- cat {pod_path} > {tmp_path}"
        stdin, stdout, stderr = ssh.exec_command(cmd)
        stdout.channel.recv_exit_status()

        # Télécharger depuis /tmp vers local
        os.makedirs(os.path.dirname(local_path), exist_ok=True)
        sftp.get(tmp_path, local_path)
        print(f"  OK: {rel_path}")
        ok.append(rel_path)
    except Exception as e:
        print(f"  ERR: {rel_path} -> {e}")
        errors.append(rel_path)

sftp.close()
ssh.close()
print(f"\nDone! {len(ok)} OK, {len(errors)} errors")
if errors:
    for e in errors:
        print(f"  MISSED: {e}")
