<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\User::where('mobile', 'like', '%0709152973%')->first();
echo "TOKEN: " . ($user ? (empty($user->device_token) ? 'EMPTY' : $user->device_token) : 'USER NOT FOUND') . "\n";
