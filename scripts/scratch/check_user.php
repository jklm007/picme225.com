<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\User::where('mobile', 'like', '%0709152973%')->first();
echo "User found: " . ($user ? "YES (ID: " . $user->id . ")" : "NO") . "\n";

$provider = DB::table('providers')->where('mobile', 'like', '%0709152973%')->first();
echo "Provider found: " . ($provider ? "YES (ID: " . $provider->id . ")" : "NO") . "\n";