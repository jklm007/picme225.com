<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

echo "--- CREATION UTILISATEURS TEST ---" . PHP_EOL;

// 1. User
$userMobile = '0102030405';
$userPass = 'password';

$user = \App\User::where('mobile', $userMobile)->first();
if (!$user) {
    $user = new \App\User();
    $user->email = 'testuser@example.com';
    $user->first_name = 'Test';
    $user->last_name = 'User';
    $user->mobile = $userMobile;
}
$user->password = Hash::make($userPass);
$user->save();
echo "User cree/mis a jour: Mobile=$userMobile, Pass=$userPass" . PHP_EOL;


// 2. Provider
$provMobile = '0504030201';
$provPass = 'password';

$provider = \App\Provider::where('mobile', $provMobile)->first();
if (!$provider) {
    $provider = new \App\Provider();
    $provider->email = 'testdriver@example.com';
    $provider->first_name = 'Test';
    $provider->last_name = 'Driver';
    $provider->mobile = $provMobile;
    $provider->status = 'approved';
    $provider->commune = 'Abidjan'; // Champ obligatoire
}
$provider->password = Hash::make($provPass);
$provider->save();
echo "Provider cree/mis a jour: Mobile=$provMobile, Pass=$provPass" . PHP_EOL;

echo "--- TERMINE ---" . PHP_EOL;
