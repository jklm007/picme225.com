# sync_seeders.ps1 - Syncs seeders to GCP pods and runs db:seed
# Fixed: proper shell escaping, no sudo issues, correct pod variable handling

$ZONE = "europe-west9-a"
$MASTER = "k3s-master-gcp"
$POD_LABEL = "app=laravel"

Write-Host "=== Step 1: Archiving seeders locally ===" -ForegroundColor Cyan
tar -cf seeders.tar -C database seeders
if ($LASTEXITCODE -ne 0) { Write-Error "Failed to create tar archive"; exit 1 }

Write-Host "=== Step 2: Uploading seeders.tar to GCP Master ===" -ForegroundColor Cyan
gcloud compute scp seeders.tar "${MASTER}:/tmp/seeders.tar" --zone=$ZONE --quiet
if ($LASTEXITCODE -ne 0) { Write-Error "Failed to upload seeders.tar"; exit 1 }

Write-Host "=== Step 3: Extracting seeders on Master ===" -ForegroundColor Cyan
gcloud compute ssh $MASTER --zone=$ZONE --command "rm -rf /tmp/seeders_extracted && mkdir -p /tmp/seeders_extracted && tar -xf /tmp/seeders.tar -C /tmp/seeders_extracted && echo 'Extraction OK'"
if ($LASTEXITCODE -ne 0) { Write-Error "Failed to extract seeders on master"; exit 1 }

Write-Host "=== Step 4: Getting pod names ===" -ForegroundColor Cyan
$PODS = gcloud compute ssh $MASTER --zone=$ZONE --command "sudo k3s kubectl get pods -l $POD_LABEL --no-headers -o custom-columns=':metadata.name' 2>/dev/null" 2>&1
$PODS = $PODS | Where-Object { $_ -match "laravel-" }
Write-Host "Pods found: $PODS"

if (-not $PODS) { Write-Error "No Laravel pods found!"; exit 1 }

Write-Host "=== Step 5: Copying seeders to each pod ===" -ForegroundColor Cyan
foreach ($POD in $PODS) {
    $POD = $POD.Trim()
    if (-not $POD) { continue }
    Write-Host "  -> Copying to pod: $POD"
    gcloud compute ssh $MASTER --zone=$ZONE --command "sudo k3s kubectl cp /tmp/seeders_extracted/seeders $POD:/app/database/seeders 2>&1 && echo 'Copy to $POD OK'"
    if ($LASTEXITCODE -ne 0) { Write-Warning "Copy to $POD may have had issues, continuing..." }
}

Write-Host "=== Step 6: Running migrate and db:seed in first pod ===" -ForegroundColor Cyan
$FIRST_POD = ($PODS | Select-Object -First 1).Trim()
Write-Host "  -> Using pod: $FIRST_POD"

Write-Host "  -> Running migrations first..." -ForegroundColor Yellow
gcloud compute ssh $MASTER --zone=$ZONE --command "sudo k3s kubectl exec $FIRST_POD -- php /app/artisan migrate --force 2>&1"

Write-Host "  -> Running db:seed..." -ForegroundColor Yellow
gcloud compute ssh $MASTER --zone=$ZONE --command "sudo k3s kubectl exec $FIRST_POD -- php /app/artisan db:seed --force 2>&1"
if ($LASTEXITCODE -ne 0) { Write-Error "Seeding failed!"; exit 1 }

Write-Host "=== Seeding complete! ===" -ForegroundColor Green

Write-Host "=== Step 7: Cleanup ===" -ForegroundColor Cyan
Remove-Item seeders.tar -ErrorAction SilentlyContinue
