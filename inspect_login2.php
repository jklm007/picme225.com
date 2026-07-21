<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/login', 'POST', [
    'email' => '+2250707070707',
    'password' => '123456'
]);
$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Redirect: " . $response->headers->get('Location') . "\n";
$errors = session()->get('errors');
if ($errors) {
    print_r($errors->all());
}
