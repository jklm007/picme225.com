#!/bin/bash
POD=$(sudo kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
sudo kubectl cp /tmp/WhatsappListingController.php default/${POD}:/app/app/Http/Controllers/Admin/WhatsappListingController.php
sudo kubectl exec ${POD} -- php artisan route:clear
