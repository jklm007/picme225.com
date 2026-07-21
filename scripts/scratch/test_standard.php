<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\UserApiController;
use Illuminate\Http\Request;

$controller = new UserApiController();
$request = new Request([
    'service_name' => 'standard',
    'ride_variant' => 'prive'
]);

$response = $controller->getServiceTypes($request);
echo $response->getContent();
