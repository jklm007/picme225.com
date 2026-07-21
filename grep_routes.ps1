gcloud compute ssh k3s-master-gcp --zone=europe-west9-a --command "sudo k3s kubectl exec laravel-deployment-7b87f5f49c-fhkgv -- sh -c ""grep -rl 'view(''index'')' /app/routes"""
