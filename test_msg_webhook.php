<?php
/**
 * Test script: Simulate a WhatsApp MESSAGES_UPSERT event
 * Run from inside a Laravel pod to test the full pipeline:
 * webhook → DB → ProcessWhatsappMessageJob → Groq AI
 */

$webhookUrl = 'http://localhost/api/user/whatsapp/webhook';

$payload = [
    "event" => "messages.upsert",
    "instance" => "picme_whatsapp",
    "data" => [
        "key" => [
            "remoteJid" => "22500000001@s.whatsapp.net",
            "fromMe" => false,
            "id" => "TEST_MSG_" . time()
        ],
        "pushName" => "Test User",
        "messageType" => "conversation",
        "message" => [
            "conversation" => "Bonjour, je cherche un appartement à Abidjan"
        ]
    ],
    "destination" => "https://www.picme225.site/api/user/whatsapp/webhook",
    "date_time" => date('c'),
    "server_url" => "https://evolution.picme225.site",
    "apikey" => "7EC9EF15-0D92-46B9-B324-C46DB277E3FD"
];

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error    = curl_error($ch);
curl_close($ch);

echo "=== WhatsApp Webhook Test ===\n";
echo "HTTP Code: $httpCode\n";
if ($error) echo "CURL Error: $error\n";
echo "Response: $response\n";
echo "\nChecking DB for saved message...\n";

// Quick DB check via artisan tinker output
