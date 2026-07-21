<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$request = \Illuminate\Http\Request::create('/api/user/service-types', 'GET', ['service_name' => 'Voyage', 'ride_variant' => 'partage']);
$controller = new \App\Http\Controllers\UserServiceController();
$response = $controller->getServiceTypes($request);

echo json_encode($response->getData(), JSON_PRETTY_PRINT);
