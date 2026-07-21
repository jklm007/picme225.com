#!/bin/bash
PODS=$(/usr/local/bin/k3s kubectl get pods -l app=laravel -o custom-columns=:metadata.name --no-headers)
for pod in $PODS; do
    echo "Syncing $pod..."
    /usr/local/bin/k3s kubectl cp /home/ubuntu/admin_index.blade.php $pod:/app/resources/views/admin/whatsapp/index.blade.php -c laravel
    /usr/local/bin/k3s kubectl cp /home/ubuntu/qr_page.blade.php $pod:/app/resources/views/whatsapp/qr_page.blade.php -c laravel
    /usr/local/bin/k3s kubectl cp /home/ubuntu/WhatsappListingController.php $pod:/app/app/Http/Controllers/Admin/WhatsappListingController.php -c laravel
    /usr/local/bin/k3s kubectl exec $pod -c laravel -- sh -c "cd /app && php artisan view:clear && php artisan cache:clear"
done
