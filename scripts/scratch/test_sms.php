<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$request = Illuminate\Http\Request::create('/api/user/gateway/sms-received', 'POST', [
    'sender' => 'OrangeMoney',
    'message' => 'Bonjour, vous avez recu un transfert de 450.00 FCFA du 0709152973.',
    'from' => 'OrangeMoney',
    'receiver_phone' => '0759747444'
]);

$response = app()->handle($request);
echo "STATUS: " . $response->getStatusCode() . "\n";
echo "CONTENT: " . $response->getContent() . "\n";
