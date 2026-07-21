#!/bin/bash
# Check locale config
sudo k3s kubectl exec laravel-deployment-5f44cbb875-dp5ff -- php /app/artisan tinker --execute="echo app()->getLocale() . ' | ' . Setting::get('language');"
