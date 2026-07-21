<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
\Illuminate\Support\Facades\Auth::loginUsingId(1);
$request = Illuminate\Http\Request::create("/api/user/send/request", "POST", [
    "s_latitude" => "5.345317",
    "s_longitude" => "-4.024429",
    "d_latitude" => "5.350000",
    "d_longitude" => "-4.000000",
    "s_address" => "A",
    "d_address" => "B",
    "service_type" => "1",
    "distance" => "2.5",
    "use_wallet" => "0",
    "payment_mode" => "CASH",
    "ride_variant" => "arret_pdp"
]);
$response = $kernel->handle($request);
echo "\nRESPONSE: " . $response->getContent() . "\n";
