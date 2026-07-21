Write-Host "Uploading fixes to /tmp..."
gcloud compute scp sync_frontend_fixes.sh k3s-master-gcp:/tmp/sync_frontend_fixes.sh --zone=europe-west9-a --quiet
gcloud compute scp resources\views\user\layout\app.blade.php k3s-master-gcp:/tmp/app.blade.php --zone=europe-west9-a --quiet
gcloud compute scp resources\views\home.blade.php k3s-master-gcp:/tmp/home.blade.php --zone=europe-west9-a --quiet
gcloud compute scp routes\web.php k3s-master-gcp:/tmp/web.php --zone=europe-west9-a --quiet
gcloud compute scp resources\lang\en\home.php k3s-master-gcp:/tmp/en_home.php --zone=europe-west9-a --quiet
gcloud compute scp resources\lang\fr\home.php k3s-master-gcp:/tmp/fr_home.php --zone=europe-west9-a --quiet
gcloud compute scp app\Http\Middleware\LandingLanguageMiddleware.php k3s-master-gcp:/tmp/LandingLanguageMiddleware.php --zone=europe-west9-a --quiet
gcloud compute scp app\Http\Controllers\Auth\RegisterController.php k3s-master-gcp:/tmp/RegisterController.php --zone=europe-west9-a --quiet
gcloud compute scp app\Http\Controllers\HomeController.php k3s-master-gcp:/tmp/HomeController.php --zone=europe-west9-a --quiet
gcloud compute ssh k3s-master-gcp --zone=europe-west9-a --command "bash /tmp/sync_frontend_fixes.sh"
