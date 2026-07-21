<?php
/**
 * Test script: Bootstrap Laravel and execute the WhatsApp Webhook Controller directly.
 * This bypasses Nginx/Curl/Network entirely to test internal routing/code execution.
 */

// 1. Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Api\WhatsAppWebhookController;

// 2. Prepare payload
$payload = [
    "event" => "messages.upsert",
    "instance" => "picme_whatsapp",
    "data" => [
        "key" => [
            "remoteJid" => "22500000001@s.whatsapp.net",
            "fromMe" => false,
            "id" => "TEST_MSG_DIR_" . time()
        ],
        "pushName" => "Test User Direct",
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

// 3. Create Request Object
$request = Request::create('/api/user/whatsapp/webhook', 'POST', [], [], [], [], json_encode($payload));
$request->headers->set('Content-Type', 'application/json');

// 4. Instantiate Controller and handle request
try {
    $controller = new WhatsAppWebhookController();
    $response = $controller->handle($request);
    
    echo "=== WhatsApp Webhook Direct execution ===\n";
    echo "HTTP Status Code: " . $response->getStatusCode() . "\n";
    echo "Response Content: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "Execution Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
