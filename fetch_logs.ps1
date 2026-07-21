$server = "k3s-master-gcp"
$zone = "europe-west9-a"

Write-Host "Fetching logs..."
$command = "bash -c `"POD=\`$(sudo kubectl get pods -l app=laravel -o jsonpath='{.items[?(@.status.phase==`"Running`")].metadata.name}' | head -n 1); sudo kubectl exec `$POD -- tail -n 200 /app/storage/logs/laravel.log`""
gcloud compute ssh $server --zone=$zone --command=$command
