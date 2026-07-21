$server = "k3s-master-gcp"
$zone = "europe-west9-a"

gcloud compute scp test_evolution_groups.php ${server}:/tmp/test_evolution_groups.php --zone=$zone --quiet

$command = "sudo kubectl cp /tmp/test_evolution_groups.php default/$(sudo kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=':metadata.name' --no-headers | head -n 1):/app/test_evolution_groups.php && sudo kubectl exec $(sudo kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=':metadata.name' --no-headers | head -n 1) -- php /app/test_evolution_groups.php"

gcloud compute ssh $server --zone=$zone --command $command
