$ZONE = "europe-west9-a"
$NODE = "k3s-master-gcp"
$ARTIFACTS_DIR = "C:\Users\HP\.gemini\antigravity\brain\eef68649-a0e3-488d-a21b-ddd928155790"

Write-Host "Creating /tmp/frontend_v2 on remote server..."
gcloud compute ssh $NODE --zone=$ZONE --command "mkdir -p /tmp/frontend_v2"

Write-Host "Uploading views..."
gcloud compute scp resources\views\user\layout\app.blade.php ${NODE}:/tmp/frontend_v2/app.blade.php --zone=$ZONE --quiet
gcloud compute scp resources\views\home.blade.php ${NODE}:/tmp/frontend_v2/home.blade.php --zone=$ZONE --quiet
gcloud compute scp resources\views\drive.blade.php ${NODE}:/tmp/frontend_v2/drive.blade.php --zone=$ZONE --quiet
gcloud compute scp resources\views\ride.blade.php ${NODE}:/tmp/frontend_v2/ride.blade.php --zone=$ZONE --quiet

Write-Host "Uploading CSS..."
gcloud compute scp public\asset\css\style.css ${NODE}:/tmp/frontend_v2/style.css --zone=$ZONE --quiet

Write-Host "Uploading Images..."
gcloud compute scp "$ARTIFACTS_DIR\abidjan_plateau_sunset_1783112350549.png" ${NODE}:/tmp/frontend_v2/abidjan_plateau_sunset.png --zone=$ZONE --quiet
gcloud compute scp "$ARTIFACTS_DIR\abidjan_plateau_night_1783112365409.png" ${NODE}:/tmp/frontend_v2/abidjan_plateau_night.png --zone=$ZONE --quiet
gcloud compute scp "$ARTIFACTS_DIR\vip_chauffeur_abidjan_1783112379852.png" ${NODE}:/tmp/frontend_v2/vip_chauffeur_abidjan.png --zone=$ZONE --quiet

Write-Host "Uploading script..."
gcloud compute scp deploy_frontend_v2.sh ${NODE}:/tmp/frontend_v2/deploy_frontend_v2.sh --zone=$ZONE --quiet

Write-Host "Executing script..."
gcloud compute ssh $NODE --zone=$ZONE --command "bash /tmp/frontend_v2/deploy_frontend_v2.sh"
Write-Host "DONE"
