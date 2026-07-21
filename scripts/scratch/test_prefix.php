<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\UserApiController;

$mobileWithPrefix = '+2250759747444';
echo "Testing login with prefix: $mobileWithPrefix\n";

$request = new Request([
    'mobile' => $mobileWithPrefix,
    'password' => '123456',
    'device_id' => 'test_id',
    'device_type' => 'android',
    'device_token' => 'test_token'
]);

$controller = new UserApiController();
try {
    $response = $controller->signin($request);
    echo "Response Code: " . $response->getStatusCode() . "\n";
    echo "Response Content: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\nTesting Passport findForPassport with prefix...\n";
$user = App\User::findForPassport($mobileWithPrefix);
if ($user) {
    echo "Passport User Found: " . $user->email . "\n";
} else {
    echo "Passport User NOT Found.\n";
}
