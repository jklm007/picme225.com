<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

echo "--- CREATION VARIANTES UTILISATEURS (CORRIGE) ---" . PHP_EOL;

$password = Hash::make('password');

// --- USER VARIANTS ---
$userMobiles = ['0102030405', '2250102030405', '+2250102030405'];

foreach ($userMobiles as $mobile) {
    $existing = \App\User::where('mobile', $mobile)->first();
    if ($existing) {
        echo "User existant: $mobile (Update password)\n";
        $existing->password = $password;
        $existing->save();
    } else {
        echo "Creation User: $mobile\n";
        $u = new \App\User();
        $u->first_name = 'Test';
        $u->last_name = 'User';
        // Email unique en utilisant le mobile brut (incluant le +) et un hash court si besoin
        $u->email = 'user_' . md5($mobile) . '@test.com'; 
        $u->mobile = $mobile;
        $u->password = $password;
        $u->save();
    }
}

// --- PROVIDER VARIANTS ---
$provMobiles = ['0504030201', '2250504030201', '+2250504030201'];

foreach ($provMobiles as $mobile) {
    $existing = \App\Provider::where('mobile', $mobile)->first();
    if ($existing) {
        echo "Provider existant: $mobile (Update password)\n";
        $existing->password = $password;
        $existing->status = 'approved';
        $existing->save();
    } else {
        echo "Creation Provider: $mobile\n";
        $p = new \App\Provider();
        $p->first_name = 'Test';
        $p->last_name = 'Driver';
        $p->email = 'driver_' . md5($mobile) . '@test.com';
        $p->mobile = $mobile;
        $p->password = $password;
        $p->status = 'approved';
        $p->commune = 'Abidjan';
        $p->save();
    }
}

echo "--- TERMINE ---" . PHP_EOL;
