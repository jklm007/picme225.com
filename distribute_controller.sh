#!/bin/bash
echo "=== Uploading MarketplaceListingController.php to Master Node ==="
POD=$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
sudo k3s kubectl cp /tmp/MarketplaceListingController.php default/${POD}:/app/app/Http/Controllers/MarketplaceListingController.php -c laravel
echo "Done!"
