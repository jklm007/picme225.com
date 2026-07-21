<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$userLink = \Setting::get('store_link_android');
$driverLink = \Setting::get('provider_store_link_android');

echo "User link: " . $userLink . "\n";
echo "Driver link: " . $driverLink . "\n";

if ($userLink === '#' || $userLink === '') {
    \Setting::set('store_link_android', '/download/user');
    \Setting::save();
}

if ($driverLink === '#' || $driverLink === '') {
    \Setting::set('provider_store_link_android', '/download/driver');
    \Setting::save();
}

echo "Settings updated.\n";
