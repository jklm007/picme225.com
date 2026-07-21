<?php

$instanceName = 'picme_whatsapp';
$apiUrl = 'http://10.43.90.250:8080/webhook/set/' . $instanceName;
$apiKey = '429683C4C977415CAC40E282200E3171'; // Global API Key

$webhookUrl = 'https://www.picme225.site/api/user/whatsapp/webhook';

$data = [
    "webhook" => [
        "url" => $webhookUrl,
        "byEvents" => false,
        "base64" => false,
        "events" => [
            "MESSAGES_UPSERT",
            "MESSAGES_UPDATE"
        ]
    ]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'apikey: ' . $apiKey
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
