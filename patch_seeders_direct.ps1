# patch_seeders_direct.ps1
# Writes fixed seeders directly into pod via kubectl exec + base64 encoding
# This avoids kubectl cp directory issues

$ZONE   = "europe-west9-a"
$MASTER = "k3s-master-gcp"

Write-Host "=== Getting pod name ===" -ForegroundColor Cyan
$POD = (gcloud compute ssh $MASTER --zone=$ZONE --command `
  "sudo k3s kubectl get pods -l app=laravel --no-headers -o custom-columns=':metadata.name' 2>/dev/null | head -1" 2>&1).Trim()

if (-not $POD -or $POD -notmatch "laravel-") {
    Write-Error "No Laravel pod found: '$POD'"
    exit 1
}
Write-Host "  Using pod: $POD" -ForegroundColor Green

# Helper: encode a local file to base64 and write it into the pod
function Send-FileToPod {
    param([string]$LocalPath, [string]$PodPath)
    Write-Host "  -> Sending $LocalPath to pod:$PodPath" -ForegroundColor Yellow
    $b64 = [Convert]::ToBase64String([IO.File]::ReadAllBytes($LocalPath))
    gcloud compute ssh $MASTER --zone=$ZONE --command `
      "echo '$b64' | sudo k3s kubectl exec -i $POD -- bash -c 'base64 -d > $PodPath && echo OK: $PodPath'"
}

# --- Send fixed seeders ---
$BASE = "c:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\database"

Send-FileToPod "$BASE\seeders\PdpStopsSeeder.php"  "/app/database/seeders/PdpStopsSeeder.php"
Send-FileToPod "$BASE\seeders\PdpRoutesSeeder.php"  "/app/database/seeders/PdpRoutesSeeder.php"
Send-FileToPod "$BASE\seeders\DemoSeeder.php"        "/app/database/seeders/DemoSeeder.php"
Send-FileToPod "$BASE\seeders\SocialMarketplaceSeeder.php" "/app/database/seeders/SocialMarketplaceSeeder.php"
Send-FileToPod "$BASE\seeders\MarketplacePlansSeeder.php"  "/app/database/seeders/MarketplacePlansSeeder.php"

# --- Send the fix migration ---
Send-FileToPod "$BASE\migrations\2026_06_26_163000_fix_postgres_check_constraints.php" `
               "/app/database/migrations/2026_06_26_163000_fix_postgres_check_constraints.php"

Write-Host "`n=== Clearing Laravel caches ===" -ForegroundColor Cyan
gcloud compute ssh $MASTER --zone=$ZONE --command `
  "sudo k3s kubectl exec $POD -- php /app/artisan config:clear 2>&1; sudo k3s kubectl exec $POD -- php /app/artisan cache:clear 2>&1"

Write-Host "`n=== Running migrate --force ===" -ForegroundColor Yellow
gcloud compute ssh $MASTER --zone=$ZONE --command `
  "sudo k3s kubectl exec $POD -- php /app/artisan migrate --force 2>&1"

Write-Host "`n=== Running db:seed --force ===" -ForegroundColor Yellow
gcloud compute ssh $MASTER --zone=$ZONE --command `
  "sudo k3s kubectl exec $POD -- php /app/artisan db:seed --force 2>&1"

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n=== SEEDING COMPLETE! ===" -ForegroundColor Green
} else {
    Write-Error "db:seed failed with exit code $LASTEXITCODE"
}
