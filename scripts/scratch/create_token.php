<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\User::find(1);
$tokenObj = $user->createToken('UserToken');
$token = $tokenObj->accessToken;
echo "Token created manually: \n$token\n";

$headers = ["Content-Type: application/json", "Accept: application/json", "Authorization: Bearer $token"];
$ctx = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => implode("\r\n", $headers),
        'timeout' => 30,
        'ignore_errors' => true,
    ]
]);
$result = file_get_contents('http://localhost:8000/api/user/services', false, $ctx);
echo "RESPONSE FROM /api/user/services:\n$result\n";
