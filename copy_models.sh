#!/bin/bash
set -e

K="sudo k3s kubectl"
PODS="laravel-deployment-78fb8fb974-br246 laravel-deployment-78fb8fb974-mt7n4"
WORKER="laravel-worker-54bc9dbd5-2kbnv"

for pod in $PODS; do
    echo "--- Copying to $pod ---"
    $K cp /tmp/WhatsappMessage.php default/$pod:/app/app/Models/WhatsappMessage.php
    $K cp /tmp/WhatsappUser.php default/$pod:/app/app/Models/WhatsappUser.php
    $K cp /tmp/WhatsAppWebhookController.php default/$pod:/app/app/Http/Controllers/Api/WhatsAppWebhookController.php
    $K cp /tmp/ProcessWhatsappMessageJob.php default/$pod:/app/app/Jobs/ProcessWhatsappMessageJob.php
    $K exec $pod -- composer dump-autoload --quiet
    echo "OK $pod"
done

echo "--- Copying to worker $WORKER ---"
$K cp /tmp/WhatsappMessage.php default/$WORKER:/app/app/Models/WhatsappMessage.php
$K cp /tmp/WhatsappUser.php default/$WORKER:/app/app/Models/WhatsappUser.php
$K cp /tmp/ProcessWhatsappMessageJob.php default/$WORKER:/app/app/Jobs/ProcessWhatsappMessageJob.php
$K exec $WORKER -- composer dump-autoload --quiet
$K exec $WORKER -- php artisan queue:restart
echo "ALL DONE"
