<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\Provider;
use App\ServiceType;
use App\UserRequests;
use Illuminate\Http\Request;
use App\Http\Controllers\UserApiController;
use Illuminate\Support\Facades\Auth;

$user = User::first();
$user->kyc_status = 'APPROVED';
$user->save();
Auth::login($user);

Provider::query()->update(['eco_wallet_balance' => 50000]);

$st = ServiceType::where('name', 'like', '%Taxi Vtc%')->first() ?? ServiceType::find(1);
$controller = new UserApiController();

echo "=== TEST E2E TAXI VTC (ID {$st->id}) ===\n\n";

foreach (['partage', 'arret_pdp'] as $variant) {
    echo "--- Variante: $variant ---\n";
    UserRequests::where('user_id', $user->id)->delete();

    $req = new Request([
        's_latitude' => 5.3484,
        's_longitude' => -4.0305,
        'd_latitude' => 5.3500,
        'd_longitude' => -4.0000,
        'service_type' => $st->id,
        'distance' => 5,
        'payment_mode' => 'CASH',
        'ride_variant' => $variant,
        'type' => 'standard',
        'booked' => 1,
    ]);
    $req->headers->set('Accept', 'application/json');
    $req->headers->set('X-Requested-With', 'XMLHttpRequest');
    $req->setUserResolver(fn () => $user);

    $res = $controller->send_request($req);
    $body = json_decode($res->getContent(), true);

    if (empty($body['request_id'])) {
        echo "FAIL send_request: " . $res->getContent() . "\n\n";
        continue;
    }

    $trip = UserRequests::find($body['request_id']);
    echo "OK request_id={$body['request_id']} status={$trip->status} variant={$trip->ride_variant}\n";

    $cancelReq = new Request(['request_id' => $body['request_id'], 'cancel_reason' => 'Test']);
    $cancelReq->headers->set('Accept', 'application/json');
    $cancelReq->headers->set('X-Requested-With', 'XMLHttpRequest');
    $cancelReq->setUserResolver(fn () => $user);
    $cancelRes = $controller->cancel_request($cancelReq);
    echo "Cancel HTTP {$cancelRes->getStatusCode()}: " . $cancelRes->getContent() . "\n\n";
}

echo "DONE\n";
