<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$phone = '0759747444'; // Try without country code first
$phone_full = '+2250759747444';

$user = App\Models\User::where('mobile', 'like', "%$phone%")->first();
if ($user) {
    echo "Found User: " . $user->mobile . " | Email: " . $user->email . "\n";
} else {
    echo "No User found with $phone\n";
}

$driver = App\Models\Provider::where('mobile', 'like', "%$phone%")->first();
if ($driver) {
    echo "Found Driver: " . $driver->mobile . " | Email: " . $driver->email . "\n";
} else {
    echo "No Driver found with $phone\n";
}
