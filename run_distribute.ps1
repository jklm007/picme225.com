$ZONE = "europe-west9-a"
$NODE = "k3s-master-gcp"

Write-Host "Uploading distribute_apks.sh..."
gcloud compute scp distribute_apks.sh ${NODE}:/tmp/frontend_v2/distribute_apks.sh --zone=$ZONE --quiet

Write-Host "Executing distribute_apks.sh..."
gcloud compute ssh $NODE --zone=$ZONE --command "bash /tmp/frontend_v2/distribute_apks.sh"
