<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/login', 'POST', [
    'email' => '+2250707070707',
    'password' => 'wrongpassword'
]);
$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
$old = session()->get('_old_input');
print_r($old);
