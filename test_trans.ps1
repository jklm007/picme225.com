gcloud compute scp test_trans.php k3s-master-gcp:/tmp/test_trans.php --zone=europe-west9-a --quiet
gcloud compute ssh k3s-master-gcp --zone=europe-west9-a --command "sudo k3s kubectl cp /tmp/test_trans.php laravel-deployment-5f44cbb875-dp5ff:/app/test_trans.php && sudo k3s kubectl exec laravel-deployment-5f44cbb875-dp5ff -- php /app/test_trans.php"
