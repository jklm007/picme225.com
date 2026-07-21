<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$mobile = '0759747444';
$password = '123456';

$provider = App\Provider::where('mobile', $mobile)->first();

if ($provider) {
    echo "Provider found: " . $provider->first_name . " " . $provider->last_name . PHP_EOL;
    echo "Stored Hash: " . $provider->password . PHP_EOL;

    if (Hash::check($password, $provider->password)) {
        echo "MATCH: Password verified successfully." . PHP_EOL;
    } else {
        echo "MISMATCH: Password does NOT match." . PHP_EOL;
        echo "Re-hashing '$password' and updating..." . PHP_EOL;
        $provider->password = Hash::make($password);
        $provider->save();
        echo "Updated. New Hash: " . $provider->password . PHP_EOL;
    }
} else {
    echo "Provider not found for mobile: $mobile" . PHP_EOL;
}
?>