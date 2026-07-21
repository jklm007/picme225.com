# run_deploy_driver.ps1
$zone   = "europe-west9-a"
$server = "k3s-master-gcp"

Write-Host "=== Upload du nouvel APK Driver vers $server ===" -ForegroundColor Cyan

# 1. Copie du script
gcloud compute scp deploy_driver_apk.sh ${server}:/tmp/deploy_driver_apk.sh --zone=$zone --quiet

# 2. Copie de l'APK
gcloud compute ssh $server --zone=$zone --command "mkdir -p /tmp/p4"
gcloud compute scp "..\picmeDriver_androix\app\build\outputs\apk\release\app-arm64-v8a-release.apk" ${server}:/tmp/p4/picme-driver.apk --zone=$zone --quiet

Write-Host "Upload termine. Lancement du script de deploiement sur le serveur..." -ForegroundColor Yellow

# 3. Execution
gcloud compute ssh $server --zone=$zone --command "chmod +x /tmp/deploy_driver_apk.sh && bash /tmp/deploy_driver_apk.sh"

Write-Host "=== DEPLOIEMENT NEW DRIVER APK TERMINE ===" -ForegroundColor Green
