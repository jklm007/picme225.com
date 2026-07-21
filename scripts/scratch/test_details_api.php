<?php
require __DIR__ . '/bootstrap/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\Http\Controllers\UserApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$user = User::where('mobile', '+22502020202')->first();
Auth::login($user);

$controller = new UserApiController();
$request = new Request(['device_type' => 'android']);

// Simuler Auth::user() dans le contrôleur via le guard
$response = $controller->details($request);

echo "DETAILS RESPONSE:\n";
if (is_array($response) || is_object($response)) {
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
} else {
    echo $response->getContent() . "\n";
}
