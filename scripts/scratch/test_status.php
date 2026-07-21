<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\UserRequests;
use Auth;

$user = User::find(1);
Auth::login($user);

$check_status = ['CANCELLED', 'SCHEDULED'];
$UserRequests = UserRequests::UserRequestStatusCheck($user->id, $check_status)
    ->get();

echo "=== Status Check Results ===\n";
echo "Count: " . $UserRequests->count() . "\n";
foreach ($UserRequests as $r) {
    echo "ID: {$r->id} | Status: {$r->status} | Provider: {$r->provider_id} | OTP: {$r->otp}\n";
    echo "Provider Obj: " . ($r->provider ? $r->provider->first_name : 'NONE') . "\n";
    echo "Service Type: " . ($r->service_type ? $r->service_type->name : 'NONE') . "\n";
}
