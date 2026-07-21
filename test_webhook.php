<?php
$url = 'https://www.picme225.site/api/whatsapp/webhook';
$data = [
    'event' => 'messages.upsert',
    'data' => [
        'message' => [
            'conversation' => 'Je vends ma voiture 2015 a 4500000 FCFA Abidjan'
        ],
        'key' => [
            'remoteJid' => '2250102030405@s.whatsapp.net',
            'fromMe' => false
        ],
        'pushName' => 'TestUser'
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
