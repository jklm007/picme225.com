$ZONE = "europe-west9-a"
$NODE = "k3s-master-gcp"

Write-Host "Uploading distribute_app.sh..."
gcloud compute scp distribute_app.sh ${NODE}:/tmp/frontend_v2/distribute_app.sh --zone=$ZONE --quiet

Write-Host "Executing distribute_app.sh..."
gcloud compute ssh $NODE --zone=$ZONE --command "bash /tmp/frontend_v2/distribute_app.sh"
