<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$instance = 'picme_whatsapp';
$apiKey = '6BE2FB65-6676-48FB-BE16-162621590D6A';
$apiUrl = 'https://evolution.picme225.site';

// Read the log file for message ID 36's raw payload
$logs = file_get_contents('/app/storage/logs/laravel.log');
// Use regex to find the payload just before message 34 or related to 36
preg_match_all('/WEBHOOK RECEIVED ({.*})/', $logs, $matches);
$payloads = array_reverse($matches[1]);

foreach ($payloads as $p) {
    $payload = json_decode($p, true);
    if (isset($payload['data']['messageType']) && in_array($payload['data']['messageType'], ['imageMessage', 'videoMessage'])) {
        echo "Found image message!\n";
        echo "Testing API fetch...\n";
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'apikey' => $apiKey
        ])->post("$apiUrl/chat/getBase64FromMediaMessage/$instance", [
            'message' => $payload['data']
        ]);
        
        echo "Status: " . $response->status() . "\n";
        echo "Response: " . substr($response->body(), 0, 500) . "\n";
        break;
    }
}
