<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;

$p = Provider::find(21);

$request = Illuminate\Http\Request::create('/api/provider/trip/help', 'GET');
$request->setUserResolver(function() use ($p) { return $p; });
$response = app()->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
