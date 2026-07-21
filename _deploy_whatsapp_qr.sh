#!/bin/bash
# Deploy WhatsApp QR feature to both Laravel pods

POD1="laravel-deployment-8c59cf9c4-8rmr7"
POD2="laravel-deployment-8c59cf9c4-bbs49"
ZONE="europe-west9-a"
K="sudo k3s kubectl"

echo "=== Uploading files to GCP ==="
# Files to copy
FILES=(
  "app/Http/Controllers/WhatsAppQrController.php"
  "resources/views/whatsapp/qr_page.blade.php"
  "routes/web.php"
)

echo "=== Deploying to Pod 1: $POD1 ==="
$K cp app/Http/Controllers/WhatsAppQrController.php default/$POD1:/app/app/Http/Controllers/WhatsAppQrController.php
$K exec $POD1 -- mkdir -p /app/resources/views/whatsapp
$K cp resources/views/whatsapp/qr_page.blade.php default/$POD1:/app/resources/views/whatsapp/qr_page.blade.php
$K cp routes/web.php default/$POD1:/app/routes/web.php
$K exec $POD1 -- php artisan route:clear
$K exec $POD1 -- php artisan view:clear
$K exec $POD1 -- php artisan config:clear

echo "=== Deploying to Pod 2: $POD2 ==="
$K cp app/Http/Controllers/WhatsAppQrController.php default/$POD2:/app/app/Http/Controllers/WhatsAppQrController.php
$K exec $POD2 -- mkdir -p /app/resources/views/whatsapp
$K cp resources/views/whatsapp/qr_page.blade.php default/$POD2:/app/resources/views/whatsapp/qr_page.blade.php
$K cp routes/web.php default/$POD2:/app/routes/web.php
$K exec $POD2 -- php artisan route:clear
$K exec $POD2 -- php artisan view:clear
$K exec $POD2 -- php artisan config:clear

echo "=== Done! WhatsApp QR feature deployed ==="
