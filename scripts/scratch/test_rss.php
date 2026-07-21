<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$controller = new App\Http\Controllers\NewsAggregatorController();
$request = new Illuminate\Http\Request();
$request->merge(['limit' => 30]);
$res = $controller->index($request);
echo json_encode($res->getData(), JSON_PRETTY_PRINT);
