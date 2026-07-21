#!/bin/bash
echo "=== Uploading detail.blade.php to Master Node ==="
POD=$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
sudo k3s kubectl cp /tmp/detail.blade.php default/${POD}:/app/resources/views/marketplace/detail.blade.php -c laravel
echo "Done!"
