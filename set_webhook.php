<?php
$evolutionApiUrl = 'http://evolution-api-service:8080';
$evolutionApiKey = 'picme225-evolution-secret-key';
$instanceName = 'picme_whatsapp';
$webhookUrl = 'https://picme225.site/api/user/whatsapp/webhook';

$data = [
    'webhook' => [
        'enabled' => true,
        'url' => $webhookUrl,
        'byEvents' => false,
        'base64' => false,
        'events' => [
            'APPLICATION_STARTUP',
            'QRCODE_UPDATED',
            'MESSAGES_UPSERT',
            'MESSAGES_UPDATE',
            'MESSAGES_DELETE',
            'SEND_MESSAGE',
            'CONNECTION_UPDATE',
        ]
    ]
];

$ch = curl_init("{$evolutionApiUrl}/webhook/set/{$instanceName}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: {$evolutionApiKey}",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
curl_close($ch);

echo "Response: $response\n";
