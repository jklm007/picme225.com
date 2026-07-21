# sync_and_seed.ps1
# Uploads fixed migration + seeders to GCP K3s, runs migrate + db:seed
# Usage: .\sync_and_seed.ps1

$ZONE       = "europe-west9-a"
$MASTER     = "k3s-master-gcp"
$POD_LABEL  = "app=laravel"

Write-Host "=====================================================" -ForegroundColor Cyan
Write-Host " SYNC & SEED - PicMe225 Backend" -ForegroundColor Cyan
Write-Host "=====================================================" -ForegroundColor Cyan

# ── Step 1: Archive seeders + the fix migration ──────────────────────────────
Write-Host "`n=== Step 1: Archiving seeders locally ===" -ForegroundColor Cyan
tar -cf seeders.tar -C database seeders
if ($LASTEXITCODE -ne 0) { Write-Error "Failed to create seeders.tar"; exit 1 }
Write-Host "  seeders.tar created OK" -ForegroundColor Green

Write-Host "`n=== Step 2: Archiving fix migration ===" -ForegroundColor Cyan
tar -cf fix_migration.tar -C database/migrations 2026_06_26_163000_fix_postgres_check_constraints.php
if ($LASTEXITCODE -ne 0) { Write-Error "Failed to create fix_migration.tar"; exit 1 }
Write-Host "  fix_migration.tar created OK" -ForegroundColor Green

# ── Step 2: Upload to GCP Master ─────────────────────────────────────────────
Write-Host "`n=== Step 3: Uploading archives to GCP Master ===" -ForegroundColor Cyan
gcloud compute scp seeders.tar      "${MASTER}:/tmp/seeders.tar"      --zone=$ZONE --quiet
gcloud compute scp fix_migration.tar "${MASTER}:/tmp/fix_migration.tar" --zone=$ZONE --quiet
if ($LASTEXITCODE -ne 0) { Write-Error "Upload failed"; exit 1 }
Write-Host "  Upload OK" -ForegroundColor Green

# ── Step 3: Extract on Master ────────────────────────────────────────────────
Write-Host "`n=== Step 4: Extracting on GCP Master ===" -ForegroundColor Cyan
gcloud compute ssh $MASTER --zone=$ZONE --command @"
  rm -rf /tmp/seeders_extracted /tmp/migration_fix
  mkdir -p /tmp/seeders_extracted /tmp/migration_fix
  tar -xf /tmp/seeders.tar      -C /tmp/seeders_extracted
  tar -xf /tmp/fix_migration.tar -C /tmp/migration_fix
  echo 'Extraction OK'
"@
if ($LASTEXITCODE -ne 0) { Write-Error "Extraction failed"; exit 1 }

# ── Step 4: Get pod names ────────────────────────────────────────────────────
Write-Host "`n=== Step 5: Getting pod names ===" -ForegroundColor Cyan
$PODS_RAW = gcloud compute ssh $MASTER --zone=$ZONE --command `
  "sudo k3s kubectl get pods -l $POD_LABEL --no-headers -o custom-columns=':metadata.name' 2>/dev/null" 2>&1
$PODS = $PODS_RAW | Where-Object { $_ -match "laravel-" }
Write-Host "  Pods found: $PODS"
if (-not $PODS) { Write-Error "No Laravel pods found!"; exit 1 }

$FIRST_POD = ($PODS | Select-Object -First 1).Trim()

# ── Step 5: Copy files into each pod ─────────────────────────────────────────
Write-Host "`n=== Step 6: Copying files to pods ===" -ForegroundColor Cyan
foreach ($POD in $PODS) {
    $POD = $POD.Trim()
    if (-not $POD) { continue }
    Write-Host "  -> Copying to pod: $POD"

    # Copy seeders
    gcloud compute ssh $MASTER --zone=$ZONE --command `
      "sudo k3s kubectl cp /tmp/seeders_extracted/seeders ${POD}:/app/database/seeders 2>&1 && echo 'Seeders OK'"

    # Copy fix migration
    gcloud compute ssh $MASTER --zone=$ZONE --command `
      "sudo k3s kubectl cp /tmp/migration_fix/2026_06_26_163000_fix_postgres_check_constraints.php ${POD}:/app/database/migrations/2026_06_26_163000_fix_postgres_check_constraints.php 2>&1 && echo 'Migration OK'"
}

# ── Step 6: Clear caches ─────────────────────────────────────────────────────
Write-Host "`n=== Step 7: Clearing caches in pod $FIRST_POD ===" -ForegroundColor Cyan
gcloud compute ssh $MASTER --zone=$ZONE --command `
  "sudo k3s kubectl exec $FIRST_POD -- php /app/artisan config:clear 2>&1; sudo k3s kubectl exec $FIRST_POD -- php /app/artisan cache:clear 2>&1; echo 'Cache cleared'"

# ── Step 7: Run migration ────────────────────────────────────────────────────
Write-Host "`n=== Step 8: Running migrate in pod $FIRST_POD ===" -ForegroundColor Yellow
gcloud compute ssh $MASTER --zone=$ZONE --command `
  "sudo k3s kubectl exec $FIRST_POD -- php /app/artisan migrate --force 2>&1"
if ($LASTEXITCODE -ne 0) {
    Write-Warning "migrate returned non-zero (may be normal if already up to date). Continuing..."
}

# ── Step 8: Run db:seed ──────────────────────────────────────────────────────
Write-Host "`n=== Step 9: Running db:seed in pod $FIRST_POD ===" -ForegroundColor Yellow
gcloud compute ssh $MASTER --zone=$ZONE --command `
  "sudo k3s kubectl exec $FIRST_POD -- php /app/artisan db:seed --force 2>&1"
$seedResult = $LASTEXITCODE

if ($seedResult -eq 0) {
    Write-Host "`n=== SEEDING COMPLETE! ===" -ForegroundColor Green
} else {
    Write-Error "db:seed failed with exit code $seedResult"
}

# ── Cleanup ───────────────────────────────────────────────────────────────────
Write-Host "`n=== Step 10: Cleanup ===" -ForegroundColor Cyan
Remove-Item seeders.tar, fix_migration.tar -ErrorAction SilentlyContinue
Write-Host "Done." -ForegroundColor Green
