<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = new \Illuminate\Http\Request();
$randTxn = 'TESTFIREBASE.' . time();
$request->replace([
    'from' => '+454',
    'message' => 'Bonjour, vous avez recu un transfert de 360.00 FCFA du 0709152973. Reference ' . $randTxn . '. Nouveau solde 4923.50 FCFA.',
    'receiver_phone' => '0759747444'
]);

$controller = new \App\Http\Controllers\SmsPaymentController();
$response = $controller->handleSms($request);

echo "RESPONSE: " . $response->getContent() . "\n";
