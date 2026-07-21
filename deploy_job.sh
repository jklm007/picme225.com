#!/bin/bash
POD=$(sudo kubectl get pods -l app=laravel -o jsonpath='{.items[?(@.status.phase=="Running")].metadata.name}' | awk '{print $1}')
echo "Deploying to $POD"
sudo kubectl cp /tmp/ProcessWhatsappBatchJob.php default/$POD:/app/app/Jobs/ProcessWhatsappBatchJob.php
