<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\UserRequests;
use App\ServiceType;
use Illuminate\Http\Request;
use App\Http\Controllers\UserApiController;
use Illuminate\Support\Facades\Auth;

echo "=== TEST ANNULATION + BOOKING ===\n";

$user = User::first();
Auth::login($user);
echo "User: {$user->id}\n";

$st = ServiceType::first();
if (!$st) {
    echo "FAIL: no service type\n";
    exit(1);
}

UserRequests::where('user_id', $user->id)->delete();

$controller = new UserApiController();
$createReq = new Request([
    's_latitude' => 5.3484,
    's_longitude' => -4.0305,
    'd_latitude' => 5.3500,
    'd_longitude' => -4.0000,
    'service_type' => $st->id,
    'distance' => 5,
    'payment_mode' => 'CASH',
    'ride_variant' => 'prive',
    'type' => 'standard',
]);
$createReq->headers->set('Accept', 'application/json');
$createReq->headers->set('X-Requested-With', 'XMLHttpRequest');
$createReq->setUserResolver(fn () => $user);

$res = $controller->send_request($createReq);
$body = json_decode($res->getContent(), true);
if (empty($body['request_id'])) {
    echo "FAIL create: " . $res->getContent() . "\n";
    exit(1);
}
$requestId = $body['request_id'];
echo "OK create request_id=$requestId\n";

$cancelReq = new Request([
    'request_id' => $requestId,
    'cancel_reason' => 'Test auto',
]);
$cancelReq->headers->set('Accept', 'application/json');
$cancelReq->headers->set('X-Requested-With', 'XMLHttpRequest');
$cancelReq->setUserResolver(fn () => $user);

$cancelRes = $controller->cancel_request($cancelReq);
echo "Cancel HTTP: " . $cancelRes->getStatusCode() . " body: " . $cancelRes->getContent() . "\n";

$trip = UserRequests::find($requestId);
if ($cancelRes->getStatusCode() === 200 && $trip && $trip->status === 'CANCELLED') {
    echo "SUCCESS cancel workflow\n";
    exit(0);
}

echo "FAIL cancel workflow\n";
exit(1);
