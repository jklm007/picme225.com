<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = new Illuminate\Http\Request();
$controller = new App\Http\Controllers\SocialTransportController();
$response = $controller->getStories($request);

echo json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT);
