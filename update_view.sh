#!/bin/bash
POD=$(sudo kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
sudo kubectl cp /tmp/index.blade.php default/${POD}:/app/resources/views/admin/whatsapp/index.blade.php
sudo kubectl exec ${POD} -- php artisan view:clear
